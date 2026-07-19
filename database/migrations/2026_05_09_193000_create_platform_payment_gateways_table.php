<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('platform_payment_gateways', function (Blueprint $t) {
            $t->id();
            $t->string('name');                                  // user-input nama provider
            $t->string('slug', 100)->unique();
            $t->string('api_format', 40);                        // redirect_checkout, virtual_account, ewallet, qris, bank_transfer_manual
            $t->string('base_url', 500)->nullable();
            $t->text('api_key_encrypted')->nullable();
            $t->text('secret_key_encrypted')->nullable();
            $t->text('merchant_id_encrypted')->nullable();
            $t->text('webhook_secret_encrypted')->nullable();
            $t->string('callback_url', 500)->nullable();
            $t->json('extra_config')->nullable();
            $t->json('extra_headers')->nullable();
            $t->boolean('is_sandbox')->default(true);
            $t->boolean('is_active')->default(true);
            $t->unsignedSmallInteger('priority')->default(0);
            $t->timestamps();
            $t->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_payment_gateways');
    }
};
