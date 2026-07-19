<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('osis_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('osis_election_id')->constrained('osis_elections')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('position');
            $table->text('vision')->nullable(); // visi
            $table->text('mission')->nullable(); // misi
            $table->string('photo_path')->nullable();
            $table->string('status')->default('registered'); // registered, approved, disqualified
            $table->integer('vote_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('osis_candidates');
    }
};
