<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('workflow_requests') || DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE workflow_requests MODIFY status ENUM('draft', 'submitted', 'under_review', 'returned', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'submitted'");
    }

    public function down(): void
    {
        if (! Schema::hasTable('workflow_requests') || DB::getDriverName() !== 'mysql') {
            return;
        }

        if (DB::table('workflow_requests')->whereIn('status', ['returned', 'cancelled'])->exists()) {
            return;
        }

        DB::statement("ALTER TABLE workflow_requests MODIFY status ENUM('draft', 'submitted', 'under_review', 'approved', 'rejected') NOT NULL DEFAULT 'submitted'");
    }
};
