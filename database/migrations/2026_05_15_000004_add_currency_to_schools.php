<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $t) {
            $t->string('currency_code', 3)->default('IDR')->after('locale');
            $t->string('currency_symbol', 8)->default('Rp')->after('currency_code');
            $t->unsignedSmallInteger('currency_decimals')->default(0)->after('currency_symbol');
            $t->string('currency_thousands_sep', 2)->default('.')->after('currency_decimals');
            $t->string('currency_decimal_sep', 2)->default(',')->after('currency_thousands_sep');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $t) {
            $t->dropColumn([
                'currency_code', 'currency_symbol',
                'currency_decimals', 'currency_thousands_sep', 'currency_decimal_sep',
            ]);
        });
    }
};
