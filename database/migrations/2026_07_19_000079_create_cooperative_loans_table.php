<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cooperative_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('cooperative_member_id')->constrained('cooperative_members')->cascadeOnDelete();
            $table->bigInteger('loan_amount');
            $table->decimal('interest_rate', 5, 2)->default(0);
            $table->integer('term_months');
            $table->bigInteger('monthly_installment')->default(0);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('purpose')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'status']);
            $table->index(['cooperative_member_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cooperative_loans');
    }
};
