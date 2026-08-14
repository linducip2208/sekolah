<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('school_branding', function (Blueprint $t) {
            $t->string('color_text', 9)->nullable()->after('color_sidebar_text');
            $t->string('color_text_muted', 9)->nullable()->after('color_text');
            $t->string('font_scale', 10)->default('normal')->after('color_text_muted');
            $t->string('radius_scale', 10)->default('medium')->after('font_scale');
        });
    }

    public function down(): void
    {
        Schema::table('school_branding', function (Blueprint $t) {
            $t->dropColumn(['color_text', 'color_text_muted', 'font_scale', 'radius_scale']);
        });
    }
};
