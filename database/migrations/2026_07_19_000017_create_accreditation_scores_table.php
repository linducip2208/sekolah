<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accreditation_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('accreditation_instrument_id')->constrained('accreditation_instruments')->restrictOnDelete();
            $table->tinyInteger('self_score')->nullable()->comment('Nilai mandiri 0-4');
            $table->tinyInteger('actual_score')->nullable()->comment('Nilai asesor 0-4');
            $table->text('notes')->nullable();
            $table->foreignId('scored_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('scored_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'accreditation_instrument_id'], 'accred_scores_school_instrument_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accreditation_scores');
    }
};
