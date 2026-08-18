<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_cards', function (Blueprint $table) {
            $table->string('verification_code', 32)->unique()->after('verification_token')->nullable();
            $table->string('qr_token', 64)->nullable()->after('verification_code');
        });
    }

    public function down(): void
    {
        Schema::table('report_cards', function (Blueprint $table) {
            $table->dropColumn(['verification_code', 'qr_token']);
        });
    }
};
