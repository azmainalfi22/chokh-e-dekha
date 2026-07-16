import "server-only";
import { Resend } from "resend";
import { STATUS_LABELS, type Status } from "@/lib/constants";

/**
 * Status-change email (FR-05). No-ops gracefully when RESEND_API_KEY is
 * unset so the app works before email is configured.
 */
export async function sendStatusChangeEmail(opts: {
  to: string;
  reportTitle: string;
  reportId: number;
  newStatus: string;
  note?: string | null;
}): Promise<void> {
  const apiKey = process.env.RESEND_API_KEY;
  if (!apiKey) return;

  const resend = new Resend(apiKey);
  const site = process.env.NEXT_PUBLIC_SITE_URL ?? "http://localhost:3000";
  const statusLabel =
    STATUS_LABELS[opts.newStatus as Status] ?? opts.newStatus;

  try {
    await resend.emails.send({
      from:
        process.env.EMAIL_FROM ?? "Chokh-e-Dekha <onboarding@resend.dev>",
      to: opts.to,
      subject: `Your report is now ${statusLabel} — ${opts.reportTitle}`,
      html: `
        <div style="font-family:Inter,Arial,sans-serif;max-width:560px;margin:0 auto;padding:24px">
          <h2 style="background:linear-gradient(135deg,#F97316,#EF4444);-webkit-background-clip:text;background-clip:text;color:transparent;margin:0 0 4px">
            Chokh-e-Dekha
          </h2>
          <p style="color:#666;margin:0 0 20px">Your Eyes, Your Voice, Your City</p>
          <p>Your report <strong>${escapeHtml(opts.reportTitle)}</strong> has been updated to
            <strong>${statusLabel}</strong>.</p>
          ${opts.note ? `<p style="background:#f5f5f4;border-radius:8px;padding:12px">Official note: ${escapeHtml(opts.note)}</p>` : ""}
          <p>
            <a href="${site}/reports/${opts.reportId}"
               style="display:inline-block;background:linear-gradient(135deg,#F97316,#EF4444);color:#fff;text-decoration:none;padding:10px 18px;border-radius:8px;font-weight:600">
              View your report
            </a>
          </p>
          <p style="color:#999;font-size:12px;margin-top:24px">
            You receive these updates because you submitted this report on Chokh-e-Dekha.
          </p>
        </div>`,
    });
  } catch {
    // Email is best-effort; in-app notification is the reliable channel.
  }
}

function escapeHtml(s: string): string {
  return s
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;");
}
