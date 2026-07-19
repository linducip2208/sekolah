<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'book_categories',
        'admission_enquiries',
        'fee_structures',
        'fee_invoices',
        'payroll_structures',
        'salary_slips',
        'hostels',
        'hostel_allocations',
        'transport_routes',
        'vehicles',
        'student_transports',
        'conversations',
        'notifications_log',
        'subscription_transactions',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->softDeletes();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropSoftDeletes();
            });
        }
    }
};
