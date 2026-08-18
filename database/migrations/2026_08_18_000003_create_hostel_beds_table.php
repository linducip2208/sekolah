<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostel_beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hostel_room_id')->constrained()->cascadeOnDelete();
            $table->string('bed_no', 20);
            $table->enum('status', ['available', 'occupied', 'maintenance'])->default('available');
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'hostel_room_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_beds');
    }
};
