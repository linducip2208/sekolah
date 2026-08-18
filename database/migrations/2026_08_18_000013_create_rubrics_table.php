<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rubric
        Schema::create('rubrics', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->text('description')->nullable();
            $t->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $t->unsignedSmallInteger('max_score')->default(4);
            $t->timestamps();
            $t->softDeletes();
            $t->index(['school_id', 'subject_id']);
        });

        // Rubric Criteria
        Schema::create('rubric_criteria', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('rubric_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->text('description')->nullable();
            $t->unsignedSmallInteger('weight')->default(1);
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
            $t->softDeletes();
            $t->index(['school_id', 'rubric_id']);
        });

        // Rubric Levels
        Schema::create('rubric_levels', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('criteria_id')->constrained('rubric_criteria')->cascadeOnDelete();
            $t->string('level_name');
            $t->unsignedSmallInteger('score');
            $t->text('description')->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['school_id', 'criteria_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rubric_levels');
        Schema::dropIfExists('rubric_criteria');
        Schema::dropIfExists('rubrics');
    }
};
