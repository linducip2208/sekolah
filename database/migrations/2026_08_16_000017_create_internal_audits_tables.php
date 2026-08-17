<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internal_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('title', 200);
            $table->string('period', 100)->nullable();
            $table->string('auditor', 200)->nullable();
            $table->enum('status', ['planned', 'in_progress', 'completed'])->default('planned');
            $table->date('started_at')->nullable();
            $table->date('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'status']);
        });

        Schema::create('audit_findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('internal_audit_id')->constrained()->cascadeOnDelete();
            $table->string('area', 200);
            $table->text('description');
            $table->enum('severity', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'resolved'])->default('open');
            $table->text('action')->nullable();
            $table->date('due_date')->nullable();
            $table->datetime('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_findings');
        Schema::dropIfExists('internal_audits');
    }
};
