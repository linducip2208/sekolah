<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('budget_category_id')->constrained('budget_categories')->restrictOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->bigInteger('planned_amount')->default(0)->comment('Nilai rencana dalam sen Rupiah');
            $table->bigInteger('actual_amount')->default(0)->comment('Nilai realisasi dalam sen Rupiah');
            $table->enum('status', ['planned', 'approved', 'revised'])->default('planned');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'budget_category_id']);
            $table->index(['school_id', 'academic_year_id']);
            $table->index(['school_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_items');
    }
};
