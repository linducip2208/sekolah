<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lesson_plans', function (Blueprint $table) {
            $table->foreignId('ai_provider_id')->nullable()->after('execution_note')->constrained('ai_providers')->nullOnDelete();
            $table->foreignId('ai_model_id')->nullable()->after('ai_provider_id')->constrained('ai_models')->nullOnDelete();
            $table->boolean('ai_generated')->default(false)->after('ai_model_id');
            $table->text('ai_prompt_used')->nullable()->after('ai_generated');
            $table->unsignedInteger('ai_tokens_used')->default(0)->after('ai_prompt_used');
        });
    }

    public function down(): void
    {
        Schema::table('lesson_plans', function (Blueprint $table) {
            $table->dropForeign(['ai_provider_id']);
            $table->dropForeign(['ai_model_id']);
            $table->dropColumn(['ai_provider_id', 'ai_model_id', 'ai_generated', 'ai_prompt_used', 'ai_tokens_used']);
        });
    }
};
