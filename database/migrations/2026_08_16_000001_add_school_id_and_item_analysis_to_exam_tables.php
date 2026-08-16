<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_questions', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->after('exam_id')->constrained()->cascadeOnDelete();
            $table->decimal('difficulty_index', 5, 3)->nullable();
            $table->decimal('discrimination_index', 5, 3)->nullable();
            $table->json('distractor_analysis')->nullable();
            $table->index('school_id');
        });

        Schema::table('exam_results', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->after('exam_id')->constrained()->cascadeOnDelete();
            $table->index('school_id');
        });

        DB::statement('UPDATE exam_questions eq JOIN exams e ON e.id = eq.exam_id SET eq.school_id = e.school_id WHERE eq.school_id IS NULL');
        DB::statement('UPDATE exam_results er JOIN exams e ON e.id = er.exam_id SET er.school_id = e.school_id WHERE er.school_id IS NULL');
    }

    public function down(): void
    {
        Schema::table('exam_questions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_id');
            $table->dropColumn(['difficulty_index', 'discrimination_index', 'distractor_analysis']);
        });

        Schema::table('exam_results', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_id');
        });
    }
};
