<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('bank_account', 100)->nullable();
            $table->date('transaction_date');
            $table->string('description', 500)->nullable();
            $table->string('reference_no', 100)->nullable();
            $table->bigInteger('amount'); // positive = credit (masuk), negative = debit (keluar), in cents
            $table->enum('status', ['unmatched', 'matched'])->default('unmatched');
            $table->foreignId('fee_payment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('matched_by')->nullable()->constrained('users')->nullOnDelete();
            $table->datetime('matched_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'status', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_statements');
    }
};
