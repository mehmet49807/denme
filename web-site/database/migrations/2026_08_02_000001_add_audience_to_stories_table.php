<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('stories') || Schema::hasColumn('stories', 'audience')) {
            return;
        }

        Schema::table('stories', function (Blueprint $table) {
            $table->string('audience', 16)->nullable()->after('media_type');
            $table->index('audience', 'stories_audience_idx');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('stories') || ! Schema::hasColumn('stories', 'audience')) {
            return;
        }

        Schema::table('stories', function (Blueprint $table) {
            $table->dropIndex('stories_audience_idx');
            $table->dropColumn('audience');
        });
    }
};
