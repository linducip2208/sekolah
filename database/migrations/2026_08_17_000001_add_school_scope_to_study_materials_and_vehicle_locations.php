<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('study_materials', 'school_id')) {
            Schema::table('study_materials', function (Blueprint $table) {
                $table->foreignId('school_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
                $table->index('school_id');
            });

            DB::statement('UPDATE study_materials sm JOIN lessons l ON l.id = sm.lesson_id SET sm.school_id = l.school_id WHERE sm.school_id IS NULL');
        }

        if (!Schema::hasColumn('study_materials', 'deleted_at')) {
            Schema::table('study_materials', fn (Blueprint $table) => $table->softDeletes());
        }

        if (!Schema::hasColumn('vehicle_locations', 'deleted_at')) {
            Schema::table('vehicle_locations', fn (Blueprint $table) => $table->softDeletes());
        }
    }

    public function down(): void
    {
        Schema::table('study_materials', function (Blueprint $table) {
            $table->dropColumn('school_id');
            $table->dropColumn('deleted_at');
        });

        Schema::table('vehicle_locations', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};
