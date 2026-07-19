<?php

namespace App\Http\Requests\Branding;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBrandingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'display_name'           => 'nullable|string|max:200',
            'tagline'                => 'nullable|string|max:200',
            'school_type_label'      => 'nullable|string|max:100',
            'academic_year_format'   => 'nullable|string|max:30',
            'color_primary'          => 'nullable|string|regex:/^#[0-9A-Fa-f]{6,8}$/',
            'color_secondary'        => 'nullable|string|regex:/^#[0-9A-Fa-f]{6,8}$/',
            'color_success'          => 'nullable|string|regex:/^#[0-9A-Fa-f]{6,8}$/',
            'color_warning'          => 'nullable|string|regex:/^#[0-9A-Fa-f]{6,8}$/',
            'color_danger'           => 'nullable|string|regex:/^#[0-9A-Fa-f]{6,8}$/',
            'background_mode'        => 'nullable|in:light,dark,auto',
            'login_welcome_text'     => 'nullable|array',
            'login_show_motto'       => 'nullable|boolean',
            'mobile_splash_bg_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6,8}$/',
            'mobile_app_display_name'=> 'nullable|string|max:200',
            'email_footer_text'      => 'nullable|string|max:2000',
            'receipt_layout'         => 'nullable|in:simple,formal,modern',
            'pdf_watermark_enabled'  => 'nullable|boolean',
            'fcm_notification_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6,8}$/',
        ];
    }
}
