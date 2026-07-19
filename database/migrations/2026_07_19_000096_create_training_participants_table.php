<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->constrained('trainings')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('registered')->comment('registered, attended, completed, absent');
            $table->string('certificate_number')->nullable()->unique();
            $table->string('certificate_file')->nullable();
            $table->integer('score')->nullable();
            $table->text('feedback')->nullable();
            $table->timestamps();

            $table->unique(['training_id', 'staff_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_participants');
    }
};
