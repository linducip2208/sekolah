<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('students', function (Blueprint $t) {
            $t->string('whatsapp_phone', 30)->nullable()->after('guardian_phone');
        });

        Schema::table('staffs', function (Blueprint $t) {
            $t->string('whatsapp_phone', 30)->nullable()->after('basic_salary');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $t) {
            $t->dropColumn('whatsapp_phone');
        });

        Schema::table('staffs', function (Blueprint $t) {
            $t->dropColumn('whatsapp_phone');
        });
    }
};
