<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pkg_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pkg_assessment_id')->constrained('pkg_assessments')->cascadeOnDelete();
            $table->foreignId('pkg_competency_id')->constrained('pkg_competencies')->cascadeOnDelete();
            $table->decimal('score', 5, 2)->default(0);
            $table->text('evidence_notes')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pkg_scores');
    }
};
