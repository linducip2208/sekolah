<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_bank_items', function (Blueprint $table) {
            $table->foreignId('author_id')->nullable()->change();
            $table->string('cognitive_level', 30)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('question_bank_items', function (Blueprint $table) {
            $table->foreignId('author_id')->nullable(false)->change();
            $table->string('cognitive_level', 4)->nullable(false)->change();
        });
    }
};
