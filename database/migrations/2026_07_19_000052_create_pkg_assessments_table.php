<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pkg_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assessor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->enum('semester', ['1', '2'])->default('1');
            $table->date('assessment_date');
            $table->enum('type', ['self', 'peer', 'supervisor'])->default('supervisor');
            $table->enum('status', ['draft', 'submitted', 'verified'])->default('draft');
            $table->decimal('final_score', 6, 2)->nullable();
            $table->enum('recommendation', ['sangat_baik', 'baik', 'cukup', 'kurang'])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pkg_assessments');
    }
};
