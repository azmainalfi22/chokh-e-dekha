/**
 * Strips metadata from uploaded images before they are published.
 *
 * A phone photograph of a pothole carries an Exif block, and that block
 * routinely carries GPS coordinates. Report evidence is served from public
 * object URLs, so publishing the file byte-for-byte publishes wherever the
 * photograph was taken — which, for a photo taken before leaving the house, is
 * the reporter's home address. It can also carry the device serial, the owner's
 * name, and the original timestamp.
 *
 * The submit wizard already runs photos through browser-image-compression,
 * whose canvas re-encode happens to drop Exif. That is a convenience, not a
 * control: it runs in the browser, and the browser is the one part of this we do
 * not own. Anything posting straight at the storage API skips it entirely. This
 * module is the server-side enforcement.
 *
 * It works at the container level rather than re-encoding: walk the JPEG marker
 * segments, PNG chunks or WebP RIFF chunks and copy everything except the
 * metadata ones. That is lossless, needs no image library, and cannot silently
 * recompress a citizen's evidence — which matters when the photograph is the
 * whole point of the report.
 *
 * Written from scratch against the JPEG (ITU-T T.81 / JFIF), PNG and WebP (RIFF)
 * container specifications.
 */

/** Result of a strip attempt. `null` bytes mean the input was not understood. */
export type StripResult =
  | { ok: true; bytes: Uint8Array; format: "jpeg" | "png" | "webp" }
  | { ok: false; reason: string };

const JPEG_SOI = [0xff, 0xd8];
const PNG_SIGNATURE = [0x89, 0x50, 0x4e, 0x47, 0x0d, 0x0a, 0x1a, 0x0a];

/** PNG ancillary chunks that carry text, timestamps or Exif. */
const PNG_DROP = new Set(["eXIf", "tEXt", "zTXt", "iTXt", "tIME"]);

/** WebP RIFF chunks that carry metadata rather than picture data. */
const WEBP_DROP = new Set(["EXIF", "XMP "]);

function startsWith(bytes: Uint8Array, prefix: number[]): boolean {
  if (bytes.length < prefix.length) return false;
  return prefix.every((byte, i) => bytes[i] === byte);
}

function ascii(bytes: Uint8Array, offset: number, length: number): string {
  return String.fromCharCode(...bytes.subarray(offset, offset + length));
}

/**
 * Remove metadata from an image.
 *
 * Callers must treat a failure as "reject the upload". Storing a file whose
 * metadata we could not strip would defeat the point of doing this at all, so
 * there is deliberately no "pass it through unchanged" path.
 */
export function stripImageMetadata(bytes: Uint8Array): StripResult {
  if (startsWith(bytes, JPEG_SOI)) {
    const out = stripJpeg(bytes);
    return out
      ? { ok: true, bytes: out, format: "jpeg" }
      : { ok: false, reason: "The JPEG data could not be read." };
  }

  if (startsWith(bytes, PNG_SIGNATURE)) {
    const out = stripPng(bytes);
    return out
      ? { ok: true, bytes: out, format: "png" }
      : { ok: false, reason: "The PNG data could not be read." };
  }

  if (
    bytes.length > 12 &&
    ascii(bytes, 0, 4) === "RIFF" &&
    ascii(bytes, 8, 4) === "WEBP"
  ) {
    const out = stripWebp(bytes);
    return out
      ? { ok: true, bytes: out, format: "webp" }
      : { ok: false, reason: "The WebP data could not be read." };
  }

  return {
    ok: false,
    reason: "Only JPEG, PNG and WebP photographs can be accepted.",
  };
}

/**
 * Copy a JPEG segment by segment, dropping APP1–APP15 and comments.
 *
 * APP0 (JFIF) is kept because it describes pixel density. APP2 is kept only when
 * it is an ICC colour profile, so colours survive; the FlashPix payload that also
 * uses APP2 goes with the rest. Exif and XMP both live in APP1, which is where
 * GPS coordinates are.
 */
function stripJpeg(bytes: Uint8Array): Uint8Array | null {
  const parts: Uint8Array[] = [Uint8Array.from(JPEG_SOI)];
  let i = 2;

  while (i + 1 < bytes.length) {
    if (bytes[i] !== 0xff) return null; // not on a marker boundary; refuse to guess

    // 0xFF bytes may pad the space before the marker code.
    let cursor = i;
    while (cursor < bytes.length && bytes[cursor] === 0xff) cursor++;
    if (cursor >= bytes.length) return null;

    const marker = bytes[cursor];
    const markerStart = i;
    i = cursor + 1;

    if (marker === 0xd9) {
      // End of image.
      parts.push(Uint8Array.from([0xff, 0xd9]));
      return concat(parts);
    }

    if (marker === 0xda) {
      // Start of scan: entropy-coded data runs to the end of the file, so copy
      // the remainder untouched.
      parts.push(bytes.subarray(markerStart));
      return concat(parts);
    }

    // Standalone markers (restart intervals, TEM) carry no length field.
    if ((marker >= 0xd0 && marker <= 0xd7) || marker === 0x01) {
      parts.push(Uint8Array.from([0xff, marker]));
      continue;
    }

    if (i + 1 >= bytes.length) return null;

    const segmentLength = (bytes[i] << 8) | bytes[i + 1];

    // The length field counts itself, so anything under 2 is corrupt.
    if (segmentLength < 2 || i + segmentLength > bytes.length) return null;

    if (keepJpegSegment(marker, bytes, i + 2, segmentLength - 2)) {
      parts.push(Uint8Array.from([0xff, marker]));
      parts.push(bytes.subarray(i, i + segmentLength));
    }

    i += segmentLength;
  }

  return concat(parts);
}

function keepJpegSegment(
  marker: number,
  bytes: Uint8Array,
  payloadStart: number,
  payloadLength: number
): boolean {
  if (marker === 0xfe) return false; // COM, a free-text comment

  // APP1 through APP15. APP0 is JFIF and stays.
  if (marker >= 0xe1 && marker <= 0xef) {
    if (marker !== 0xe2) return false;
    const tag = ascii(bytes, payloadStart, Math.min(12, payloadLength));
    return tag.startsWith("ICC_PROFILE\0");
  }

  return true;
}

/**
 * Copy a PNG chunk by chunk, dropping the text, timestamp and Exif chunks.
 *
 * Every chunk is length(4) + type(4) + data + CRC(4), and the CRC covers only
 * that chunk, so whole chunks can be dropped without recomputing anything.
 */
function stripPng(bytes: Uint8Array): Uint8Array | null {
  const parts: Uint8Array[] = [Uint8Array.from(PNG_SIGNATURE)];
  const view = new DataView(bytes.buffer, bytes.byteOffset, bytes.byteLength);
  let i = PNG_SIGNATURE.length;

  while (i + 8 <= bytes.length) {
    const dataLength = view.getUint32(i);
    const type = ascii(bytes, i + 4, 4);
    const chunkSize = 12 + dataLength;

    if (i + chunkSize > bytes.length) return null;

    if (!PNG_DROP.has(type)) parts.push(bytes.subarray(i, i + chunkSize));

    i += chunkSize;

    if (type === "IEND") return concat(parts);
  }

  return null; // ran out of bytes before IEND
}

/**
 * Copy a WebP RIFF file chunk by chunk, dropping EXIF and XMP.
 *
 * Chunks are type(4) + size(4, little endian) + payload, padded to an even
 * length. The RIFF header carries the total size, so it is rewritten once the
 * metadata chunks are gone.
 */
function stripWebp(bytes: Uint8Array): Uint8Array | null {
  const view = new DataView(bytes.buffer, bytes.byteOffset, bytes.byteLength);
  const parts: Uint8Array[] = [];
  let i = 12; // past "RIFF" + size + "WEBP"

  while (i + 8 <= bytes.length) {
    const type = ascii(bytes, i, 4);
    const size = view.getUint32(i + 4, true);
    const padded = size + (size % 2); // chunks are padded to an even length
    const chunkSize = 8 + padded;

    if (i + chunkSize > bytes.length) return null;

    if (!WEBP_DROP.has(type)) parts.push(bytes.subarray(i, i + chunkSize));

    i += chunkSize;
  }

  if (parts.length === 0) return null;

  const body = concat(parts);
  const out = new Uint8Array(12 + body.length);
  out.set(bytes.subarray(0, 12));
  out.set(body, 12);

  // "RIFF" size counts everything after the size field itself, so 4 for "WEBP"
  // plus the chunks that survived.
  new DataView(out.buffer).setUint32(4, 4 + body.length, true);

  return out;
}

function concat(parts: Uint8Array[]): Uint8Array {
  const total = parts.reduce((sum, part) => sum + part.length, 0);
  const out = new Uint8Array(total);
  let offset = 0;
  for (const part of parts) {
    out.set(part, offset);
    offset += part.length;
  }
  return out;
}

/** File extension for a format we accept, for building a storage path. */
export function extensionFor(format: "jpeg" | "png" | "webp"): string {
  return format === "jpeg" ? "jpg" : format;
}

/** Content type for a format we accept. */
export function contentTypeFor(format: "jpeg" | "png" | "webp"): string {
  return `image/${format}`;
}
