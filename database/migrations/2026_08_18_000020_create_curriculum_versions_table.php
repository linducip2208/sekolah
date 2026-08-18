<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curriculum_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('curriculum_framework_id')->constrained('curriculum_frameworks')->cascadeOnDelete();
            $table->string('version_name', 100);
            $table->string('academic_year', 20)->nullable();
            $table->boolean('is_active')->default(false);
            $table->date('effective_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'curriculum_framework_id', 'is_active'], 'cv_school_fw_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curriculum_versions');
    }
};
