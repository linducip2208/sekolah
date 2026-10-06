<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('school_registrations', 'coupon_code')) {
            Schema::table('school_registrations', function (Blueprint $table) {
                $table->string('coupon_code', 50)->nullable()->after('billing_months');
            });
        }
        if (! Schema::hasColumn('school_registrations', 'discount_amount')) {
            Schema::table('school_registrations', function (Blueprint $table) {
                $table->unsignedBigInteger('discount_amount')->default(0)->after('coupon_code');
            });
        }
        if (! Schema::hasColumn('school_branding', 'custom_domain_token')) {
            Schema::table('school_branding', function (Blueprint $table) {
                $table->string('custom_domain_token', 64)->nullable()->after('custom_domain');
            });
        }
        if (! Schema::hasColumn('school_branding', 'custom_domain_verified_at')) {
            Schema::table('school_branding', function (Blueprint $table) {
                $table->timestamp('custom_domain_verified_at')->nullable()->after('custom_domain_token');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('school_registrations', 'coupon_code')) {
            Schema::table('school_registrations', function (Blueprint $table) {
                $table->dropColumn(['coupon_code']);
            });
        }
        if (Schema::hasColumn('school_registrations', 'discount_amount')) {
            Schema::table('school_registrations', function (Blueprint $table) {
                $table->dropColumn(['discount_amount']);
            });
        }
        if (Schema::hasColumn('school_branding', 'custom_domain_token') || Schema::hasColumn('school_branding', 'custom_domain_verified_at')) {
            $drop = [];
            if (Schema::hasColumn('school_branding', 'custom_domain_token')) {
                $drop[] = 'custom_domain_token';
            }
            if (Schema::hasColumn('school_branding', 'custom_domain_verified_at')) {
                $drop[] = 'custom_domain_verified_at';
            }
            Schema::table('school_branding', function (Blueprint $table) use ($drop) {
                $table->dropColumn($drop);
            });
        }
    }
};
