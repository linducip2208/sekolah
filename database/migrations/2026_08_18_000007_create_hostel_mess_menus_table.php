<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostel_mess_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hostel_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 0=Sunday
            $table->enum('meal_type', ['breakfast', 'lunch', 'dinner']);
            $table->text('menu_description');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['school_id', 'hostel_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_mess_menus');
    }
};
