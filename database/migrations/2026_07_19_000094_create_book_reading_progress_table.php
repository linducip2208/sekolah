<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_reading_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('digital_book_issue_id')->constrained('digital_book_issues')->cascadeOnDelete();
            $table->integer('current_page')->default(1);
            $table->integer('total_pages')->nullable();
            $table->timestamp('last_read_at')->useCurrent();
            $table->decimal('progress_percent', 5, 2)->default(0.00);
            $table->timestamps();

            $table->unique('digital_book_issue_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_reading_progress');
    }
};
