<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('max_score')->default(100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('kpi_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->constrained('kpi_templates')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('weight')->default(1); // relative weight
            $table->unsignedSmallInteger('max_score')->default(10);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('kpi_appraisals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staffs');
            $table->foreignId('template_id')->constrained('kpi_templates');
            $table->foreignId('reviewer_id')->constrained('users');
            $table->string('period'); // "2025-Genap" or "2025-1"
            $table->enum('status', ['draft', 'submitted', 'finalized'])->default('draft');
            $table->unsignedSmallInteger('total_score')->nullable();
            $table->text('reviewer_notes')->nullable();
            $table->text('staff_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'staff_id', 'period']);
        });

        Schema::create('kpi_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appraisal_id')->constrained('kpi_appraisals')->cascadeOnDelete();
            $table->foreignId('criteria_id')->constrained('kpi_criteria')->cascadeOnDelete();
            $table->unsignedSmallInteger('score');
            $table->text('evidence')->nullable();
            $table->timestamps();
            $table->unique(['appraisal_id', 'criteria_id']);
        });

        Schema::create('kpi_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staffs');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('target_value')->nullable();
            $table->string('actual_value')->nullable();
            $table->enum('status', ['in_progress', 'achieved', 'missed'])->default('in_progress');
            $table->date('due_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_goals');
        Schema::dropIfExists('kpi_scores');
        Schema::dropIfExists('kpi_appraisals');
        Schema::dropIfExists('kpi_criteria');
        Schema::dropIfExists('kpi_templates');
    }
};
