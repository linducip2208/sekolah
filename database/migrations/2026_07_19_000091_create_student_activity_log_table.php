<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_activity_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->enum('activity_type', [
                'attendance', 'mark', 'exam', 'assignment', 'achievement',
                'discipline', 'payment', 'fee', 'portfolio', 'event',
                'counseling',
            ]);
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('reference_type', 100)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'student_id', 'created_at']);
            $table->index(['student_id', 'activity_type']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_activity_log');
    }
};
