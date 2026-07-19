<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cooperative_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cooperative_loan_id')->constrained('cooperative_loans')->cascadeOnDelete();
            $table->integer('installment_number');
            $table->date('due_date');
            $table->bigInteger('amount');
            $table->bigInteger('paid_amount')->default(0);
            $table->date('paid_date')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['cooperative_loan_id', 'status']);
            $table->index(['due_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cooperative_installments');
    }
};
