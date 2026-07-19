<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_study_observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_study_id')->constrained('lesson_studies')->cascadeOnDelete();
            $table->foreignId('observer_id')->constrained('users')->cascadeOnDelete();
            $table->string('observation_type')->default('student_engagement')->comment('student_engagement, teaching_method, class_management, material_clarity');
            $table->text('notes');
            $table->tinyInteger('rating')->nullable()->comment('1-5');
            $table->timestamp('observed_at')->nullable();
            $table->timestamps();

            $table->index(['lesson_study_id', 'observer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_study_observations');
    }
};
