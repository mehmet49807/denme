<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique();          // READ-ONLY after creation
            $table->string('first_name', 100);                 // PRIVATE
            $table->string('last_name', 100);                  // PRIVATE
            $table->string('email', 190)->unique();            // PRIVATE
            $table->string('phone', 30);                       // PRIVATE
            $table->string('password');
            $table->enum('gender', ['male', 'female']);
            $table->string('city', 80);
            $table->string('district', 80);
            $table->string('profile_photo')->nullable();
            $table->string('bio', 500)->nullable();
            $table->enum('role', ['user', 'admin'])->default('user');
            $table->enum('status', ['active', 'banned'])->default('active');
            $table->boolean('is_premium')->default(false);     // men only
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index(['gender', 'status']);
            $table->index(['city', 'district']);
        });

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
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('users');
    }
};
