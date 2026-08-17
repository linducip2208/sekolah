<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anomaly_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50);
            $table->enum('severity', ['low', 'medium', 'high'])->default('medium');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->decimal('metric_value', 10, 2)->nullable();
            $table->decimal('reference_value', 10, 2)->nullable();
            $table->json('context')->nullable();
            $table->datetime('detected_at');
            $table->datetime('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'type', 'resolved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anomaly_alerts');
    }
};
