"use client";

import { useRouter } from "next/navigation";
import { useTransition } from "react";
import { ShieldCheck, ShieldOff } from "lucide-react";
import { toast } from "sonner";
import { setUserRole } from "@/lib/actions/admin";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";

export type UserRow = {
  id: string;
  displayName: string;
  city: string | null;
  role: string;
  createdAt: string;
  reportCount: number;
  isSelf: boolean;
};

export function UsersTable({ rows }: { rows: UserRow[] }) {
  const router = useRouter();
  const [pending, startTransition] = useTransition();

  function toggleRole(row: UserRow) {
    const next = row.role === "admin" ? "citizen" : "admin";
    startTransition(async () => {
      const result = await setUserRole(row.id, next);
      if (!result.ok) toast.error(result.error);
      else {
        toast.success(
          next === "admin"
            ? `${row.displayName} is now an admin`
            : `${row.displayName} is now a citizen`
        );
        router.refresh();
      }
    });
  }

  return (
    <div className="overflow-x-auto rounded-xl border">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>User</TableHead>
            <TableHead>City</TableHead>
            <TableHead>Reports</TableHead>
            <TableHead>Role</TableHead>
            <TableHead>Joined</TableHead>
            <TableHead className="text-right">Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {rows.map((u) => (
            <TableRow key={u.id}>
              <TableCell>
                <div className="flex items-center gap-2.5">
                  <Avatar className="size-8">
                    <AvatarFallback className="bg-brand-gradient text-[10px] font-semibold text-white">
                      {u.displayName
                        .split(/\s+/)
                        .map((w) => w[0])
                        .slice(0, 2)
                        .join("")
                        .toUpperCase()}
                    </AvatarFallback>
                  </Avatar>
                  <span className="font-medium">
                    {u.displayName}
                    {u.isSelf ? (
                      <span className="text-muted-foreground text-xs"> (you)</span>
                    ) : null}
                  </span>
                </div>
              </TableCell>
              <TableCell className="text-sm">
                {u.city ?? <span className="text-muted-foreground">—</span>}
              </TableCell>
              <TableCell className="text-sm">{u.reportCount}</TableCell>
              <TableCell>
                {u.role === "admin" ? (
                  <Badge className="bg-brand-gradient border-0 text-white">
                    Admin
                  </Badge>
                ) : (
                  <Badge variant="secondary">Citizen</Badge>
                )}
              </TableCell>
              <TableCell className="text-muted-foreground text-sm whitespace-nowrap">
                {new Date(u.createdAt).toLocaleDateString("en-GB", {
                  day: "numeric",
                  month: "short",
                  year: "numeric",
                })}
              </TableCell>
              <TableCell className="text-right">
                {!u.isSelf ? (
                  <Button
                    size="sm"
                    variant="outline"
                    disabled={pending}
                    onClick={() => toggleRole(u)}
                  >
                    {u.role === "admin" ? (
                      <>
                        <ShieldOff className="size-4" aria-hidden /> Demote
                      </>
                    ) : (
                      <>
                        <ShieldCheck className="size-4" aria-hidden /> Make admin
                      </>
                    )}
                  </Button>
                ) : null}
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </div>
  );
}
