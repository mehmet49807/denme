<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'sender_id', 'receiver_id', 'message_text', 'is_read', 'read_at',
        'hidden_for_sender_at', 'hidden_for_receiver_at', 'created_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (Message $message) {
            if (!$message->created_at) {
                $message->created_at = now();
            }
        });

        static::created(function (Message $message) {
            app(\App\Services\NotificationService::class)->notifyNewMessage($message);
            \App\Jobs\RunAiModerationJob::dispatchAfterResponse('message', $message->id);
        });
    }

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
            'hidden_for_sender_at' => 'datetime',
            'hidden_for_receiver_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}

