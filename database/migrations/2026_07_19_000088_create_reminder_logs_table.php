<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('reminder_schedule_id')->constrained('reminder_schedules')->cascadeOnDelete();
            $table->unsignedBigInteger('target_id');
            $table->string('target_phone', 30)->nullable();
            $table->string('target_email', 100)->nullable();
            $table->text('message_sent');
            $table->string('channel', 20);
            $table->timestamp('sent_at')->nullable();
            $table->enum('status', ['success', 'failed']);
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'reminder_schedule_id']);
            $table->index(['target_id', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_logs');
    }
};
