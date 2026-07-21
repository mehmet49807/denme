<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->tryIndex('messages', function (Blueprint $table) {
            $table->index(['receiver_id', 'sender_id', 'id'], 'messages_receiver_sender_id_idx');
        });

        $this->tryIndex('users', function (Blueprint $table) {
            $table->index(['role', 'is_banned', 'gender', 'last_active_at'], 'users_discovery_active_idx');
        });

        $this->tryIndex('users', function (Blueprint $table) {
            $table->index(['boost_until'], 'users_boost_until_idx');
        });

        $this->tryIndex('posts', function (Blueprint $table) {
            $table->index(['user_id', 'is_active', 'created_at'], 'posts_user_active_created_idx');
        });
    }

    public function down(): void
    {
        foreach ([
            ['messages', 'messages_receiver_sender_id_idx'],
            ['users', 'users_discovery_active_idx'],
            ['users', 'users_boost_until_idx'],
            ['posts', 'posts_user_active_created_idx'],
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
            //
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
