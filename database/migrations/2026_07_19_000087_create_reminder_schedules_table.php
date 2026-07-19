<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminder_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name', 200);
            $table->enum('recipient_type', ['parent', 'student', 'staff']);
            $table->json('trigger_days_before');
            $table->enum('reminder_type', ['wa', 'email', 'sms']);
            $table->text('message_template');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_schedules');
    }
};
