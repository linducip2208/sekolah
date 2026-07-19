<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('webhooks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('url', 500);
            $t->text('secret_encrypted')->nullable();   // HMAC secret
            $t->json('events');                          // ['invoice.paid', 'student.created', ...]
            $t->json('extra_headers')->nullable();
            $t->unsignedTinyInteger('max_retries')->default(3);
            $t->boolean('is_active')->default(true);
            $t->timestamp('last_delivered_at')->nullable();
            $t->timestamp('last_failed_at')->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['school_id', 'is_active']);
        });

        Schema::create('webhook_deliveries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('webhook_id')->constrained()->cascadeOnDelete();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('event', 80);
            $t->string('event_id', 100)->nullable();
            $t->longText('payload');
            $t->unsignedSmallInteger('http_status')->nullable();
            $t->longText('response_body')->nullable();
            $t->string('status', 20)->default('pending'); // pending, success, failed, retrying
            $t->unsignedTinyInteger('attempts')->default(0);
            $t->timestamp('next_retry_at')->nullable();
            $t->timestamp('delivered_at')->nullable();
            $t->timestamps();
            $t->index(['webhook_id', 'status']);
            $t->index(['school_id', 'event']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
        Schema::dropIfExists('webhooks');
    }
};
