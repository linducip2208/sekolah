<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitor_qr_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_log_id')->constrained('visitor_logs')->cascadeOnDelete();
            $table->string('qr_token', 128)->unique();
            $table->timestamp('issued_at');
            $table->timestamp('expires_at');
            $table->timestamp('scanned_at')->nullable();
            $table->string('scanned_by', 100)->nullable();
            $table->timestamps();

            $table->index('qr_token');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_qr_sessions');
    }
};
