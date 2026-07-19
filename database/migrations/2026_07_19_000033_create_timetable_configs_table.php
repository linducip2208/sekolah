<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('class_section_id')->constrained('class_sections')->cascadeOnDelete();
            $table->unsignedTinyInteger('days_per_week')->default(5);
            $table->unsignedTinyInteger('periods_per_day')->default(8);
            $table->unsignedSmallInteger('period_duration_minutes')->default(45);
            $table->json('break_after_periods')->nullable();
            $table->time('start_time')->default('07:00:00');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_configs');
    }
};
