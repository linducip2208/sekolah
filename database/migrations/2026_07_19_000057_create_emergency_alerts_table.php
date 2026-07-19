<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergency_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('alert_type'); // fire, earthquake, flood, security, medical, other
            $table->string('title');
            $table->text('message');
            $table->foreignId('triggered_by')->constrained('users')->cascadeOnDelete();
            $table->string('severity')->default('info'); // info, warning, critical
            $table->string('status')->default('draft'); // draft, sent, cancelled
            $table->timestamp('sent_at')->nullable();
            $table->integer('recipient_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_alerts');
    }
};
