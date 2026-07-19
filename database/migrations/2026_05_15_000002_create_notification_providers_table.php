<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notification_providers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('name');                         // user-input nama
            $t->string('transport', 20);                // push, sms, whatsapp, email
            $t->string('api_format', 40);               // fcm_legacy, fcm_v1, rest_generic, twilio_rest, smpp, smtp, etc
            $t->string('base_url', 500)->nullable();
            $t->text('api_key_encrypted')->nullable();
            $t->text('secret_encrypted')->nullable();
            $t->text('sender_id_encrypted')->nullable();
            $t->json('extra_headers')->nullable();
            $t->json('extra_config')->nullable();       // template, sender_name, custom payload mapping
            $t->boolean('is_active')->default(true);
            $t->boolean('is_default')->default(false);
            $t->timestamps();
            $t->softDeletes();
            $t->index(['school_id', 'transport', 'is_active']);
        });

        Schema::create('device_tokens', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('token', 255);
            $t->string('platform', 20)->nullable();    // android, ios, web
            $t->string('device_name', 200)->nullable();
            $t->timestamp('last_used_at')->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->unique(['user_id', 'token']);
            $t->index(['school_id', 'platform']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
        Schema::dropIfExists('notification_providers');
    }
};
