/** Public URL for an object in the report-media bucket. */
export function reportMediaUrl(storagePath: string): string {
  return `${process.env.NEXT_PUBLIC_SUPABASE_URL}/storage/v1/object/public/report-media/${storagePath}`;
}
