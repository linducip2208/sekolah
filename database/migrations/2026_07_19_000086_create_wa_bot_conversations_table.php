<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_bot_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('phone', 30);
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->enum('message_direction', ['incoming', 'outgoing']);
            $table->text('message_text');
            $table->string('matched_command', 100)->nullable();
            $table->text('response_text')->nullable();
            $table->boolean('session_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'phone']);
            $table->index('session_active');
            $table->index(['phone', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_bot_conversations');
    }
};
