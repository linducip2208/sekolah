<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_studies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('title');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('class_section_id')->nullable()->constrained('class_sections')->nullOnDelete();
            $table->string('topic')->nullable();
            $table->string('phase')->default('plan')->comment('plan, do, see');
            $table->string('status')->default('draft')->comment('draft, planned, observed, reflected, completed');
            $table->date('plan_date')->nullable();
            $table->date('teach_date')->nullable();
            $table->date('reflect_date')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('lead_teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('description')->nullable();
            $table->text('plan_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'status']);
            $table->index('phase');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_studies');
    }
};
