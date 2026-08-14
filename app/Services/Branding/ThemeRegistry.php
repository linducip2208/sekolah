<?php

namespace App\Services\Branding;

/**
 * Registry of built-in white-label themes.
 *
 * A "theme" bundles a cohesive set of design tokens — brand palette,
 * typography (body + display), border radius and surface tones — so a
 * school can switch its whole look with one selection, then fine-tune
 * colours afterwards. All business logic remains shared; only the
 * presentation tokens differ.
 */
class ThemeRegistry
{
    public const DEFAULT = 'elegant';

    public static function keys(): array
    {
        return array_column(self::themes(), 'key');
    }

    public static function get(?string $key): array
    {
        $key = $key ?: self::DEFAULT;
        foreach (self::themes() as $theme) {
            if ($theme['key'] === $key) {
                return $theme;
            }
        }
        return self::get(self::DEFAULT);
    }

    public static function themes(): array
    {
        return [
            [
                'key'         => 'elegant',
                'name'        => 'Elegan',
                'description' => 'Navy & emas klasik bergaya akademi bergengsi — serif untuk judul, krem hangat.',
                'palette' => [
                    'primary'      => '#0b1d3a',
                    'secondary'    => '#7a1e2b',
                    'accent'       => '#b8860b',
                    'sidebar'      => '#0b1d3a',
                    'sidebar_text' => '#f8f5ee',
                    'success'      => '#16A34A',
                    'warning'      => '#EAB308',
                    'danger'       => '#DC2626',
                ],
                'fonts' => [
                    'body'    => "'Inter', ui-sans-serif, system-ui, sans-serif",
                    'display' => "'Playfair Display', Georgia, serif",
                    'url'     => 'https://fonts.bunny.net/css?family=inter:400,500,600,700|playfair-display:500,600,700',
                ],
                'radius' => ['sm' => '2px', 'md' => '6px', 'lg' => '10px'],
                'surface' => [
                    'paper' => '#f8f5ee',
                    'ink'   => '#1a1a1a',
                    'muted' => '#6b6660',
                    'rule'  => 'rgba(11, 29, 58, 0.15)',
                ],
            ],
            [
                'key'         => 'corporate',
                'name'        => 'Korporat',
                'description' => 'Indigo profesional & tegas — Inter, sudut tajam, cocok untuk yayasan & konsorsium.',
                'palette' => [
                    'primary'      => '#1E40AF',
                    'secondary'    => '#1D4ED8',
                    'accent'       => '#2563EB',
                    'sidebar'      => '#0F172A',
                    'sidebar_text' => '#E2E8F0',
                    'success'      => '#16A34A',
                    'warning'      => '#EAB308',
                    'danger'       => '#DC2626',
                ],
                'fonts' => [
                    'body'    => "'Inter', ui-sans-serif, system-ui, sans-serif",
                    'display' => "'Inter', ui-sans-serif, system-ui, sans-serif",
                    'url'     => 'https://fonts.bunny.net/css?family=inter:400,500,600,700,800',
                ],
                'radius' => ['sm' => '4px', 'md' => '8px', 'lg' => '12px'],
                'surface' => [
                    'paper' => '#F1F5F9',
                    'ink'   => '#0F172A',
                    'muted' => '#64748B',
                    'rule'  => 'rgba(15, 23, 42, 0.14)',
                ],
            ],
            [
                'key'         => 'modern',
                'name'        => 'Modern',
                'description' => 'Ungu violet segar & membulat — Plus Jakarta Sans, ringan, cocok untuk startup edutech.',
                'palette' => [
                    'primary'      => '#6D28D9',
                    'secondary'    => '#7C3AED',
                    'accent'       => '#8B5CF6',
                    'sidebar'      => '#1E1B4B',
                    'sidebar_text' => '#E0E7FF',
                    'success'      => '#10B981',
                    'warning'      => '#F59E0B',
                    'danger'       => '#EF4444',
                ],
                'fonts' => [
                    'body'    => "'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif",
                    'display' => "'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif",
                    'url'     => 'https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800',
                ],
                'radius' => ['sm' => '8px', 'md' => '14px', 'lg' => '18px'],
                'surface' => [
                    'paper' => '#FAF9FF',
                    'ink'   => '#1E1B4B',
                    'muted' => '#6D6A8A',
                    'rule'  => 'rgba(76, 29, 149, 0.14)',
                ],
            ],
            [
                'key'         => 'minimal',
                'name'        => 'Minimalis',
                'description' => 'Slate netral & bersih — Manrope, halus, banyak whitespace, fokus pada konten.',
                'palette' => [
                    'primary'      => '#334155',
                    'secondary'    => '#475569',
                    'accent'       => '#64748B',
                    'sidebar'      => '#1E293B',
                    'sidebar_text' => '#F1F5F9',
                    'success'      => '#16A34A',
                    'warning'      => '#EAB308',
                    'danger'       => '#DC2626',
                ],
                'fonts' => [
                    'body'    => "'Manrope', ui-sans-serif, system-ui, sans-serif",
                    'display' => "'Manrope', ui-sans-serif, system-ui, sans-serif",
                    'url'     => 'https://fonts.bunny.net/css?family=manrope:400,500,600,700,800',
                ],
                'radius' => ['sm' => '4px', 'md' => '6px', 'lg' => '10px'],
                'surface' => [
                    'paper' => '#FFFFFF',
                    'ink'   => '#0F172A',
                    'muted' => '#64748B',
                    'rule'  => 'rgba(15, 23, 42, 0.10)',
                ],
            ],
            [
                'key'         => 'academic',
                'name'        => 'Akademik',
                'description' => 'Teal & hijau segar — Nunito ramah, hangat, cocok untuk sekolah & pesantren.',
                'palette' => [
                    'primary'      => '#0F766E',
                    'secondary'    => '#0D9488',
                    'accent'       => '#14B8A6',
                    'sidebar'      => '#134E4A',
                    'sidebar_text' => '#CCFBF1',
                    'success'      => '#16A34A',
                    'warning'      => '#EAB308',
                    'danger'       => '#DC2626',
                ],
                'fonts' => [
                    'body'    => "'Nunito', ui-sans-serif, system-ui, sans-serif",
                    'display' => "'Nunito', ui-sans-serif, system-ui, sans-serif",
                    'url'     => 'https://fonts.bunny.net/css?family=nunito:400,500,600,700,800',
                ],
                'radius' => ['sm' => '8px', 'md' => '10px', 'lg' => '14px'],
                'surface' => [
                    'paper' => '#F0FDFA',
                    'ink'   => '#134E4A',
                    'muted' => '#5F7161',
                    'rule'  => 'rgba(15, 118, 110, 0.14)',
                ],
            ],
        ];
    }
}
