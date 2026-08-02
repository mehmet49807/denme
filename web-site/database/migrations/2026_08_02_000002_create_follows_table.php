<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // follows: follower_id → following_id
        if (Schema::hasTable('follows')) {
            return;
        }

        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('follower_id');
            $table->unsignedBigInteger('following_id');
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['follower_id', 'following_id'], 'follows_pair_uidx');
            $table->index(['following_id', 'created_at'], 'follows_following_created_idx');
            $table->index(['follower_id', 'created_at'], 'follows_follower_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follows');
    }
};

