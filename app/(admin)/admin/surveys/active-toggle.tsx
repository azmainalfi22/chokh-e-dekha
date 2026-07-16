"use client";

import { useRouter } from "next/navigation";
import { useTransition } from "react";
import { toast } from "sonner";
import { setSurveyActive } from "@/lib/actions/surveys";
import { Switch } from "@/components/ui/switch";

export function SurveyActiveToggle({
  id,
  active,
}: {
  id: number;
  active: boolean;
}) {
  const router = useRouter();
  const [pending, startTransition] = useTransition();

  return (
    <Switch
      checked={active}
      disabled={pending}
      aria-label="Toggle survey active"
      onCheckedChange={(next) =>
        startTransition(async () => {
          const result = await setSurveyActive(id, next);
          if (!result.ok) toast.error(result.error);
          else router.refresh();
        })
      }
    />
  );
}
