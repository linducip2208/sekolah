<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('benchmark_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('foundation_id')->nullable()->constrained('foundations')->nullOnDelete();
            $table->string('metric_key')->unique();
            $table->string('metric_name');
            $table->text('description')->nullable();
            $table->string('unit')->default('percent');
            $table->text('data_source')->nullable()->comment('SQL query template or method name');
            $table->enum('aggregation', ['avg', 'sum', 'count', 'max', 'min'])->default('avg');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('benchmark_metrics');
    }
};
