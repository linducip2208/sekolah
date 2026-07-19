<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_branding', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->unique()->constrained()->cascadeOnDelete();

            $table->string('display_name')->nullable();
            $table->string('tagline')->nullable();
            $table->string('school_type_label', 100)->nullable();
            $table->string('academic_year_format', 30)->default('Y/Y+1');

            $table->string('logo_primary_path', 500)->nullable();
            $table->string('logo_secondary_path', 500)->nullable();
            $table->string('logo_monochrome_path', 500)->nullable();
            $table->string('favicon_path', 500)->nullable();

            $table->string('color_primary', 9)->default('#2563EB');
            $table->string('color_secondary', 9)->default('#64748B');
            $table->string('color_success', 9)->default('#16A34A');
            $table->string('color_warning', 9)->default('#EAB308');
            $table->string('color_danger', 9)->default('#DC2626');
            $table->string('background_mode', 10)->default('light');

            $table->string('login_background_path', 500)->nullable();
            $table->json('login_welcome_text')->nullable();
            $table->boolean('login_show_motto')->default(true);

            $table->string('mobile_splash_logo_path', 500)->nullable();
            $table->string('mobile_splash_bg_color', 9)->default('#FFFFFF');
            $table->string('mobile_app_display_name')->nullable();

            $table->string('email_header_logo_path', 500)->nullable();
            $table->text('email_footer_text')->nullable();
            $table->string('receipt_layout', 20)->default('formal');
            $table->boolean('pdf_watermark_enabled')->default(false);

            $table->string('fcm_notification_icon_path', 500)->nullable();
            $table->string('fcm_notification_color', 9)->nullable();

            $table->unsignedInteger('cache_version')->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_branding');
    }
};
