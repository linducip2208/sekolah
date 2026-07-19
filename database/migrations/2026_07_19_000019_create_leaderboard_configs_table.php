<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaderboard_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('config_type')->default('monthly');
            $table->boolean('is_active')->default(true);
            $table->integer('weight_academic')->default(30);
            $table->integer('weight_attendance')->default(25);
            $table->integer('weight_extracurricular')->default(20);
            $table->integer('weight_discipline')->default(25);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'config_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaderboard_configs');
    }
};
