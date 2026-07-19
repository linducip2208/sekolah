<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cooperative_savings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('cooperative_member_id')->constrained('cooperative_members')->cascadeOnDelete();
            $table->date('transaction_date');
            $table->bigInteger('amount');
            $table->string('savings_type');
            $table->string('transaction_type');
            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'transaction_date']);
            $table->index(['cooperative_member_id', 'savings_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cooperative_savings');
    }
};
