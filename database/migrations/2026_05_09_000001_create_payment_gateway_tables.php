<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug', 100);
            $table->string('api_format', 40);
            $table->string('base_url', 500)->nullable();
            $table->text('api_key_encrypted')->nullable();
            $table->text('secret_key_encrypted')->nullable();
            $table->text('merchant_id_encrypted')->nullable();
            $table->text('webhook_secret_encrypted')->nullable();
            $table->string('callback_url', 500)->nullable();
            $table->json('extra_config')->nullable();
            $table->json('extra_headers')->nullable();
            $table->boolean('is_sandbox')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('priority')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'slug']);
            $table->index(['school_id', 'is_active', 'api_format']);
        });

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_provider_id')->constrained()->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('display_name');
            $table->string('logo_url', 500)->nullable();
            $table->text('instruction_template')->nullable();
            $table->unsignedInteger('fee_flat')->default(0);
            $table->unsignedSmallInteger('fee_percent_bp')->default(0);
            $table->unsignedTinyInteger('fee_borne_by')->default(0);
            $table->unsignedInteger('min_amount')->default(0);
            $table->unsignedInteger('max_amount')->nullable();
            $table->unsignedInteger('expiry_minutes')->default(1440);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'code']);
            $table->index(['school_id', 'is_active', 'sort_order']);
        });

        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->constrained();
            $table->foreignId('payment_provider_id')->constrained();
            $table->foreignId('initiated_by')->constrained('users');
            $table->foreignId('fee_payment_id')->nullable()->constrained()->nullOnDelete();

            $table->string('reference_no', 100)->unique();
            $table->string('external_id', 200)->nullable();
            $table->string('gateway_transaction_id', 200)->nullable();

            $table->unsignedInteger('amount');
            $table->unsignedInteger('fee_amount')->default(0);
            $table->unsignedInteger('net_amount');
            $table->string('currency', 3)->default('IDR');

            $table->string('status', 30)->default('pending');

            $table->string('redirect_url', 1000)->nullable();
            $table->string('va_number', 50)->nullable();
            $table->string('va_bank_code', 20)->nullable();
            $table->text('qr_string')->nullable();
            $table->string('deeplink_url', 1000)->nullable();

            $table->json('raw_request')->nullable();
            $table->json('raw_response')->nullable();

            $table->timestamp('expired_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'fee_invoice_id', 'status']);
            $table->index(['school_id', 'status', 'expired_at']);
            $table->index('external_id');
        });

        Schema::create('payment_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_provider_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source_ip', 45)->nullable();
            $table->json('headers')->nullable();
            $table->json('payload')->nullable();
            $table->string('signature_status', 30)->nullable();
            $table->string('processing_status', 30)->default('received');
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->index('payment_provider_id');
            $table->index(['processing_status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_webhook_logs');
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('payment_providers');
    }
};
