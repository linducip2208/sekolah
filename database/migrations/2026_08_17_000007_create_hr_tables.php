<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employment_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staffs')->cascadeOnDelete();
            $table->enum('type', ['permanent', 'contract', 'probation'])->default('contract');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->unsignedBigInteger('salary')->default(0);
            $table->enum('status', ['active', 'expired', 'terminated'])->default('active');
            $table->string('document_path', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'staff_id']);
        });

        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staffs')->cascadeOnDelete();
            $table->enum('type', ['annual', 'sick', 'other'])->default('annual');
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedSmallInteger('days')->default(1);
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->datetime('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'staff_id', 'status']);
        });

        Schema::create('overtime_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staffs')->cascadeOnDelete();
            $table->date('date');
            $table->decimal('hours', 5, 2)->default(0);
            $table->unsignedBigInteger('rate_per_hour')->default(0);
            $table->unsignedBigInteger('amount')->default(0);
            $table->text('note')->nullable();
            $table->enum('status', ['pending', 'approved', 'paid'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'staff_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_records');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('employment_contracts');
    }
};
