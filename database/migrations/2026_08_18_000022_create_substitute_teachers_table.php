<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('substitute_teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('original_teacher_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('substitute_teacher_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('timetable_entry_id')->nullable()->constrained('timetable_slots')->nullOnDelete();
            $table->date('date');
            $table->integer('period_number')->nullable();
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'cancelled'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('substitute_teachers');
    }
};
