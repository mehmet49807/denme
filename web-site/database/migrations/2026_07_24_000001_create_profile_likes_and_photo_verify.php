<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('profile_likes')) {
            Schema::create('profile_likes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('liker_id');
                $table->unsignedBigInteger('liked_id');
                $table->timestamp('created_at')->useCurrent();
                $table->unique(['liker_id', 'liked_id'], 'profile_likes_pair_uidx');
                $table->index(['liked_id', 'created_at'], 'profile_likes_liked_created_idx');
                $table->index(['liker_id', 'created_at'], 'profile_likes_liker_created_idx');
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'photo_verify_status')) {
                    $table->string('photo_verify_status', 20)->nullable()->after('is_verified');
                }
                if (! Schema::hasColumn('users', 'photo_verify_selfie_url')) {
                    $table->string('photo_verify_selfie_url', 500)->nullable()->after('photo_verify_status');
                }
                if (! Schema::hasColumn('users', 'photo_verify_submitted_at')) {
                    $table->timestamp('photo_verify_submitted_at')->nullable()->after('photo_verify_selfie_url');
                }
                if (! Schema::hasColumn('users', 'photo_verify_reviewed_at')) {
                    $table->timestamp('photo_verify_reviewed_at')->nullable()->after('photo_verify_submitted_at');
                }
                if (! Schema::hasColumn('users', 'photo_verify_note')) {
                    $table->string('photo_verify_note', 255)->nullable()->after('photo_verify_reviewed_at');
                }
                if (! Schema::hasColumn('users', 'profile_verified_at')) {
                    $table->timestamp('profile_verified_at')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_likes');
    }
};
