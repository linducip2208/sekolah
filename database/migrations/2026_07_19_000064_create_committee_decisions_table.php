<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('committee_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('committee_meeting_id')->constrained('committee_meetings')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('decision_type')->default('lainnya'); // kebijakan, anggaran, program, lainnya
            $table->string('voting_result')->nullable(); // approved, rejected, deferred
            $table->json('voting_detail')->nullable(); // {setuju: 10, tidak_setuju: 2, abstain: 1}
            $table->string('status')->default('draft'); // draft, finalized
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('committee_decisions');
    }
};
