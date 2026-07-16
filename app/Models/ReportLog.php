<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportLog extends Model
{
    protected $fillable = [
        'report_id',
        'actor_id',
        'action',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the report that this log belongs to
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    /**
     * Get the user who performed this action
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Get a human-readable description of this log entry
     */
    public function getDescriptionAttribute(): string
    {
        return match($this->action) {
            'created' => 'Report submitted',
            'status_changed' => 'Status changed to ' . ($this->meta['new_status'] ?? 'unknown'),
            'assigned' => 'Assigned to ' . ($this->meta['assigned_to_name'] ?? 'officer'),
            'note_added' => 'Admin note added',
            'sla_breach' => 'SLA deadline breached',
            'escalated' => 'Escalated to level ' . ($this->meta['level'] ?? ''),
            'resolved' => 'Report resolved',
            'rejected' => 'Report rejected',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }

    /**
     * Get the icon for this log entry
     */
    public function getIconAttribute(): string
    {
        return match($this->action) {
            'created' => 'plus-circle',
            'status_changed' => 'refresh',
            'assigned' => 'user-check',
            'note_added' => 'message-square',
            'sla_breach' => 'alert-triangle',
            'escalated' => 'arrow-up-circle',
            'resolved' => 'check-circle',
            'rejected' => 'x-circle',
            default => 'circle',
        };
    }

    /**
     * Get the color for this log entry
     */
    public function getColorAttribute(): string
    {
        return match($this->action) {
            'created' => 'blue',
            'status_changed' => 'purple',
            'assigned' => 'indigo',
            'note_added' => 'cyan',
            'sla_breach' => 'red',
            'escalated' => 'orange',
            'resolved' => 'green',
            'rejected' => 'red',
            default => 'gray',
        };
    }
}
