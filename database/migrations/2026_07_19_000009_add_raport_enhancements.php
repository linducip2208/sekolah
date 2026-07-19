<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_cards', function (Blueprint $t) {
            $t->json('competency_scores')->nullable()->after('remarks');
            $t->text('extracurricular_notes')->nullable()->after('competency_scores');
            $t->json('attendance_summary')->nullable()->after('extracurricular_notes');
            $t->text('teacher_notes')->nullable()->after('attendance_summary');
        });
    }

    public function down(): void
    {
        Schema::table('report_cards', function (Blueprint $t) {
            $t->dropColumn(['competency_scores', 'extracurricular_notes', 'attendance_summary', 'teacher_notes']);
        });
    }
};
