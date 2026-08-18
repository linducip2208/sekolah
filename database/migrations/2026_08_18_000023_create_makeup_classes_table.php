<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('makeup_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('original_timetable_id')->nullable()->constrained('timetable_slots')->nullOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->restrictOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('class_room_id')->constrained('class_rooms')->restrictOnDelete();
            $table->date('new_date');
            $table->integer('new_period_number');
            $table->string('new_room', 100)->nullable();
            $table->text('reason')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'new_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('makeup_classes');
    }
};
