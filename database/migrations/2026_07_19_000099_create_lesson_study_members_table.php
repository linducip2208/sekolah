<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_study_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_study_id')->constrained('lesson_studies')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->default('observer')->comment('lead, observer, facilitator');
            $table->timestamps();

            $table->unique(['lesson_study_id', 'staff_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_study_members');
    }
};
