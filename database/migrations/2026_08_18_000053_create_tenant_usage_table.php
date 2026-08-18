<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('month', 7);
            $table->unsignedInteger('active_students')->default(0);
            $table->unsignedInteger('active_teachers')->default(0);
            $table->unsignedInteger('total_logins')->default(0);
            $table->unsignedInteger('api_calls')->default(0);
            $table->unsignedInteger('storage_used_bytes')->default(0);
            $table->unsignedInteger('sms_sent')->default(0);
            $table->unsignedInteger('emails_sent')->default(0);
            $table->timestamps();
            $table->unique(['school_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_usage');
    }
};
