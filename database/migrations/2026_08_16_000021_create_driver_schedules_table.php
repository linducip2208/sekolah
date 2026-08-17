<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transport_route_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->string('driver_name', 200)->nullable();
            $table->date('date');
            $table->enum('shift', ['morning', 'afternoon'])->default('morning');
            $table->string('note', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['transport_route_id', 'date', 'shift'], 'driver_schedule_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_schedules');
    }
};
