<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_questions', function (Blueprint $table) {
            $table->foreignId('question_bank_item_id')->nullable()->after('exam_id')
                ->constrained('question_bank_items')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('exam_questions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('question_bank_item_id');
        });
    }
};
