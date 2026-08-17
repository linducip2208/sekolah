<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name', 200);
            $table->string('trigger_type', 50);
            $table->json('config')->nullable();
            $table->string('action_type', 30);
            $table->json('action_config')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'trigger_type']);
        });

        Schema::create('automation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('automation_rule_id')->nullable()->constrained()->nullOnDelete();
            $table->string('trigger_type', 50);
            $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('payload')->nullable();
            $table->enum('status', ['success', 'skipped', 'failed'])->default('success');
            $table->text('error')->nullable();
            $table->datetime('executed_at');
            $table->timestamps();
            $table->index(['school_id', 'trigger_type', 'executed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_logs');
        Schema::dropIfExists('automation_rules');
    }
};
