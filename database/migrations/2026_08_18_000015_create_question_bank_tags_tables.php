<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_bank_tags', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('name', 100);
            $t->string('color', 7)->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->unique(['school_id', 'name']);
        });

        Schema::create('question_tag_pivot', function (Blueprint $t) {
            $t->foreignId('question_bank_item_id')->constrained('question_bank_items')->cascadeOnDelete();
            $t->foreignId('question_tag_id')->constrained('question_bank_tags')->cascadeOnDelete();
            $t->primary(['question_bank_item_id', 'question_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_tag_pivot');
        Schema::dropIfExists('question_bank_tags');
    }
};
