<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('osis_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('osis_election_id')->constrained('osis_elections')->cascadeOnDelete();
            $table->foreignId('voter_id')->constrained('users')->cascadeOnDelete(); // student user_id
            $table->foreignId('candidate_id')->constrained('osis_candidates')->cascadeOnDelete();
            $table->timestamp('voted_at')->nullable();
            $table->timestamps();

            $table->unique(['osis_election_id', 'voter_id'], 'unique_student_vote');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('osis_votes');
    }
};
