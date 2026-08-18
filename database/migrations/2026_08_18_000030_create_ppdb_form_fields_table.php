<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppdb_form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('period_id')->constrained('ppdb_periods')->cascadeOnDelete();
            $table->string('field_name', 100);
            $table->enum('field_type', ['text', 'textarea', 'number', 'date', 'file', 'select', 'checkbox', 'radio']);
            $table->string('field_label', 200);
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(true);
            $table->json('validation_rules')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'period_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_form_fields');
    }
};
