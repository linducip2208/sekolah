<?php

namespace App\Models\Branding;

use App\Models\School;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolBranding extends Model
{
    protected $table = 'school_branding';

    protected $fillable = [
        'school_id',
        'display_name', 'tagline', 'school_type_label', 'academic_year_format',
        'logo_primary_path', 'logo_secondary_path', 'logo_monochrome_path', 'favicon_path',
        'color_primary', 'color_secondary', 'color_success', 'color_warning', 'color_danger',
        'color_accent', 'color_sidebar', 'color_sidebar_text',
        'font_family', 'google_fonts_url', 'custom_domain', 'custom_css', 'custom_js',
        'theme',
        'background_mode',
        'login_background_path', 'login_welcome_text', 'login_show_motto',
        'mobile_splash_logo_path', 'mobile_splash_bg_color', 'mobile_app_display_name',
        'email_header_logo_path', 'email_footer_text',
        'receipt_layout', 'pdf_watermark_enabled',
        'fcm_notification_icon_path', 'fcm_notification_color',
        'cache_version',
    ];

    protected $casts = [
        'login_welcome_text'    => 'array',
        'login_show_motto'      => 'boolean',
        'pdf_watermark_enabled' => 'boolean',
        'cache_version'         => 'integer',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
