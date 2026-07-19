<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Module 17 — Notice Board
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->string('title');
            $table->text('content');
            $table->json('target_roles')->nullable();           // ["student","parent"]
            $table->json('target_class_sections')->nullable();  // [1, 3, 5]
            $table->datetime('publish_at')->nullable();
            $table->datetime('expire_at')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'is_published', 'publish_at']);
        });

        // Module 18 — Chat
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_one')->constrained('users');  // always smaller user_id
            $table->foreignId('user_two')->constrained('users');  // always larger user_id
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
            $table->unique(['school_id', 'user_one', 'user_two']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users');
            $table->text('body');
            $table->string('file')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            $table->index(['conversation_id', 'created_at']);
        });

        // Module 19 — In-app Notifications
        Schema::create('notifications_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50);
            $table->string('title');
            $table->text('body')->nullable();
            $table->json('data')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            $table->index(['user_id', 'is_read', 'created_at']);
        });

        // Module 13 — Subscription (for SaaS platform mode)
        Schema::create('subscription_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained();
            $table->unsignedInteger('amount');    // cents
            $table->string('payment_method')->nullable();
            $table->string('reference')->nullable();
            $table->enum('status', ['pending', 'paid', 'failed'])->default('pending');
            $table->date('period_from');
            $table->date('period_to');
            $table->timestamps();
            $table->index(['school_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_transactions');
        Schema::dropIfExists('notifications_log');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('notices');
    }
};
