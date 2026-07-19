<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergency_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emergency_alert_id')->constrained('emergency_alerts')->cascadeOnDelete();
            $table->string('recipient_type'); // all_parents, all_staff, class, individual
            $table->foreignId('recipient_id')->nullable(); // student_id or staff_id or null for all
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_recipients');
    }
};
