<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('curriculum_competencies', function (Blueprint $table) {
            $table->json('mapping_rules')->nullable()->after('indicators');
        });
    }

    public function down(): void
    {
        Schema::table('curriculum_competencies', function (Blueprint $table) {
            $table->dropColumn('mapping_rules');
        });
    }
};
