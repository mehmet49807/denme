<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiModerationReport extends Model
{
    public const TYPE_DAILY = 'daily';
    public const TYPE_COMPLAINT = 'complaint';
    public const TYPE_SCAN = 'scan';

    protected $fillable = [
        'report_type',
        'title',
        'summary',
        'details',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
