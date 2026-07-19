<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_dropout_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->date('prediction_date');
            $table->string('risk_level', 20)->comment('low/medium/high/critical');
            $table->decimal('risk_score', 5, 2)->comment('0-100');
            $table->json('contributing_factors')->nullable();
            $table->text('ai_analysis')->nullable();
            $table->foreignId('ai_provider_id')->nullable()->constrained('ai_providers')->nullOnDelete();
            $table->foreignId('ai_model_id')->nullable()->constrained('ai_models')->nullOnDelete();
            $table->text('recommended_actions')->nullable();
            $table->boolean('notified_parents')->default(false);
            $table->boolean('notified_teacher')->default(false);
            $table->unsignedInteger('tokens_used')->default(0);
            $table->unsignedInteger('processing_time_ms')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'prediction_date']);
            $table->index(['school_id', 'risk_level']);
            $table->index(['student_id', 'prediction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_dropout_predictions');
    }
};
