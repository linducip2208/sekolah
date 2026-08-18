<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bpjs_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('kesehatan_employee_pct')->default(400); // 4.00%
            $table->unsignedInteger('kesehatan_employer_pct')->default(400); // 4.00%
            $table->unsignedBigInteger('kesehatan_salary_cap')->default(1200000000); // Rp 12jt
            $table->unsignedInteger('jkk_pct')->default(24); // 0.24%
            $table->unsignedInteger('jkm_pct')->default(30); // 0.30%
            $table->unsignedInteger('jht_employee_pct')->default(200); // 2%
            $table->unsignedInteger('jht_employer_pct')->default(370); // 3.7%
            $table->unsignedInteger('jp_employee_pct')->default(100); // 1%
            $table->unsignedInteger('jp_employer_pct')->default(200); // 2%
            $table->unsignedBigInteger('jp_salary_cap')->default(955960000); // Rp 9.559.600
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique('school_id');
        });

        Schema::create('pph21_brackets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('min_annual'); // cents
            $table->unsignedBigInteger('max_annual')->nullable(); // cents, null = unlimited
            $table->unsignedInteger('rate_pct'); // percentage * 100 (e.g. 500 = 5%)
            $table->timestamps();
            $table->index(['school_id', 'min_annual']);
        });

        Schema::create('staff_tax_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staffs')->cascadeOnDelete();
            $table->string('npwp', 20)->nullable();
            $table->unsignedInteger('pTKP_status')->default(1); // 1=TK/0, 2=TK/1, 3=K/0, 4=K/1, 5=K/2, 6=K/3
            $table->unsignedInteger('number_of_dependents')->default(0);
            $table->boolean('is_bpjs_active')->default(true);
            $table->boolean('is_pph21_active')->default(true);
            $table->timestamps();
            $table->unique(['school_id', 'staff_id']);
        });

        Schema::create('bpjs_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('month', 7); // "2025-01"
            $table->foreignId('staff_id')->constrained('staffs');
            $table->unsignedInteger('salary_base'); // cents
            $table->unsignedInteger('kesehatan_employee')->default(0);
            $table->unsignedInteger('kesehatan_employer')->default(0);
            $table->unsignedInteger('jkk')->default(0);
            $table->unsignedInteger('jkm')->default(0);
            $table->unsignedInteger('jht_employee')->default(0);
            $table->unsignedInteger('jht_employer')->default(0);
            $table->unsignedInteger('jp_employee')->default(0);
            $table->unsignedInteger('jp_employer')->default(0);
            $table->unsignedInteger('total_employee')->default(0);
            $table->unsignedInteger('total_employer')->default(0);
            $table->timestamps();
            $table->unique(['school_id', 'month', 'staff_id']);
            $table->index(['school_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bpjs_reports');
        Schema::dropIfExists('staff_tax_profiles');
        Schema::dropIfExists('pph21_brackets');
        Schema::dropIfExists('bpjs_configs');
    }
};
