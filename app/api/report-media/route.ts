import { NextResponse, type NextRequest } from "next/server";
import { createClient, createServiceClient } from "@/lib/supabase/server";
import {
  stripImageMetadata,
  extensionFor,
  contentTypeFor,
} from "@/lib/image-metadata";

/**
 * Upload report evidence.
 *
 * Photos used to go straight from the browser to the storage API. That put two
 * things in the browser's hands that cannot stay there:
 *
 *  - Metadata removal. The wizard compresses photos with
 *    browser-image-compression, whose canvas re-encode happens to drop Exif, so
 *    in practice GPS was usually gone. But "usually" is doing a lot of work in
 *    that sentence: anything posting straight at the storage API skipped it, and
 *    report evidence is served from public URLs. A photograph taken before
 *    leaving the house publishes the reporter's home address.
 *  - The storage path. The wizard chose `${uid}/${batch}/${n}.jpg` by
 *    convention. The accompanying migration makes that the only path the API
 *    will accept, and this route is what produces it.
 *
 * The accompanying migration revokes INSERT on report-media from `authenticated`
 * altogether, so this route is not merely the preferred way in — it is the only
 * one. That is what makes the stripping an actual control rather than a habit:
 * a client posting straight at the storage API with an unmodified photograph is
 * refused by the bucket, not just discouraged by the wizard.
 *
 * Because direct inserts are revoked, the upload itself runs with the service
 * role. Every part of the destination path is derived server-side — the
 * authenticated user's id, a fresh UUID, and a loop index — so no caller-supplied
 * string reaches it.
 */

/** Matches the bucket's own size limit. */
const MAX_BYTES = 10 * 1024 * 1024;

/** Matches MAX_PHOTOS in the submit wizard and the cap in createReport. */
const MAX_FILES = 6;

export async function POST(request: NextRequest) {
  const supabase = await createClient();
  const {
    data: { user },
  } = await supabase.auth.getUser();

  if (!user) {
    return NextResponse.json({ error: "Not signed in" }, { status: 401 });
  }

  let form: FormData;
  try {
    form = await request.formData();
  } catch {
    return NextResponse.json({ error: "Malformed upload" }, { status: 400 });
  }

  const files = form.getAll("photos").filter((v): v is File => v instanceof File);

  if (files.length === 0) {
    return NextResponse.json({ error: "No photographs supplied" }, { status: 400 });
  }

  if (files.length > MAX_FILES) {
    return NextResponse.json(
      { error: `At most ${MAX_FILES} photographs per report` },
      { status: 400 }
    );
  }

  // One batch folder per submission, so a report's evidence stays together and
  // two submissions cannot collide.
  const batch = crypto.randomUUID();
  const paths: string[] = [];
  const storage = createServiceClient();

  for (const [index, file] of files.entries()) {
    if (file.size > MAX_BYTES) {
      return NextResponse.json(
        { error: `Photograph ${index + 1} is larger than 10MB` },
        { status: 413 }
      );
    }

    const original = new Uint8Array(await file.arrayBuffer());
    const stripped = stripImageMetadata(original);

    // A file whose metadata cannot be removed is refused rather than stored
    // untouched. Storing it would publish exactly what this route exists to
    // prevent.
    if (!stripped.ok) {
      return NextResponse.json(
        { error: `Photograph ${index + 1}: ${stripped.reason}` },
        { status: 415 }
      );
    }

    const path = `${user.id}/${batch}/${index + 1}.${extensionFor(stripped.format)}`;

    const { error } = await storage.storage
      .from("report-media")
      .upload(path, stripped.bytes, {
        contentType: contentTypeFor(stripped.format),
        upsert: false,
      });

    if (error) {
      return NextResponse.json(
        { error: `Photograph ${index + 1} could not be stored` },
        { status: 502 }
      );
    }

    paths.push(path);
  }

  return NextResponse.json({ paths });
}
