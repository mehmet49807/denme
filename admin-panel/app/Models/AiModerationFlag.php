<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiModerationFlag extends Model
{
    public const TYPE_MESSAGE = 'message';
    public const TYPE_POST = 'post';
    public const TYPE_STORY = 'story';
    public const TYPE_PROFILE = 'profile';
    public const TYPE_REPORT = 'report';

    public const CATEGORY_IBAN = 'iban';
    public const CATEGORY_MONEY_REQUEST = 'money_request';
    public const CATEGORY_PHONE = 'phone';
    public const CATEGORY_SOCIAL_MEDIA = 'social_media';
    public const CATEGORY_FRAUD = 'fraud';
    public const CATEGORY_FAKE_PROFILE = 'fake_profile';
    public const CATEGORY_OTHER = 'other';

    public const STATUS_PENDING = 'pending';
    public const STATUS_REVIEWED = 'reviewed';
    public const STATUS_ACTIONED = 'actioned';
    public const STATUS_DISMISSED = 'dismissed';

    protected $fillable = [
        'user_id',
        'content_type',
        'content_id',
        'category',
        'severity',
        'source',
        'status',
        'content_excerpt',
        'ai_reason',
        'ai_confidence',
        'admin_notes',
        'resolved_by',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'ai_confidence' => 'float',
            'resolved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function categoryLabel(): string
    {
        return match ($this->category) {
            self::CATEGORY_IBAN => 'IBAN paylaşımı',
            self::CATEGORY_MONEY_REQUEST => 'Para talebi',
            self::CATEGORY_PHONE => 'Telefon numarası',
            self::CATEGORY_SOCIAL_MEDIA => 'Sosyal medya yönlendirme',
            self::CATEGORY_FRAUD => 'Dolandırıcılık',
            self::CATEGORY_FAKE_PROFILE => 'Sahte profil',
            default => 'Diğer',
        };
    }

    public function contentTypeLabel(): string
    {
        return match ($this->content_type) {
            self::TYPE_MESSAGE => 'Mesaj',
            self::TYPE_POST => 'Gönderi',
            self::TYPE_STORY => 'Hikaye',
            self::TYPE_PROFILE => 'Profil',
            self::TYPE_REPORT => 'Şikayet',
            default => $this->content_type,
        };
    }
}
