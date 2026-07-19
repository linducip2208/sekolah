<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_systems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('grade_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_system_id')->constrained()->cascadeOnDelete();
            $table->string('grade');        // A+, A, B+, B, C, D, F
            $table->decimal('min_percent', 5, 2);
            $table->decimal('max_percent', 5, 2);
            $table->decimal('gpa_point', 3, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained();
            $table->foreignId('exam_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('semester_id')->constrained();
            $table->unsignedSmallInteger('obtained_marks');
            $table->unsignedSmallInteger('total_marks');
            $table->decimal('percentage', 5, 2)->storedAs('ROUND((obtained_marks / total_marks) * 100, 2)');
            $table->string('grade')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'student_id', 'subject_id', 'semester_id', 'exam_id']);
            $table->index(['school_id', 'student_id', 'semester_id']);
        });

        Schema::create('report_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained();
            $table->decimal('total_percentage', 5, 2)->default(0);
            $table->string('overall_grade')->nullable();
            $table->decimal('gpa', 3, 2)->nullable();
            $table->unsignedSmallInteger('rank')->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->unique(['school_id', 'student_id', 'semester_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_cards');
        Schema::dropIfExists('marks');
        Schema::dropIfExists('grade_rules');
        Schema::dropIfExists('grade_systems');
    }
};
