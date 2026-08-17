<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teaching_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('class_section_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->date('journal_date');
            $table->text('material')->nullable();
            $table->json('competency_ids')->nullable(); // links to curriculum_competencies (CP/TP/ATP)
            $table->text('learning_activity')->nullable();
            $table->text('attendance_summary')->nullable();
            $table->text('student_participation')->nullable();
            $table->text('homework')->nullable();
            $table->text('notes')->nullable();
            $table->text('reflection')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'teacher_id', 'journal_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_journals');
    }
};
