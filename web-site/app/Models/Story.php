<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Story extends Model
{
    public const AUDIENCE_ALL = 'all';

    public const AUDIENCE_MALE = 'male';

    public const AUDIENCE_FEMALE = 'female';

    /** @var list<string> */
    public const AUDIENCES = [
        self::AUDIENCE_ALL,
        self::AUDIENCE_MALE,
        self::AUDIENCE_FEMALE,
    ];

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'media_url', 'media_type', 'audience', 'expires_at', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        // users join'larında belirsizliği önlemek için tablo öneki zorunlu.
        return $query->where(function ($query) {
            $query->where('stories.expires_at', '>', now())
                ->orWhere(function ($nested) {
                    $nested->whereNull('stories.expires_at')
                        ->where('stories.created_at', '>', now()->subHours(24));
                });
        });
    }

    public function getIsVideoAttribute(): bool
    {
        if ($this->media_type === 'video') {
            return true;
        }

        return (bool) preg_match('/\.(mp4|webm|mov)(\?|$)/i', (string) $this->media_url);
    }

    public function isOfficial(): bool
    {
        return in_array((string) $this->audience, self::AUDIENCES, true);
    }

    public function visibleTo(?User $viewer): bool
    {
        if (! $this->isOfficial()) {
            return true;
        }

        if (! $viewer || $viewer->isAdmin()) {
            return true;
        }

        if ($this->audience === self::AUDIENCE_ALL) {
            return true;
        }

        return $this->audience === $viewer->gender;
    }

    public static function ensureAudienceColumn(): void
    {
        if (! Schema::hasTable('stories') || Schema::hasColumn('stories', 'audience')) {
            return;
        }

        try {
            Schema::table('stories', function ($table) {
                $table->string('audience', 16)->nullable();
            });
        } catch (\Throwable) {
            //
        }
    }

    public static function audienceLabel(?string $audience): string
    {
        return match ($audience) {
            self::AUDIENCE_ALL => 'Tüm üyeler',
            self::AUDIENCE_MALE => 'Erkekler',
            self::AUDIENCE_FEMALE => 'Kadınlar',
            default => 'Üye hikâyesi',
        };
    }
}
