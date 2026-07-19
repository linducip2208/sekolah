<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_portfolios', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $t->string('title');
            $t->text('description')->nullable();
            $t->string('portfolio_type', 30);
            $t->string('file_path')->nullable();
            $t->string('thumbnail_path')->nullable();
            $t->string('url')->nullable();
            $t->json('tags')->nullable();
            $t->boolean('is_public')->default(false);
            $t->string('share_token', 64)->nullable()->unique();
            $t->foreignId('approved_by')->nullable()->constrained('users');
            $t->timestamp('approved_at')->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['school_id', 'student_id', 'portfolio_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_portfolios');
    }
};
