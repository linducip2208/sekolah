<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('school_registrations', function (Blueprint $t) {
            $t->id();
            $t->string('school_name');
            $t->string('subdomain')->unique();
            $t->string('admin_name');
            $t->string('admin_email');
            $t->string('admin_phone', 30)->nullable();
            $t->string('address', 500)->nullable();

            $t->foreignId('plan_id')->constrained()->restrictOnDelete();
            $t->unsignedBigInteger('plan_price')->default(0);
            $t->unsignedTinyInteger('billing_months')->default(12);

            $t->enum('status', ['pending', 'verifying', 'paid', 'rejected', 'activated'])->default('pending');
            $t->string('payment_method', 50)->nullable();
            $t->string('payment_reference', 100)->nullable();
            $t->string('payment_proof_path')->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->timestamp('activated_at')->nullable();
            $t->foreignId('activated_school_id')->nullable()->constrained('schools')->nullOnDelete();
            $t->text('notes')->nullable();
            $t->text('rejection_reason')->nullable();
            $t->timestamps();

            $t->index('status');
        });

        Schema::create('platform_billing_accounts', function (Blueprint $t) {
            $t->id();
            $t->string('bank_name', 100);
            $t->string('account_number', 50);
            $t->string('account_holder', 200);
            $t->string('logo_path')->nullable();
            $t->boolean('is_active')->default(true);
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_billing_accounts');
        Schema::dropIfExists('school_registrations');
    }
};
