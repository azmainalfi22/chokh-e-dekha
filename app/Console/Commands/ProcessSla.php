<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Report;

class ProcessSla extends Command
{
    protected $signature = 'sla:tick';
    protected $description = 'Process SLA breaches and escalate overdue reports';

    public function handle(): int
    {
        $now = now();
        $affected = 0;

        $query = Report::query()
            ->whereIn('status', ['pending','in_progress'])
            ->whereNotNull('sla_due_at')
            ->where('sla_due_at', '<=', $now);

        $query->chunkById(500, function ($batch) use (&$affected) {
            foreach ($batch as $report) {
                $report->priority = $report->priority ?: 'high';
                $report->escalation_level = (int) $report->escalation_level + 1;
                $report->escalated_at = now();
                $report->status_updated_at = now();
                $report->save();

                // Log
                \DB::table('report_logs')->insert([
                    'report_id' => $report->id,
                    'actor_id' => null,
                    'action' => 'sla_breach',
                    'meta' => json_encode(['level' => $report->escalation_level]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $affected++;
            }
        });

        $this->info("SLA processed: {$affected} reports");
        return self::SUCCESS;
    }
}


