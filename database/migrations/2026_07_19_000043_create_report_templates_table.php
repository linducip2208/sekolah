<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('report_type', ['student', 'academic', 'finance', 'attendance', 'combined'])->default('combined');
            $table->json('config')->comment('columns, filters, grouping, chart_config');
            $table->boolean('is_shared')->default(false);
            $table->timestamps();

            $table->index(['school_id', 'report_type']);
            $table->index('is_shared');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_templates');
    }
};
