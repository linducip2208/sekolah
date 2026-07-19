<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accreditation_instruments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accreditation_standard_id')->constrained('accreditation_standards')->cascadeOnDelete();
            $table->string('number')->comment('Nomor instrumen: 1.1, 2.3, dst.');
            $table->text('description');
            $table->integer('max_score')->default(4);
            $table->text('evidence_hint')->nullable()->comment('Petunjuk bukti fisik');
            $table->timestamps();

            $table->unique(['accreditation_standard_id', 'number'], 'instruments_standard_number_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accreditation_instruments');
    }
};
