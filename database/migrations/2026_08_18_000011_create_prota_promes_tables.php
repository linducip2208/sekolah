<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PROTA — Program Tahunan
        Schema::create('prota_programs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('staff_id')->nullable()->constrained('staffs')->nullOnDelete();
            $t->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('academic_year_id')->nullable()->constrained()->nullOnDelete();
            $t->json('competencies')->nullable();
            $t->json('target_completion')->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['school_id', 'academic_year_id']);
            $t->index(['school_id', 'staff_id']);
        });

        // PROMES — Program Semester
        Schema::create('promes_programs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('staff_id')->nullable()->constrained('staffs')->nullOnDelete();
            $t->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('semester_id')->nullable()->constrained()->nullOnDelete();
            $t->unsignedTinyInteger('week_number');
            $t->text('activity_description')->nullable();
            $t->unsignedSmallInteger('allocation_hours')->default(0);
            $t->enum('status', ['draft', 'approved'])->default('draft');
            $t->timestamps();
            $t->softDeletes();
            $t->index(['school_id', 'semester_id']);
            $t->index(['school_id', 'staff_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promes_programs');
        Schema::dropIfExists('prota_programs');
    }
};
