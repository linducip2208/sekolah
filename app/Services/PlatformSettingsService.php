<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class PlatformSettingsService
{
    public const CACHE_KEY = 'platform_settings';

    public const IMAGE_FIELDS = [
        'logo_path',
        'logo_dark_path',
        'favicon_path',
        'hero_image_path',
        'crest_path',
        'og_image_path',
    ];

    public function defaults(): array
    {
        return [
            'app_name'         => 'Sikad Pro',
            'tagline'          => 'Floreat Schola — Excellence Through Tradition',
            'motto_latin'      => 'Floreat Schola',
            'motto_translated' => 'Let the school flourish',
            'description'      => 'A modern school management platform crafted with the rigour and grace of the world\'s leading educational institutions.',

            'established_year' => '1890',
            'institution_type' => 'Multi-Tenant Academy Platform',

            'hero_kicker'   => 'Founded MDCCCXC',
            'hero_title'    => 'A Tradition of Excellence,<br>A Future of Possibility.',
            'hero_subtitle' => 'Sikad Pro unites timeless pedagogy with modern technology — equipping institutions with the tools to nurture remarkable scholars.',

            'logo_path'        => null,
            'logo_dark_path'   => null,
            'favicon_path'     => null,
            'hero_image_path'  => null,
            'crest_path'       => null,
            'og_image_path'    => null,

            'color_primary'   => '#2563EB',
            'color_secondary' => '#1D4ED8',
            'color_accent'    => '#F59E0B',
            'color_paper'     => '#F8FAFC',

            'contact_phone'    => '081296052010',
            'contact_whatsapp' => '6281296052010',
            'contact_email'    => 'admissions@sikadpro.test',
            'address_line1'    => 'Sikad Pro Foundation House',
            'address_line2'    => 'Jakarta, Indonesia',

            'social_facebook'  => null,
            'social_instagram' => null,
            'social_youtube'   => null,
            'social_linkedin'  => null,

            'popup_enabled'  => true,
            'popup_title'    => 'Source Code Dijual',
            'popup_message'  => 'Aplikasi Sikad Pro ini tersedia untuk dibeli — termasuk full source code, dokumentasi, dan dukungan migrasi. Hubungi kami untuk negosiasi harga.',
            'popup_phone'    => '081296052010',
            'popup_whatsapp' => '6281296052010',
            'popup_cta_text' => 'Hubungi Sekarang',

            'footer_disclaimer' => 'Sikad Pro is a trademark of the Sikad Pro. All rights reserved.',

            'landing_theme' => 'modern',

            'cache_version' => 1,
        ];
    }

    public function all(): array
    {
        $stored = Cache::get(self::CACHE_KEY, []);
        $merged = array_merge($this->defaults(), is_array($stored) ? $stored : []);

        foreach (self::IMAGE_FIELDS as $field) {
            $merged[str_replace('_path', '_url', $field)] = $this->resolveUrl($merged[$field] ?? null);
        }

        $whatsapp = $merged['contact_whatsapp'] ?? null;
        $merged['whatsapp_link'] = $whatsapp ? 'https://wa.me/' . preg_replace('/\D/', '', $whatsapp) : null;

        $popupWa = $merged['popup_whatsapp'] ?? null;
        $merged['popup_whatsapp_link'] = $popupWa
            ? 'https://wa.me/' . preg_replace('/\D/', '', $popupWa) . '?text=' . rawurlencode('Halo, saya tertarik membeli source code ' . ($merged['app_name'] ?? 'Sikad Pro') . '.')
            : null;

        return $merged;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->all();
        return $all[$key] ?? $default;
    }

    public function update(array $data): array
    {
        $current = Cache::get(self::CACHE_KEY, []);
        if (!is_array($current)) {
            $current = [];
        }

        $allowed = array_keys($this->defaults());
        $patch   = array_intersect_key($data, array_flip($allowed));

        foreach (['popup_enabled'] as $boolField) {
            if (array_key_exists($boolField, $patch)) {
                $patch[$boolField] = filter_var($patch[$boolField], FILTER_VALIDATE_BOOLEAN);
            }
        }

        $updated = array_merge($current, $patch);
        $updated['cache_version'] = ($current['cache_version'] ?? 1) + 1;

        Cache::forever(self::CACHE_KEY, $updated);

        return $this->all();
    }

    public function uploadImage(string $field, UploadedFile $file): string
    {
        if (!in_array($field, self::IMAGE_FIELDS, true)) {
            throw new \InvalidArgumentException("Unknown platform image field: {$field}");
        }

        $disk      = config('filesystems.default');
        $extension = $file->getClientOriginalExtension() ?: $file->extension();
        $path      = $file->storeAs(
            'platform',
            str_replace('_path', '', $field) . '-' . time() . '.' . $extension,
            ['disk' => $disk]
        );

        $current = Cache::get(self::CACHE_KEY, []);
        if (is_array($current) && !empty($current[$field]) && $current[$field] !== $path) {
            Storage::disk($disk)->delete($current[$field]);
        }

        $this->update([$field => $path]);

        return $path;
    }

    public function removeImage(string $field): void
    {
        if (!in_array($field, self::IMAGE_FIELDS, true)) {
            throw new \InvalidArgumentException("Unknown platform image field: {$field}");
        }

        $current = Cache::get(self::CACHE_KEY, []);
        if (is_array($current) && !empty($current[$field])) {
            Storage::disk(config('filesystems.default'))->delete($current[$field]);
            $this->update([$field => null]);
        }
    }

    public function resolveUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        try {
            return Storage::disk(config('filesystems.default'))->url($path);
        } catch (\Throwable) {
            return null;
        }
    }
}
