<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('request_number', 30);
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->string('department')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('estimated_budget')->default(0);
            $table->enum('urgency', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'ordered', 'received'])->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejected_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'request_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_requests');
    }
};
