<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('fee_payments', 'school_id')) {
            Schema::table('fee_payments', function (Blueprint $table) {
                $table->foreignId('school_id')->nullable()->after('fee_invoice_id')->constrained()->cascadeOnDelete();
                $table->index(['school_id', 'payment_date']);
            });

            // Backfill from parent invoice so existing rows stay tenant-isolated.
            DB::statement('UPDATE fee_payments fp JOIN fee_invoices fi ON fi.id = fp.fee_invoice_id SET fp.school_id = fi.school_id WHERE fp.school_id IS NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('fee_payments', 'school_id')) {
            Schema::table('fee_payments', function (Blueprint $table) {
                $table->dropForeign(['school_id']);
                $table->dropIndex(['school_id_payment_date']);
                $table->dropColumn('school_id');
            });
        }
    }
};
