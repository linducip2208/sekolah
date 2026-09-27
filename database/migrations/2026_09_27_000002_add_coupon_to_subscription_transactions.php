<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('subscription_transactions', 'coupon_code')) {
            Schema::table('subscription_transactions', function (Blueprint $table) {
                $table->string('coupon_code', 50)->nullable()->after('reference');
                $table->unsignedInteger('discount_amount')->default(0)->after('coupon_code');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('subscription_transactions', 'coupon_code')) {
            Schema::table('subscription_transactions', function (Blueprint $table) {
                $table->dropColumn(['coupon_code', 'discount_amount']);
            });
        }
    }
};
