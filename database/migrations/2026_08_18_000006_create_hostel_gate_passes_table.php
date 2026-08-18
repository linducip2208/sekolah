<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostel_gate_passes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->enum('pass_type', ['in', 'out']);
            $table->string('purpose');
            $table->string('visitor_name')->nullable();
            $table->string('visitor_phone')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->datetime('out_time')->nullable();
            $table->datetime('expected_return')->nullable();
            $table->datetime('actual_return')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_gate_passes');
    }
};
