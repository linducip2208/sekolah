<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ptm_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('parent_user_id')->constrained('users');
            $table->foreignId('teacher_id')->constrained('users');
            $table->foreignId('class_room_id')->nullable()->constrained('class_rooms');
            $table->date('meeting_date');
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->text('follow_up')->nullable();
            $table->boolean('reminder_sent')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'meeting_date', 'status']);
            $table->index(['school_id', 'teacher_id', 'meeting_date']);
            $table->index(['school_id', 'parent_user_id', 'meeting_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ptm_schedules');
    }
};
