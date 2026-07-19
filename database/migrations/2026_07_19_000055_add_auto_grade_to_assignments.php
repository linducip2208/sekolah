<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->enum('question_type', ['essay', 'multiple_choice', 'mixed'])->default('essay')->after('total_marks');
            $table->json('answer_key')->nullable()->after('question_type');
            $table->boolean('auto_grade')->default(false)->after('answer_key');
            $table->boolean('allow_late_submission')->default(false)->after('due_date');
            $table->integer('max_file_size_mb')->nullable()->default(10)->after('allow_late_submission');
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn([
                'question_type', 'answer_key', 'auto_grade',
                'allow_late_submission', 'max_file_size_mb',
            ]);
        });
    }
};
