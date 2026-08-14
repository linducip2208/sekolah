<?php

namespace App\Services\Branding;

use App\Models\Branding\SchoolBranding;
use App\Models\School;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class BrandingService
{
    public const LOGO_TYPES = [
        'primary'      => 'logo_primary_path',
        'secondary'    => 'logo_secondary_path',
        'monochrome'   => 'logo_monochrome_path',
        'favicon'      => 'favicon_path',
        'login_bg'     => 'login_background_path',
        'splash_logo'  => 'mobile_splash_logo_path',
        'email_header' => 'email_header_logo_path',
        'fcm_icon'     => 'fcm_notification_icon_path',
    ];

    public function getForSchool(int $schoolId): array
    {
        return Cache::remember("branding:school:{$schoolId}", 3600, function () use ($schoolId) {
            $branding = SchoolBranding::firstOrCreate(['school_id' => $schoolId]);
            return $this->toArray($branding);
        });
    }

    public function getBySubdomain(string $subdomain): array
    {
        $school = School::where('subdomain', $subdomain)->firstOrFail();
        return $this->getForSchool($school->id);
    }

    public function update(int $schoolId, array $data): SchoolBranding
    {
        $branding = SchoolBranding::firstOrCreate(['school_id' => $schoolId]);

        $allowed = [
            'display_name', 'tagline', 'school_type_label', 'academic_year_format',
            'color_primary', 'color_secondary', 'color_success', 'color_warning', 'color_danger',
            'color_accent', 'color_sidebar', 'color_sidebar_text',
            'font_family', 'google_fonts_url', 'custom_domain', 'custom_css', 'custom_js',
            'theme',
            'background_mode',
            'login_welcome_text', 'login_show_motto',
            'mobile_splash_bg_color', 'mobile_app_display_name',
            'email_footer_text', 'receipt_layout', 'pdf_watermark_enabled',
            'fcm_notification_color',
        ];

        $branding->fill(array_intersect_key($data, array_flip($allowed)));
        $branding->cache_version = ($branding->cache_version ?? 1) + 1;
        $branding->save();

        $this->forgetCache($schoolId);

        return $branding->fresh();
    }

    public function uploadLogo(int $schoolId, string $type, UploadedFile $file): string
    {
        if (!isset(self::LOGO_TYPES[$type])) {
            throw new \InvalidArgumentException("Unknown logo type: {$type}");
        }

        $extension = $file->getClientOriginalExtension() ?: $file->extension();
        $path      = $file->storeAs(
            "branding/{$schoolId}",
            "{$type}-" . time() . '.' . $extension,
            ['disk' => config('filesystems.default')]
        );

        $branding = SchoolBranding::firstOrCreate(['school_id' => $schoolId]);
        $field    = self::LOGO_TYPES[$type];

        if ($branding->{$field} && $branding->{$field} !== $path) {
            Storage::disk(config('filesystems.default'))->delete($branding->{$field});
        }

        $branding->{$field} = $path;
        $branding->cache_version = ($branding->cache_version ?? 1) + 1;
        $branding->save();

        $this->forgetCache($schoolId);

        return $path;
    }

    public function removeLogo(int $schoolId, string $type): void
    {
        if (!isset(self::LOGO_TYPES[$type])) {
            throw new \InvalidArgumentException("Unknown logo type: {$type}");
        }

        $branding = SchoolBranding::firstOrCreate(['school_id' => $schoolId]);
        $field    = self::LOGO_TYPES[$type];

        if ($branding->{$field}) {
            Storage::disk(config('filesystems.default'))->delete($branding->{$field});
            $branding->{$field} = null;
            $branding->cache_version = ($branding->cache_version ?? 1) + 1;
            $branding->save();
        }

        $this->forgetCache($schoolId);
    }

    public function reset(int $schoolId): void
    {
        $branding = SchoolBranding::where('school_id', $schoolId)->first();
        if (!$branding) {
            return;
        }

        foreach (self::LOGO_TYPES as $field) {
            if ($branding->{$field}) {
                Storage::disk(config('filesystems.default'))->delete($branding->{$field});
            }
        }

        $branding->delete();
        SchoolBranding::create(['school_id' => $schoolId]);

        $this->forgetCache($schoolId);
    }

    public function resolveUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        try {
            return Storage::disk(config('filesystems.default'))->url($path);
        } catch (\Throwable) {
            return null;
        }
    }

    public function findByCustomDomain(string $host): ?SchoolBranding
    {
        return SchoolBranding::where('custom_domain', $host)->first();
    }

    public function generateCss(int $schoolId): string
    {
        $b = SchoolBranding::firstOrCreate(['school_id' => $schoolId]);
        $theme = ThemeRegistry::get($b->theme);

        // For a school that has explicitly picked a theme, its colour fields are the
        // source of truth (they were seeded from the theme and can be tweaked). For a
        // legacy school without a theme, fall back to the default theme palette so the
        // existing look is preserved.
        $useStored = (bool) $b->theme;
        $primary    = $useStored ? ($b->color_primary ?: $theme['palette']['primary'])         : $theme['palette']['primary'];
        $secondary  = $useStored ? ($b->color_secondary ?: $theme['palette']['secondary'])     : $theme['palette']['secondary'];
        $accent     = $useStored ? ($b->color_accent ?: $theme['palette']['accent'])           : $theme['palette']['accent'];
        $sidebar    = $useStored ? ($b->color_sidebar ?: $theme['palette']['sidebar'])         : $theme['palette']['sidebar'];
        $sidebarTxt = $useStored ? ($b->color_sidebar_text ?: $theme['palette']['sidebar_text']) : $theme['palette']['sidebar_text'];

        $fontBody    = $b->font_family ?: $theme['fonts']['body'];
        $fontDisplay = $theme['fonts']['display'];

        $css = ":root {\n";
        $css .= "  --c-primary: {$primary};\n";
        $css .= "  --c-secondary: {$secondary};\n";
        $css .= "  --c-accent: {$accent};\n";
        $css .= "  --c-paper: {$theme['surface']['paper']};\n";
        $css .= "  --c-ink: {$theme['surface']['ink']};\n";
        $css .= "  --c-muted: {$theme['surface']['muted']};\n";
        $css .= "  --c-rule: {$theme['surface']['rule']};\n";
        $css .= "  --brand-primary: {$primary};\n";
        $css .= "  --brand-secondary: {$secondary};\n";
        $css .= "  --brand-accent: {$accent};\n";
        $css .= "  --brand-success: {$b->color_success};\n";
        $css .= "  --brand-warning: {$b->color_warning};\n";
        $css .= "  --brand-danger: {$b->color_danger};\n";
        $css .= "  --brand-sidebar: {$sidebar};\n";
        $css .= "  --brand-sidebar-text: {$sidebarTxt};\n";
        $css .= "  --brand-font-family: {$fontBody};\n";
        $css .= "  --brand-display-font: {$fontDisplay};\n";
        $css .= "  --radius-sm: {$theme['radius']['sm']};\n";
        $css .= "  --radius-md: {$theme['radius']['md']};\n";
        $css .= "  --radius-lg: {$theme['radius']['lg']};\n";
        $css .= "}\n";
        $css .= "body { font-family: var(--brand-font-family); }\n";
        $css .= ".font-display, .elite-h1, .elite-h2, .elite-h3, .page-title, .section-title { font-family: var(--brand-display-font); }\n";

        if ($b->custom_css) {
            $css .= "\n/* school custom css */\n" . $b->custom_css;
        }

        return $css;
    }

    public function toArray(SchoolBranding $b): array
    {
        $theme = ThemeRegistry::get($b->theme);

        return [
            'theme'                => $b->theme,
            'display_name'         => $b->display_name,
            'tagline'              => $b->tagline,
            'school_type_label'    => $b->school_type_label,
            'academic_year_format' => $b->academic_year_format,
            'colors' => [
                'primary'   => $b->color_primary,
                'secondary' => $b->color_secondary,
                'accent'    => $b->color_accent ?? '#0EA5E9',
                'sidebar'   => $b->color_sidebar ?? '#0F172A',
                'sidebar_text' => $b->color_sidebar_text ?? '#F1F5F9',
                'success'   => $b->color_success,
                'warning'   => $b->color_warning,
                'danger'    => $b->color_danger,
            ],
            'font' => [
                'family'           => $b->font_family ?: $theme['fonts']['body'],
                'google_fonts_url' => $b->google_fonts_url ?: $theme['fonts']['url'],
            ],
            'custom' => [
                'domain' => $b->custom_domain,
                'css'    => (bool) $b->custom_css,
                'js'     => (bool) $b->custom_js,
            ],
            'background_mode' => $b->background_mode,
            'logos' => [
                'primary'      => $this->resolveUrl($b->logo_primary_path),
                'secondary'    => $this->resolveUrl($b->logo_secondary_path),
                'monochrome'   => $this->resolveUrl($b->logo_monochrome_path),
                'favicon'      => $this->resolveUrl($b->favicon_path),
                'login_bg'     => $this->resolveUrl($b->login_background_path),
                'splash_logo'  => $this->resolveUrl($b->mobile_splash_logo_path),
                'email_header' => $this->resolveUrl($b->email_header_logo_path),
                'fcm_icon'     => $this->resolveUrl($b->fcm_notification_icon_path),
            ],
            'login' => [
                'welcome_text' => $b->login_welcome_text,
                'show_motto'   => $b->login_show_motto,
            ],
            'mobile' => [
                'splash_bg_color' => $b->mobile_splash_bg_color,
                'app_name'        => $b->mobile_app_display_name,
            ],
            'email' => [
                'footer_text' => $b->email_footer_text,
            ],
            'pdf' => [
                'receipt_layout'    => $b->receipt_layout,
                'watermark_enabled' => $b->pdf_watermark_enabled,
            ],
            'notification' => [
                'color' => $b->fcm_notification_color,
            ],
            'cache_version' => $b->cache_version,
        ];
    }

    protected function forgetCache(int $schoolId): void
    {
        Cache::forget("branding:school:{$schoolId}");
    }
}
