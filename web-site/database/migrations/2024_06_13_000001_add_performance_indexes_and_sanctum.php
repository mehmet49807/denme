<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->tryIndex('messages', function (Blueprint $table) {
            $table->index(['receiver_id', 'is_read', 'hidden_for_receiver_at'], 'messages_receiver_unread_idx');
        });
        $this->tryIndex('messages', function (Blueprint $table) {
            $table->index(['sender_id', 'receiver_id', 'id'], 'messages_thread_latest_idx');
        });

        $this->tryIndex('user_notifications', function (Blueprint $table) {
            $table->index(['user_id', 'read_at', 'created_at'], 'user_notifications_user_read_created_idx');
        });

        $this->tryIndex('likes', function (Blueprint $table) {
            $table->index(['user_id', 'post_id'], 'likes_user_post_idx');
        });
        $this->tryIndex('likes', function (Blueprint $table) {
            $table->index(['post_id'], 'likes_post_id_idx');
        });

        $this->tryIndex('posts', function (Blueprint $table) {
            $table->index(['is_active', 'user_id', 'created_at'], 'posts_active_user_created_idx');
        });

        $this->tryIndex('premium_subscriptions', function (Blueprint $table) {
            $table->index(['user_id', 'is_active', 'expires_at'], 'premium_subscriptions_user_active_exp_idx');
        });

        $this->tryIndex('blocks', function (Blueprint $table) {
            $table->index(['blocker_id', 'blocked_id'], 'blocks_blocker_blocked_idx');
        });
        $this->tryIndex('blocks', function (Blueprint $table) {
            $table->index(['blocked_id', 'blocker_id'], 'blocks_blocked_blocker_idx');
        });

        $this->tryIndex('stories', function (Blueprint $table) {
            $table->index(['user_id', 'expires_at'], 'stories_user_expires_idx');
        });

        $this->tryIndex('device_tokens', function (Blueprint $table) {
            $table->index(['user_id'], 'device_tokens_user_id_idx');
        });

        if (! Schema::hasTable('personal_access_tokens')) {
            try {
                Schema::create('personal_access_tokens', function (Blueprint $table) {
                    $table->id();
                    $table->morphs('tokenable');
                    $table->string('name');
                    $table->string('token', 64)->unique();
                    $table->text('abilities')->nullable();
                    $table->timestamp('last_used_at')->nullable();
                    $table->timestamp('expires_at')->nullable();
                    $table->timestamps();
                });
            } catch (\Throwable) {
                //
            }
        }
    }

    public function down(): void
    {
        foreach ([
            ['messages', 'messages_receiver_unread_idx'],
            ['messages', 'messages_thread_latest_idx'],
            ['user_notifications', 'user_notifications_user_read_created_idx'],
            ['likes', 'likes_user_post_idx'],
            ['likes', 'likes_post_id_idx'],
            ['posts', 'posts_active_user_created_idx'],
            ['premium_subscriptions', 'premium_subscriptions_user_active_exp_idx'],
            ['blocks', 'blocks_blocker_blocked_idx'],
            ['blocks', 'blocks_blocked_blocker_idx'],
            ['stories', 'stories_user_expires_idx'],
            ['device_tokens', 'device_tokens_user_id_idx'],
        ] as [$table, $index]) {
            $this->tryDropIndex($table, $index);
        }
    }

    private function tryIndex(string $table, callable $callback): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        try {
            Schema::table($table, $callback);
        } catch (\Throwable) {
            // Index already exists or column missing on this host.
        }
    }

    private function tryDropIndex(string $table, string $index): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($index) {
                $blueprint->dropIndex($index);
            });
        } catch (\Throwable) {
            //
        }
    }
};
