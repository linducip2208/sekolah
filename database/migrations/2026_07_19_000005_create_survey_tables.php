<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_templates', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->text('description')->nullable();
            $t->string('survey_type', 30);
            $t->boolean('is_active')->default(true);
            $t->date('start_date')->nullable();
            $t->date('end_date')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('survey_questions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('survey_template_id')->constrained()->cascadeOnDelete();
            $t->text('question_text');
            $t->string('question_type', 30);
            $t->json('options')->nullable();
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
            $t->softDeletes();
        });

        Schema::create('survey_responses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('survey_template_id')->constrained()->cascadeOnDelete();
            $t->string('respondent_type', 20);
            $t->unsignedBigInteger('respondent_id');
            $t->string('target_type', 20);
            $t->unsignedBigInteger('target_id')->nullable();
            $t->timestamp('submitted_at')->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['survey_template_id', 'respondent_type', 'respondent_id'], 'sresp_respondent_idx');
            $t->index(['survey_template_id', 'target_type', 'target_id'], 'sresp_target_idx');
        });

        Schema::create('survey_answers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('survey_response_id')->constrained()->cascadeOnDelete();
            $t->foreignId('survey_question_id')->constrained()->cascadeOnDelete();
            $t->text('answer_text')->nullable();
            $t->unsignedTinyInteger('answer_rating')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_answers');
        Schema::dropIfExists('survey_responses');
        Schema::dropIfExists('survey_questions');
        Schema::dropIfExists('survey_templates');
    }
};
