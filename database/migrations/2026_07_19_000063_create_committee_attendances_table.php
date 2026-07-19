<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('committee_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('committee_meeting_id')->constrained('committee_meetings')->cascadeOnDelete();
            $table->foreignId('committee_member_id')->constrained('committee_members')->cascadeOnDelete();
            $table->string('status')->default('hadir'); // hadir, izin, alpha
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('committee_attendances');
    }
};
