<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('document_approvals', 'decided_by')) {
            Schema::table('document_approvals', function (Blueprint $table) {
                $table->foreignId('decided_by')->nullable()->after('approver_id')->constrained('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('document_approvals', 'decided_by')) {
            Schema::table('document_approvals', function (Blueprint $table) {
                $table->dropForeign(['decided_by']);
                $table->dropColumn('decided_by');
            });
        }
    }
};
