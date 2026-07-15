<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileView extends Model
{
    protected $fillable = [
        'viewer_id',
        'viewed_id',
    ];

    public function viewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viewer_id');
    }

    public function viewed(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viewed_id');
    }

    public static function record(User $viewer, User $viewed): void
    {
        if ($viewer->id === $viewed->id) {
            return;
        }

        $recent = static::query()
            ->where('viewer_id', $viewer->id)
            ->where('viewed_id', $viewed->id)
            ->where('created_at', '>=', now()->subHours(6))
            ->exists();

        if ($recent) {
            return;
        }

        static::create([
            'viewer_id' => $viewer->id,
            'viewed_id' => $viewed->id,
        ]);
    }
}
