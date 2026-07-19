<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('school_branding', function (Blueprint $t) {
            $t->string('color_accent', 9)->default('#0EA5E9')->after('color_danger');
            $t->string('color_sidebar', 9)->default('#0F172A')->after('color_accent');
            $t->string('color_sidebar_text', 9)->default('#F1F5F9')->after('color_sidebar');
            $t->string('font_family', 200)->nullable()->after('color_sidebar_text');
            $t->string('google_fonts_url', 500)->nullable()->after('font_family');
            $t->string('custom_domain', 200)->nullable()->after('google_fonts_url');
            $t->text('custom_css')->nullable()->after('custom_domain');
            $t->text('custom_js')->nullable()->after('custom_css');
            $t->unique('custom_domain');
        });
    }

    public function down(): void
    {
        Schema::table('school_branding', function (Blueprint $t) {
            $t->dropUnique(['custom_domain']);
            $t->dropColumn([
                'color_accent', 'color_sidebar', 'color_sidebar_text',
                'font_family', 'google_fonts_url', 'custom_domain',
                'custom_css', 'custom_js',
            ]);
        });
    }
};
