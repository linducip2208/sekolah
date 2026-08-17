<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_cards', function (Blueprint $table) {
            $table->enum('status', ['draft', 'submitted', 'approved', 'locked'])->default('draft')->after('is_published');
            $table->foreignId('approved_by')->nullable()->after('verification_token')->constrained('users')->nullOnDelete();
            $table->datetime('approved_at')->nullable()->after('approved_by');
            $table->datetime('locked_at')->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('report_cards', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['status', 'approved_at', 'locked_at']);
        });
    }
};
