<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitor_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('visitor_logs', 'qr_code')) {
                $table->string('qr_code', 64)->nullable()->unique()->after('note');
            }
            if (!Schema::hasColumn('visitor_logs', 'pre_registered')) {
                $table->boolean('pre_registered')->default(false)->after('qr_code');
            }
            if (!Schema::hasColumn('visitor_logs', 'host_staff_id')) {
                $table->foreignId('host_staff_id')->nullable()->after('pre_registered')->constrained('staff')->nullOnDelete();
            }
            if (!Schema::hasColumn('visitor_logs', 'vehicle_plate')) {
                $table->string('vehicle_plate', 20)->nullable()->after('host_staff_id');
            }
            if (!Schema::hasColumn('visitor_logs', 'expected_arrival')) {
                $table->dateTime('expected_arrival')->nullable()->after('vehicle_plate');
            }
            if (!Schema::hasColumn('visitor_logs', 'status')) {
                $table->enum('status', ['pending', 'checked_in', 'checked_out', 'cancelled'])->default('pending')->after('expected_arrival');
            }
        });
    }

    public function down(): void
    {
        Schema::table('visitor_logs', function (Blueprint $table) {
            if (Schema::hasColumn('visitor_logs', 'host_staff_id')) {
                $table->dropForeign(['host_staff_id']);
            }
            $columns = ['qr_code', 'pre_registered', 'host_staff_id', 'vehicle_plate', 'expected_arrival', 'status'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('visitor_logs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
