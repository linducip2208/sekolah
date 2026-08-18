<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostel_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('hostel_room_id')->constrained('hostel_rooms')->cascadeOnDelete();
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'late', 'permission'])->default('present');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->foreignId('noted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['school_id', 'hostel_room_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_attendances');
    }
};
