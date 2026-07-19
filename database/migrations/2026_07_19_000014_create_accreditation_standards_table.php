<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accreditation_standards', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Kode standar: 1, 2, 3, 4');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('max_score')->default(100);
            $table->decimal('weight_percent', 5, 2)->default(0)->comment('Bobot dalam persen');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accreditation_standards');
    }
};
