<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bkk_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->integer('semester')->default(1);
            $table->bigInteger('total_graduates')->default(0);
            $table->bigInteger('total_placed')->default(0);
            $table->bigInteger('total_entrepreneur')->default(0);
            $table->bigInteger('total_university')->default(0);
            $table->bigInteger('total_unemployed')->default(0);
            $table->date('report_date');
            $table->string('report_file_path')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'academic_year_id']);
            $table->index(['school_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bkk_reports');
    }
};
