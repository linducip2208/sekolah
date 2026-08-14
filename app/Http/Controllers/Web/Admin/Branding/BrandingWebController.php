<?php

namespace App\Http\Controllers\Web\Admin\Branding;

use App\Http\Controllers\Controller;
use App\Services\Branding\BrandingService;
use App\Services\Branding\ThemeRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandingWebController extends Controller
{
    public function __construct(private BrandingService $branding) {}

    public function show(): View
    {
        $branding = $this->branding->getForSchool(auth()->user()->school_id);
        $themes   = ThemeRegistry::themes();
        $selected = $branding['theme'] ?? ThemeRegistry::DEFAULT;
        return view('school-admin.branding.show', compact('branding', 'themes', 'selected'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'display_name'           => 'nullable|string|max:200',
            'tagline'                => 'nullable|string|max:200',
            'school_type_label'      => 'nullable|string|max:100',
            'academic_year_format'   => 'nullable|string|max:30',
            'color_primary'          => 'nullable|string|regex:/^#[0-9A-Fa-f]{6,8}$/',
            'color_secondary'        => 'nullable|string|regex:/^#[0-9A-Fa-f]{6,8}$/',
            'color_success'          => 'nullable|string|regex:/^#[0-9A-Fa-f]{6,8}$/',
            'color_warning'          => 'nullable|string|regex:/^#[0-9A-Fa-f]{6,8}$/',
            'color_danger'           => 'nullable|string|regex:/^#[0-9A-Fa-f]{6,8}$/',
            'color_accent'           => 'nullable|string|regex:/^#[0-9A-Fa-f]{6,8}$/',
            'color_sidebar'          => 'nullable|string|regex:/^#[0-9A-Fa-f]{6,8}$/',
            'color_sidebar_text'     => 'nullable|string|regex:/^#[0-9A-Fa-f]{6,8}$/',
            'font_family'            => 'nullable|string|max:200',
            'google_fonts_url'       => 'nullable|url|max:500',
            'custom_domain'          => 'nullable|string|max:200|regex:/^[a-z0-9.\-]+$/',
            'custom_css'             => 'nullable|string|max:50000',
            'custom_js'              => 'nullable|string|max:50000',
            'theme'                  => 'nullable|in:'.implode(',', ThemeRegistry::keys()),
            'background_mode'        => 'nullable|in:light,dark,auto',
            'login_show_motto'       => 'nullable|in:0,1,true,false',
            'mobile_splash_bg_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6,8}$/',
            'mobile_app_display_name'=> 'nullable|string|max:200',
            'email_footer_text'      => 'nullable|string|max:2000',
            'receipt_layout'         => 'nullable|in:simple,formal,modern',
            'pdf_watermark_enabled'  => 'nullable|in:0,1,true,false',
            'fcm_notification_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6,8}$/',
            'login_welcome_id'       => 'nullable|string|max:500',
            'login_welcome_en'       => 'nullable|string|max:500',
        ]);

        if (isset($data['login_welcome_id']) || isset($data['login_welcome_en'])) {
            $data['login_welcome_text'] = [
                'id' => $data['login_welcome_id'] ?? null,
                'en' => $data['login_welcome_en'] ?? null,
            ];
            unset($data['login_welcome_id'], $data['login_welcome_en']);
        }

        foreach (['login_show_motto', 'pdf_watermark_enabled'] as $bool) {
            if (array_key_exists($bool, $data)) {
                $data[$bool] = filter_var($data[$bool], FILTER_VALIDATE_BOOL);
            }
        }

        $schoolId = auth()->user()->school_id;
        $newTheme = $data['theme'] ?? null;
        if ($newTheme) {
            $current = $this->branding->getForSchool($schoolId);
            if (($current['theme'] ?? null) !== $newTheme) {
                $palette = ThemeRegistry::get($newTheme)['palette'];
                foreach (['primary', 'secondary', 'accent', 'sidebar', 'sidebar_text'] as $key) {
                    $data["color_{$key}"] = $palette[$key];
                }
            }
        }

        $this->branding->update($schoolId, $data);
        return redirect()->route('admin.branding.show')->with('success', 'Branding berhasil diperbarui.');
    }

    public function uploadLogo(Request $request): RedirectResponse
    {
        $request->validate([
            'type' => 'required|in:primary,secondary,monochrome,favicon,login_bg,splash_logo,email_header,fcm_icon',
            'file' => 'required|file|max:2048|mimes:png,jpg,jpeg,svg,ico,webp',
        ]);

        $this->branding->uploadLogo(
            auth()->user()->school_id,
            $request->input('type'),
            $request->file('file'),
        );

        return redirect()->route('admin.branding.show')->with('success', 'Logo berhasil diunggah.');
    }

    public function removeLogo(string $type): RedirectResponse
    {
        $this->branding->removeLogo(auth()->user()->school_id, $type);
        return redirect()->route('admin.branding.show')->with('success', 'Logo dihapus.');
    }

    public function reset(): RedirectResponse
    {
        $this->branding->reset(auth()->user()->school_id);
        return redirect()->route('admin.branding.show')->with('success', 'Branding di-reset ke default.');
    }
}
