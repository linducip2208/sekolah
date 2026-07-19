<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wa_bot_commands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('command_keyword', 100);
            $table->enum('response_type', ['static', 'text_function']);
            $table->text('static_response')->nullable();
            $table->string('function_method', 100)->nullable();
            $table->string('description', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'command_keyword']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_bot_commands');
    }
};
