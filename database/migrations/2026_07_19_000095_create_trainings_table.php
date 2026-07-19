<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('title');
            $table->string('provider')->nullable();
            $table->string('training_type')->comment('seminar, workshop, diklat, online, sertifikasi');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('duration_hours')->nullable();
            $table->string('location')->nullable();
            $table->text('certificate_template')->nullable();
            $table->boolean('is_mandatory')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainings');
    }
};
