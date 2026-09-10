<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('payment_webhook_logs', 'payload_hash')) {
            Schema::table('payment_webhook_logs', function (Blueprint $table) {
                $table->string('payload_hash', 64)->nullable()->after('payload');
                $table->index(['payment_provider_id', 'payload_hash'], 'payment_webhook_replay_lookup');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('payment_webhook_logs', 'payload_hash')) {
            Schema::table('payment_webhook_logs', function (Blueprint $table) {
                $table->dropIndex('payment_webhook_replay_lookup');
                $table->dropColumn('payload_hash');
            });
        }
    }
};
