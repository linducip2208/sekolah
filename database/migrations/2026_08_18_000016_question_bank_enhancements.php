<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_bank_items', function (Blueprint $t) {
            $t->enum('question_type', ['multiple_choice', 'essay', 'true_false', 'matching', 'fill_blank'])->default('multiple_choice')->after('type');
            $t->unsignedInteger('version')->default(1)->after('is_published');
            $t->foreignId('parent_id')->nullable()->after('version')->constrained('question_bank_items')->nullOnDelete();
            $t->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft')->after('parent_id');
            $t->foreignId('reviewed_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $t->dateTime('reviewed_at')->nullable()->after('reviewed_by');
            $t->unsignedInteger('total_attempts')->default(0)->after('reviewed_at');
            $t->unsignedInteger('correct_attempts')->default(0)->after('total_attempts');
        });

        Schema::create('question_blueprints', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('name', 200);
            $t->foreignId('exam_id')->nullable()->constrained('exams')->nullOnDelete();
            $t->unsignedSmallInteger('total_items')->default(0);
            $t->json('distribution')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_blueprints');

        Schema::table('question_bank_items', function (Blueprint $t) {
            $t->dropForeign(['reviewed_by']);
            $t->dropForeign(['parent_id']);
            $t->dropColumn([
                'question_type', 'version', 'parent_id',
                'status', 'reviewed_by', 'reviewed_at',
                'total_attempts', 'correct_attempts',
            ]);
        });
    }
};
