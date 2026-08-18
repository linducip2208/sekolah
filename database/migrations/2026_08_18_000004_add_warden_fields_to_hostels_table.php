<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hostels', function (Blueprint $table) {
            if (!Schema::hasColumn('hostels', 'warden_phone')) {
                $table->string('warden_phone', 30)->nullable()->after('warden_name');
            }
            if (!Schema::hasColumn('hostels', 'warden_email')) {
                $table->string('warden_email')->nullable()->after('warden_phone');
            }
            if (!Schema::hasColumn('hostels', 'description')) {
                $table->text('description')->nullable()->after('warden_email');
            }
            if (!Schema::hasColumn('hostels', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hostels', function (Blueprint $table) {
            $table->dropColumn(['warden_phone', 'warden_email', 'description', 'is_active']);
        });
    }
};
