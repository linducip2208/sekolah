<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop and recreate if table exists but is incomplete (missing FKs)
        if (Schema::hasTable('student_observations')) {
            Schema::dropIfExists('observation_scores');
            Schema::dropIfExists('student_observations');
        }

        Schema::create('student_observations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('student_id')->constrained()->restrictOnDelete();
            $t->foreignId('observer_id')->constrained('users')->restrictOnDelete();
            $t->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('rubric_id')->nullable()->constrained()->nullOnDelete();
            $t->date('date');
            $t->enum('observation_type', ['akademik', 'non_akademik', 'sosial', 'emosional']);
            $t->text('overall_notes')->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['school_id', 'student_id']);
            $t->index(['school_id', 'observer_id']);
        });

        Schema::create('observation_scores', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('observation_id')->constrained('student_observations')->cascadeOnDelete();
            $t->foreignId('rubric_criteria_id')->nullable()->constrained('rubric_criteria')->nullOnDelete();
            $t->unsignedSmallInteger('score')->default(0);
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['school_id', 'observation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observation_scores');
        Schema::dropIfExists('student_observations');
    }
};
