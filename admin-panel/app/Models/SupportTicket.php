<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SupportTicket extends Model
{
    protected $fillable = [
        'user_id', 'name', 'email', 'subject', 'message', 'status', 'admin_reply', 'replied_at',
    ];

    protected function casts(): array
    {
        return [
            'replied_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function ensureTable(): bool
    {
        try {
            if (Schema::hasTable('support_tickets')) {
                return true;
            }

            Schema::create('support_tickets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('name', 120);
                $table->string('email', 190);
                $table->string('subject', 160);
                $table->text('message');
                $table->string('status', 20)->default('pending')->index();
                $table->text('admin_reply')->nullable();
                $table->timestamp('replied_at')->nullable();
                $table->timestamps();
            });

            return Schema::hasTable('support_tickets');
        } catch (\Throwable) {
            return Schema::hasTable('support_tickets');
        }
    }
}
