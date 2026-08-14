<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('workflow_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $t->string('type', 30);
            $t->string('title');
            $t->text('description')->nullable();
            $t->json('payload')->nullable();
            $t->enum('status', ['draft', 'submitted', 'under_review', 'approved', 'rejected'])->default('submitted');
            $t->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('submitted_at')->nullable();
            $t->timestamp('decided_at')->nullable();
            $t->text('decision_note')->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['school_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_requests');
    }
};
