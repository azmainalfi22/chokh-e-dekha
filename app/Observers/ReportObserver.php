<?php

namespace App\Observers;

use App\Models\Report;
use App\Services\CacheService;
use Illuminate\Support\Facades\Log;

class ReportObserver
{
    protected CacheService $cache;

    public function __construct(CacheService $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Handle the Report "created" event.
     */
    public function created(Report $report): void
    {
        // ✅ Clear cache when new report is created
        $this->cache->clearDashboardCache($report->user_id);
        $this->cache->clearGlobalCache();

        Log::info('Report created', [
            'report_id' => $report->id,
            'user_id' => $report->user_id,
            'category' => $report->category,
        ]);
    }

    /**
     * Handle the Report "updated" event.
     */
    public function updated(Report $report): void
    {
        // ✅ Clear cache when report is updated
        $this->cache->clearDashboardCache($report->user_id);
        $this->cache->clearGlobalCache();

        // ✅ Log status changes
        if ($report->wasChanged('status')) {
            Log::info('Report status changed', [
                'report_id' => $report->id,
                'old_status' => $report->getOriginal('status'),
                'new_status' => $report->status,
            ]);
        }
    }

    /**
     * Handle the Report "deleted" event.
     */
    public function deleted(Report $report): void
    {
        // ✅ Clear cache when report is deleted
        $this->cache->clearDashboardCache($report->user_id);
        $this->cache->clearGlobalCache();

        Log::warning('Report deleted', [
            'report_id' => $report->id,
            'user_id' => $report->user_id,
        ]);
    }
}

