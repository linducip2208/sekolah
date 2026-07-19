<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracer_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('alumni_profile_id')->constrained('alumni_profiles')->cascadeOnDelete();
            $table->year('graduation_year');
            $table->enum('status', ['kerja', 'kuliah', 'wirausaha', 'menganggur', 'lainnya'])->nullable();
            $table->string('company_name')->nullable();
            $table->string('position')->nullable();
            $table->string('salary_range')->nullable();
            $table->boolean('is_relevant')->nullable();
            $table->text('feedback')->nullable();
            $table->json('answers')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracer_responses');
    }
};
