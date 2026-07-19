<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Module 10 — Admission
        Schema::create('admission_enquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('student_name');
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('phone', 30);
            $table->string('email')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('class_applying')->nullable();
            $table->enum('status', ['enquiry', 'applied', 'enrolled', 'rejected'])->default('enquiry');
            $table->foreignId('converted_student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['school_id', 'status']);
        });

        // Module 11 — Fee & Invoice
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_room_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('frequency'); // monthly, quarterly, annual, one-time
            $table->unsignedInteger('amount'); // cents
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['school_id', 'class_room_id']);
        });

        Schema::create('fee_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_structure_id')->constrained();
            $table->string('invoice_no', 50)->unique();
            $table->date('due_date');
            $table->unsignedInteger('amount');       // cents
            $table->unsignedInteger('paid_amount')->default(0);  // cents
            $table->unsignedInteger('discount')->default(0);     // cents
            $table->enum('status', ['unpaid', 'partial', 'paid', 'overdue'])->default('unpaid');
            $table->string('period')->nullable();    // "2025-01" for monthly
            $table->timestamps();
            $table->index(['school_id', 'student_id', 'status']);
            $table->index(['school_id', 'due_date', 'status']);
        });

        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('collected_by')->constrained('users');
            $table->unsignedInteger('amount');       // cents
            $table->string('payment_method')->default('cash'); // cash, transfer, qris
            $table->string('reference')->nullable();
            $table->text('note')->nullable();
            $table->date('payment_date');
            $table->timestamps();
        });

        // Module 12 — Payroll
        Schema::create('payroll_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['allowance', 'deduction']);
            $table->enum('calculation', ['fixed', 'percentage'])->default('fixed');
            $table->unsignedInteger('value')->default(0); // cents or percentage * 100
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('salary_slips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staffs');
            $table->string('month', 7); // "2025-01"
            $table->unsignedInteger('basic_salary');       // cents
            $table->unsignedInteger('total_allowances')->default(0); // cents
            $table->unsignedInteger('total_deductions')->default(0); // cents
            $table->unsignedInteger('net_salary');         // cents
            $table->json('allowances_detail')->nullable(); // snapshot
            $table->json('deductions_detail')->nullable(); // snapshot
            $table->enum('status', ['draft', 'paid'])->default('draft');
            $table->date('paid_on')->nullable();
            $table->timestamps();
            $table->unique(['staff_id', 'month']);
            $table->index(['school_id', 'month', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_slips');
        Schema::dropIfExists('payroll_structures');
        Schema::dropIfExists('fee_payments');
        Schema::dropIfExists('fee_invoices');
        Schema::dropIfExists('fee_structures');
        Schema::dropIfExists('admission_enquiries');
    }
};
