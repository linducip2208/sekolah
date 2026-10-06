<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('device_tokens', function (Blueprint $table) {
            $table->index('token', 'device_tokens_token_index');
        });
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->index(['fee_invoice_id', 'payment_date'], 'fee_payments_invoice_date_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropIndex('fee_payments_invoice_date_index');
        });
        Schema::table('device_tokens', function (Blueprint $table) {
            $table->dropIndex('device_tokens_token_index');
        });
    }
};
