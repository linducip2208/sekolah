<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Learning Outcomes (CP — Capaian Pembelajaran)
        Schema::create('learning_outcomes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $t->enum('stage', ['SD', 'SMP', 'SMA']);
            $t->text('description');
            $t->string('code', 30);
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
            $t->softDeletes();
            $t->index(['school_id', 'subject_id']);
            $t->index(['school_id', 'stage']);
        });

        // Learning Objectives (TP — Tujuan Pembelajaran)
        Schema::create('learning_objectives', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('learning_outcome_id')->constrained()->cascadeOnDelete();
            $t->text('description');
            $t->string('code', 30);
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
            $t->softDeletes();
            $t->index(['school_id', 'learning_outcome_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_objectives');
        Schema::dropIfExists('learning_outcomes');
    }
};
