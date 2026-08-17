<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('ai_models', 'priority')) {
            Schema::table('ai_models', function (Blueprint $table) {
                $table->unsignedInteger('priority')->default(0)->after('is_active');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ai_models', 'priority')) {
            Schema::table('ai_models', function (Blueprint $table) {
                $table->dropColumn('priority');
            });
        }
    }
};
