<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('school_branding', function (Blueprint $t) {
            $t->string('color_table_header', 9)->nullable()->after('color_text_muted');
        });
    }

    public function down(): void
    {
        Schema::table('school_branding', function (Blueprint $t) {
            $t->dropColumn('color_table_header');
        });
    }
};
