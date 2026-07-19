<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_study_reflections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_study_id')->constrained('lesson_studies')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
            $table->text('reflection_text');
            $table->text('strength_points')->nullable();
            $table->text('improvement_points')->nullable();
            $table->text('action_plan')->nullable();
            $table->timestamps();

            $table->unique(['lesson_study_id', 'staff_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_study_reflections');
    }
};
