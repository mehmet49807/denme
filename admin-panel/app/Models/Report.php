<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'reporter_id', 'reported_id', 'reason', 'status',
        'admin_notes', 'resolved_by', 'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Report $report) {
            \App\Jobs\RunAiModerationJob::dispatchAfterResponse('report', $report->id);
        });
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reported()
    {
        return $this->belongsTo(User::class, 'reported_id');
    }
}
