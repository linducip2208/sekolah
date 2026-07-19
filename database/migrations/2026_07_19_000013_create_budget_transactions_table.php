<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('budget_item_id')->constrained('budget_items')->restrictOnDelete();
            $table->date('transaction_date');
            $table->bigInteger('amount')->default(0)->comment('Jumlah dalam sen Rupiah');
            $table->text('description')->nullable();
            $table->string('reference_no')->nullable();
            $table->string('receipt_path')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'budget_item_id']);
            $table->index(['school_id', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_transactions');
    }
};
