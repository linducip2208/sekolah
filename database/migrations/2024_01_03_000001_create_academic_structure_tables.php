<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mediums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
            $table->unique(['school_id', 'name']);
        });

        Schema::create('class_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medium_id')->constrained('mediums');
            $table->string('name');
            $table->timestamps();
            $table->unique(['school_id', 'name', 'medium_id']);
        });

        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
            $table->unique(['school_id', 'name']);
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medium_id')->constrained('mediums');
            $table->string('name');
            $table->string('code', 30)->nullable();
            $table->enum('type', ['theory', 'practical'])->default('theory');
            $table->unsignedSmallInteger('credit_hours')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'medium_id']);
        });

        Schema::create('class_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_room_id')->constrained();
            $table->foreignId('section_id')->constrained();
            $table->foreignId('medium_id')->constrained('mediums');
            $table->foreignId('academic_year_id')->constrained();
            $table->foreignId('class_teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['class_room_id', 'section_id', 'academic_year_id']);
            $table->index(['school_id', 'academic_year_id']);
        });

        Schema::create('class_section_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['class_section_id', 'subject_id']);
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_section_id')->nullable()->constrained()->nullOnDelete();
            $table->string('admission_no', 50)->nullable();
            $table->date('admission_date')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('blood_group', 10)->nullable();
            $table->text('address')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone', 30)->nullable();
            $table->boolean('has_hostel')->default(false);
            $table->boolean('has_transport')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['school_id', 'admission_no']);
            $table->index(['school_id', 'class_section_id']);
        });

        Schema::create('parent_student', function (Blueprint $table) {
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->primary(['parent_id', 'student_id']);
        });

        Schema::create('staffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('employee_id', 50)->nullable();
            $table->string('department')->nullable();
            $table->string('designation')->nullable();
            $table->date('joining_date')->nullable();
            $table->unsignedInteger('basic_salary')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['school_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staffs');
        Schema::dropIfExists('parent_student');
        Schema::dropIfExists('students');
        Schema::dropIfExists('class_section_subjects');
        Schema::dropIfExists('class_sections');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('class_rooms');
        Schema::dropIfExists('mediums');
    }
};
