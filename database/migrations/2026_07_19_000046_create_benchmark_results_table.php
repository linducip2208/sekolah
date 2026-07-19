<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('benchmark_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('benchmark_metric_id')->constrained('benchmark_metrics')->cascadeOnDelete();
            $table->string('period', 7)->comment('year-month format: 2026-07');
            $table->decimal('value', 15, 4);
            $table->integer('rank')->nullable();
            $table->decimal('percentile', 5, 2)->nullable();
            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->unique(['school_id', 'benchmark_metric_id', 'period'], 'benchmark_result_unique');
            $table->index(['benchmark_metric_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('benchmark_results');
    }
};
