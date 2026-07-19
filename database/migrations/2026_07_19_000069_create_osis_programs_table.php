<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('osis_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('osis_election_id')->nullable()->constrained('osis_elections')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->bigInteger('budget')->nullable(); // in sen
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('planned'); // planned, ongoing, completed, cancelled
            $table->text('progress_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('osis_programs');
    }
};
