<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pkg_observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pkg_assessment_id')->constrained('pkg_assessments')->cascadeOnDelete();
            $table->foreignId('class_section_id')->nullable()->constrained('class_sections')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->date('observation_date');
            $table->text('observation_notes')->nullable();
            $table->text('class_atmosphere')->nullable();
            $table->text('student_engagement')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pkg_observations');
    }
};
