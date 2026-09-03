<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('messages')) {
            return;
        }

        $hasUrl = Schema::hasColumn('messages', 'attachment_url');
        $hasType = Schema::hasColumn('messages', 'attachment_type');

        if ($hasUrl && $hasType) {
            return;
        }

        Schema::table('messages', function (Blueprint $table) use ($hasUrl, $hasType): void {
            if (! $hasUrl) {
                $table->string('attachment_url', 1000)->nullable()->after('message_text');
            }
            if (! $hasType) {
                $table->string('attachment_type', 20)->nullable()->after('attachment_url');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('messages')) {
            return;
        }

        $columns = [];
        if (Schema::hasColumn('messages', 'attachment_type')) {
            $columns[] = 'attachment_type';
        }
        if (Schema::hasColumn('messages', 'attachment_url')) {
            $columns[] = 'attachment_url';
        }

        if ($columns !== []) {
            Schema::table('messages', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }
};
