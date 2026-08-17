<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transport_route_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->enum('direction', ['to_school', 'from_school'])->default('to_school');
            $table->enum('status', ['present', 'absent'])->default('present');
            $table->string('note', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['transport_route_id', 'student_id', 'date', 'direction'], 'transport_attendance_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_attendances');
    }
};
