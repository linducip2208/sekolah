<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookable_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('room_type')->default('classroom');
            $table->integer('capacity')->default(30);
            $table->string('floor')->nullable();
            $table->string('building')->nullable();
            $table->json('facilities')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookable_rooms');
    }
};
