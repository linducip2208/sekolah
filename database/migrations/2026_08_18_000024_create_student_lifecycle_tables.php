<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('from_class_section_id')->nullable()->constrained('class_sections')->nullOnDelete();
            $table->foreignId('to_class_section_id')->nullable()->constrained('class_sections')->nullOnDelete();
            $table->string('academic_year', 20);
            $table->enum('status', ['enrolled', 'promoted', 'graduated', 'transferred', 'dropped'])->default('enrolled');
            $table->date('effective_date');
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'academic_year', 'status']);
        });

        Schema::create('student_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('from_school_name', 200)->nullable();
            $table->string('to_school_name', 200)->nullable();
            $table->date('transfer_date');
            $table->text('reason')->nullable();
            $table->string('document_no', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'student_id']);
        });

        Schema::create('student_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('color', 20)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'name']);
        });

        Schema::create('student_tag_pivot', function (Blueprint $table) {
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('student_tag_id')->constrained('student_tags')->cascadeOnDelete();
            $table->primary(['student_id', 'student_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_tag_pivot');
        Schema::dropIfExists('student_tags');
        Schema::dropIfExists('student_transfers');
        Schema::dropIfExists('student_enrollments');
    }
};
