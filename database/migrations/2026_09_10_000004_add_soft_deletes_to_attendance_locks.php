<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('attendance_locks') && ! Schema::hasColumn('attendance_locks', 'deleted_at')) {
            Schema::table('attendance_locks', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('attendance_locks') && Schema::hasColumn('attendance_locks', 'deleted_at')) {
            Schema::table('attendance_locks', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
