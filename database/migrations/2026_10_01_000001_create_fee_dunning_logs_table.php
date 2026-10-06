<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_dunning_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_invoice_id')->constrained()->cascadeOnDelete();
            $table->string('stage', 10); // H-7, H-3, H+1, H+7
            $table->string('channel', 20)->default('whatsapp');
            $table->string('status', 20)->default('sent');
            $table->text('message')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['school_id', 'fee_invoice_id', 'stage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_dunning_logs');
    }
};
