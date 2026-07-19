<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained();
            $table->string('title');
            $table->enum('type', ['online', 'offline'])->default('offline');
            $table->datetime('start_at')->nullable();
            $table->datetime('end_at')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->unsignedSmallInteger('total_marks')->default(100);
            $table->unsignedSmallInteger('pass_marks')->default(40);
            $table->boolean('shuffle_questions')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'class_section_id']);
        });

        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->text('question');
            $table->enum('type', ['mcq', 'true_false', 'essay'])->default('mcq');
            $table->json('options')->nullable();    // [{text, is_correct}]
            $table->text('correct_answer')->nullable();
            $table->unsignedSmallInteger('marks')->default(1);
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('obtained_marks')->default(0);
            $table->enum('status', ['pending', 'passed', 'failed'])->default('pending');
            $table->datetime('started_at')->nullable();
            $table->datetime('submitted_at')->nullable();
            $table->json('answers')->nullable();   // student answers
            $table->timestamps();
            $table->unique(['exam_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_results');
        Schema::dropIfExists('exam_questions');
        Schema::dropIfExists('exams');
    }
};
