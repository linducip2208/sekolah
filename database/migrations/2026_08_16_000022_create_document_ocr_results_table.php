<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_ocr_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('filename', 255);
            $table->string('mime_type', 100)->nullable();
            $table->string('file_path', 500);
            $table->longText('extracted_text')->nullable();
            $table->enum('status', ['completed', 'failed'])->default('failed');
            $table->text('error')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_ocr_results');
    }
};
