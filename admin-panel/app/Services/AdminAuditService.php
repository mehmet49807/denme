<?php

namespace App\Services;

use App\Models\AdminAuditLog;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminAuditService
{
    public function ensureTables(): void
    {
        try {
            if (! Schema::hasTable('admin_audit_logs')) {
                Schema::create('admin_audit_logs', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('admin_id')->nullable()->index();
                    $table->string('action', 80)->index();
                    $table->string('target_type', 80)->nullable()->index();
                    $table->unsignedBigInteger('target_id')->nullable()->index();
                    $table->string('summary', 500);
                    $table->json('meta')->nullable();
                    $table->string('ip_address', 45)->nullable();
                    $table->timestamp('created_at')->useCurrent()->index();
                });
            }

            if (! Schema::hasTable('admin_user_notes')) {
                Schema::create('admin_user_notes', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('user_id')->index();
                    $table->unsignedBigInteger('admin_id')->nullable()->index();
                    $table->text('note');
                    $table->boolean('is_pinned')->default(false);
                    $table->timestamps();
                });
            }
        } catch (\Throwable) {
            //
        }
    }

    public function ensureBroadcastColumns(): void
    {
        try {
            if (Schema::hasTable('admin_broadcasts') && ! Schema::hasColumn('admin_broadcasts', 'scheduled_at')) {
                Schema::table('admin_broadcasts', function (Blueprint $table) {
                    $table->timestamp('scheduled_at')->nullable()->index()->after('created_at');
                });
            }
            if (Schema::hasTable('admin_broadcasts') && ! Schema::hasColumn('admin_broadcasts', 'status')) {
                Schema::table('admin_broadcasts', function (Blueprint $table) {
                    $table->string('status', 20)->default('sent')->index()->after('sent_count');
                });
            }
        } catch (\Throwable) {
            //
        }
    }

    /**
     * @param  array<string, mixed>|null  $meta
     */
    public function log(
        string $action,
        string $summary,
        ?string $targetType = null,
        ?int $targetId = null,
        ?array $meta = null,
        ?int $adminId = null,
    ): void {
        $this->ensureTables();

        if (! Schema::hasTable('admin_audit_logs')) {
            return;
        }

        try {
            AdminAuditLog::query()->create([
                'admin_id' => $adminId ?? optional(auth()->user())->id,
                'action' => Str::limit($action, 80, ''),
                'target_type' => $targetType,
                'target_id' => $targetId,
                'summary' => Str::limit($summary, 500, ''),
                'meta' => $meta,
                'ip_address' => request()?->ip(),
                'created_at' => now(),
            ]);
        } catch (\Throwable) {
            //
        }
    }
}
