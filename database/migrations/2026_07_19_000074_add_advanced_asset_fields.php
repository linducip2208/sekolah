<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            if (!Schema::hasColumn('assets', 'last_maintenance_date')) {
                $table->date('last_maintenance_date')->nullable()->after('condition');
            }
            if (!Schema::hasColumn('assets', 'next_maintenance_date')) {
                $table->date('next_maintenance_date')->nullable()->after('last_maintenance_date');
            }
            if (!Schema::hasColumn('assets', 'qr_code')) {
                $table->string('qr_code', 64)->nullable()->unique()->after('next_maintenance_date');
            }
            if (!Schema::hasColumn('assets', 'location_detail')) {
                $table->text('location_detail')->nullable()->after('location');
            }
            if (!Schema::hasColumn('assets', 'warranty_expiry_date')) {
                $table->date('warranty_expiry_date')->nullable()->after('warranty_until');
            }
            if (!Schema::hasColumn('assets', 'supplier_name')) {
                $table->string('supplier_name')->nullable()->after('warranty_expiry_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $columns = ['last_maintenance_date', 'next_maintenance_date', 'qr_code', 'location_detail', 'warranty_expiry_date', 'supplier_name'];
            $existing = array_filter($columns, fn($c) => Schema::hasColumn('assets', $c));
            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
};
