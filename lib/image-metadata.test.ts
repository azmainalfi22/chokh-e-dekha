import test from "node:test";
import assert from "node:assert/strict";
import { stripImageMetadata } from "./image-metadata.ts";

/**
 * The metadata stripper.
 *
 * Containers are built here byte by byte rather than loaded from fixtures, so
 * what is asserted is the actual segment structure rather than "some file we
 * happened to save". The GPS case matters most: a phone photograph of a pothole
 * carries the coordinates it was taken at, and report evidence is served from
 * public URLs.
 */

const u8 = (...bytes: number[]) => Uint8Array.from(bytes);

function concat(...parts: Uint8Array[]): Uint8Array {
  const out = new Uint8Array(parts.reduce((n, p) => n + p.length, 0));
  let at = 0;
  for (const p of parts) {
    out.set(p, at);
    at += p.length;
  }
  return out;
}

const big16 = (n: number) => u8((n >> 8) & 0xff, n & 0xff);
const asciiBytes = (s: string) => Uint8Array.from([...s].map((c) => c.charCodeAt(0)));

/** A structurally valid JPEG whose Exif APP1 carries a GPS tag. */
function jpegWithGps(): Uint8Array {
  // TIFF header, little endian, first IFD at offset 8.
  const tiff = concat(
    asciiBytes("II"),
    u8(0x2a, 0x00, 0x08, 0x00, 0x00, 0x00),
    // IFD0: one entry, a GPS IFD pointer (tag 0x8825, LONG) at offset 26.
    u8(0x01, 0x00),
    u8(0x25, 0x88, 0x04, 0x00, 0x01, 0x00, 0x00, 0x00, 0x1a, 0x00, 0x00, 0x00),
    u8(0x00, 0x00, 0x00, 0x00),
    // GPS IFD at offset 26: latitude ref "N".
    u8(0x01, 0x00),
    u8(0x01, 0x00, 0x02, 0x00, 0x02, 0x00, 0x00, 0x00),
    asciiBytes("N"),
    u8(0x00, 0x00, 0x00),
    u8(0x00, 0x00, 0x00, 0x00)
  );

  const exif = concat(asciiBytes("Exif"), u8(0x00, 0x00), tiff);
  const app1 = concat(u8(0xff, 0xe1), big16(exif.length + 2), exif);

  // JFIF APP0, which must survive: it describes pixel density.
  const jfif = concat(asciiBytes("JFIF"), u8(0x00, 0x01, 0x02, 0x00, 0x00, 0x01, 0x00, 0x01, 0x00, 0x00));
  const app0 = concat(u8(0xff, 0xe0), big16(jfif.length + 2), jfif);

  const commentText = asciiBytes("Taken at home");
  const comment = concat(u8(0xff, 0xfe), big16(commentText.length + 2), commentText);

  const sos = concat(u8(0xff, 0xda), big16(8), u8(0x01, 0x01, 0x00, 0x00, 0x3f, 0x00));

  return concat(u8(0xff, 0xd8), app0, app1, comment, sos, u8(0x12, 0x34, 0x56), u8(0xff, 0xd9));
}

function contains(haystack: Uint8Array, needle: string): boolean {
  return Buffer.from(haystack).includes(Buffer.from(needle, "latin1"));
}

test("it removes the Exif block from a JPEG", () => {
  const original = jpegWithGps();
  assert.ok(contains(original, "Exif\0\0"), "fixture should start with an Exif block");

  const result = stripImageMetadata(original);

  assert.ok(result.ok);
  assert.equal(result.format, "jpeg");
  assert.ok(!contains(result.bytes, "Exif\0\0"), "the Exif block survived");
});

test("it removes the GPS tag specifically", () => {
  // 0x8825 is the GPS IFD pointer. Its little-endian bytes are 25 88.
  const result = stripImageMetadata(jpegWithGps());

  assert.ok(result.ok);
  assert.ok(
    !Buffer.from(result.bytes).includes(Buffer.from([0x25, 0x88, 0x04, 0x00])),
    "the GPS IFD pointer survived"
  );
});

test("it drops free-text comments but keeps JFIF", () => {
  const result = stripImageMetadata(jpegWithGps());

  assert.ok(result.ok);
  assert.ok(!contains(result.bytes, "Taken at home"), "the comment survived");
  assert.ok(contains(result.bytes, "JFIF"), "JFIF density info should survive");
});

test("it leaves the picture data byte for byte", () => {
  const result = stripImageMetadata(jpegWithGps());

  assert.ok(result.ok);
  assert.deepEqual([...result.bytes.subarray(0, 2)], [0xff, 0xd8]);
  assert.deepEqual([...result.bytes.subarray(-2)], [0xff, 0xd9]);
  assert.ok(
    Buffer.from(result.bytes).includes(Buffer.from([0x12, 0x34, 0x56])),
    "entropy-coded image data must not be recompressed away"
  );
});

test("it keeps an ICC colour profile so colours do not shift", () => {
  const icc = concat(asciiBytes("ICC_PROFILE"), u8(0x00), new Uint8Array(20));
  const app2 = concat(u8(0xff, 0xe2), big16(icc.length + 2), icc);
  const sos = concat(u8(0xff, 0xda), big16(8), u8(0x01, 0x01, 0x00, 0x00, 0x3f, 0x00));
  const jpeg = concat(u8(0xff, 0xd8), app2, sos, u8(0xaa), u8(0xff, 0xd9));

  const result = stripImageMetadata(jpeg);

  assert.ok(result.ok);
  assert.ok(contains(result.bytes, "ICC_PROFILE"));
});

/** Builds a PNG from chunks, computing no CRCs: the stripper never checks them. */
function png(chunks: Array<[string, Uint8Array]>): Uint8Array {
  const parts: Uint8Array[] = [u8(0x89, 0x50, 0x4e, 0x47, 0x0d, 0x0a, 0x1a, 0x0a)];
  for (const [type, data] of chunks) {
    const length = new Uint8Array(4);
    new DataView(length.buffer).setUint32(0, data.length);
    parts.push(length, asciiBytes(type), data, u8(0, 0, 0, 0));
  }
  return concat(...parts);
}

test("it removes PNG text and Exif chunks", () => {
  const input = png([
    ["IHDR", new Uint8Array(13)],
    ["tEXt", asciiBytes("Comment\0Shot from my balcony")],
    ["eXIf", concat(asciiBytes("II"), u8(0x2a, 0x00, 0x08, 0x00, 0x00, 0x00))],
    ["IDAT", u8(0x78, 0x9c, 0x03, 0x00, 0x00, 0x00, 0x00, 0x01)],
    ["IEND", new Uint8Array(0)],
  ]);

  const result = stripImageMetadata(input);

  assert.ok(result.ok);
  assert.equal(result.format, "png");
  assert.ok(!contains(result.bytes, "Shot from my balcony"));
  assert.ok(!contains(result.bytes, "eXIf"));
  assert.ok(contains(result.bytes, "IHDR"), "header must survive");
  assert.ok(contains(result.bytes, "IDAT"), "picture data must survive");
});

/** Builds a WebP RIFF file from chunks. */
function webp(chunks: Array<[string, Uint8Array]>): Uint8Array {
  const body: Uint8Array[] = [];
  for (const [type, data] of chunks) {
    const size = new Uint8Array(4);
    new DataView(size.buffer).setUint32(0, data.length, true);
    body.push(asciiBytes(type), size, data);
    if (data.length % 2 === 1) body.push(u8(0));
  }
  const payload = concat(...body);
  const header = new Uint8Array(12);
  header.set(asciiBytes("RIFF"), 0);
  new DataView(header.buffer).setUint32(4, 4 + payload.length, true);
  header.set(asciiBytes("WEBP"), 8);
  return concat(header, payload);
}

test("it removes the WebP Exif chunk and rewrites the RIFF size", () => {
  const input = webp([
    ["VP8 ", u8(1, 2, 3, 4)],
    ["EXIF", asciiBytes("GPSLatitude 23.78")],
  ]);

  const result = stripImageMetadata(input);

  assert.ok(result.ok);
  assert.equal(result.format, "webp");
  assert.ok(!contains(result.bytes, "GPSLatitude"));
  assert.ok(contains(result.bytes, "VP8 "), "picture data must survive");

  const declared = new DataView(
    result.bytes.buffer,
    result.bytes.byteOffset
  ).getUint32(4, true);
  assert.equal(declared, result.bytes.length - 8, "RIFF size must match the trimmed file");
});

test("it refuses a file that is not an image it understands", () => {
  const gif = stripImageMetadata(asciiBytes("GIF89a and then some bytes"));
  assert.equal(gif.ok, false);

  const empty = stripImageMetadata(new Uint8Array(0));
  assert.equal(empty.ok, false);
});

test("it refuses a truncated JPEG rather than guessing", () => {
  // Declares a 400-byte APP1 but the file ends immediately.
  const truncated = concat(u8(0xff, 0xd8, 0xff, 0xe1), big16(400), asciiBytes("short"));

  assert.equal(stripImageMetadata(truncated).ok, false);
});
