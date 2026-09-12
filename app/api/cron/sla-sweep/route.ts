import { NextResponse, type NextRequest } from "next/server";
import { createServiceClient } from "@/lib/supabase/server";

/**
 * Runs the SLA breach sweep.
 *
 * sla_due_at was computed and shown but never acted on, so a deadline passed in
 * silence. Beyond the queue looking wrong, that kept a door shut: a breach is
 * what makes a report eligible for the official GRS and 333 rails, so until the
 * breach was recognised the citizen's route to escalate stayed closed.
 *
 * The work is in sweep_sla_breaches(), which is idempotent by construction —
 * it writes the level a report *should* be at rather than incrementing — so a
 * duplicate or retried invocation is harmless. This route is only the trigger.
 *
 * Guarded by CRON_SECRET. Vercel Cron sends it as a bearer token automatically
 * when the variable is set on the project. If the variable is not set the route
 * refuses rather than running open, because an unauthenticated endpoint that
 * writes notifications to every overdue reporter is worth abusing.
 */
export async function GET(request: NextRequest) {
  const secret = process.env.CRON_SECRET;

  if (!secret) {
    return NextResponse.json(
      { error: "CRON_SECRET is not configured" },
      { status: 503 }
    );
  }

  const authorization = request.headers.get("authorization");

  if (authorization !== `Bearer ${secret}`) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
  }

  const supabase = createServiceClient();
  const { data, error } = await supabase.rpc("sweep_sla_breaches");

  if (error) {
    return NextResponse.json({ error: "Sweep failed" }, { status: 500 });
  }

  // The function returns a single row of counts.
  const result = Array.isArray(data) ? data[0] : data;

  return NextResponse.json({
    ok: true,
    escalatedToLevel1: result?.escalated_to_1 ?? 0,
    escalatedToLevel2: result?.escalated_to_2 ?? 0,
  });
}
