<?php

namespace App\Services;

/**
 * Registry of built-in public landing-page templates.
 *
 * Templates only control presentation (tokens, fonts, radius, shadow, button /
 * navbar / hero style). Business content lives in a single shared data set
 * (LandingController) and shared Blade components, so adding a template never
 * duplicates copy or markup.
 */
class LandingThemeRegistry
{
    public const DEFAULT = 'modern';

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
                'key'         => 'modern',
                'name'        => 'Modern SaaS',
                'description' => 'Bersih, teknologi, startup — tombol pill, kartu ber-shadow, radius besar.',
                'fonts' => [
                    'body'    => "'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif",
                    'display' => "'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif",
                    'url'     => 'https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800',
                ],
                'vars' => [
                    '--lp-primary'       => '#6D28D9',
                    '--lp-primary-dark'  => '#5B21B6',
                    '--lp-accent'        => '#8B5CF6',
                    '--lp-accent-soft'   => 'rgba(139, 92, 246, 0.12)',
                    '--lp-bg'            => '#FFFFFF',
                    '--lp-surface'       => '#FFFFFF',
                    '--lp-ink'           => '#1E1B4B',
                    '--lp-muted'         => '#6D6A8A',
                    '--lp-border'        => 'rgba(30, 27, 75, 0.12)',
                    '--lp-radius-sm'     => '10px',
                    '--lp-radius-md'     => '14px',
                    '--lp-radius-lg'     => '20px',
                    '--lp-radius-btn'    => '999px',
                    '--lp-shadow'        => '0 16px 40px -16px rgba(30, 27, 75, 0.18)',
                ],
                'style' => ['hero' => 'split', 'navbar' => 'solid', 'card' => 'shadow', 'button' => 'pill'],
            ],
            [
                'key'         => 'corporate',
                'name'        => 'Korporat',
                'description' => 'Enterprise, profesional, terstruktur — Inter, sudut tajam, premium.',
                'fonts' => [
                    'body'    => "'Inter', ui-sans-serif, system-ui, sans-serif",
                    'display' => "'Inter', ui-sans-serif, system-ui, sans-serif",
                    'url'     => 'https://fonts.bunny.net/css?family=inter:400,500,600,700,800',
                ],
                'vars' => [
                    '--lp-primary'       => '#1E40AF',
                    '--lp-primary-dark'  => '#1E3A8A',
                    '--lp-accent'        => '#2563EB',
                    '--lp-accent-soft'   => 'rgba(37, 99, 235, 0.12)',
                    '--lp-bg'            => '#F8FAFC',
                    '--lp-surface'       => '#FFFFFF',
                    '--lp-ink'           => '#0F172A',
                    '--lp-muted'         => '#64748B',
                    '--lp-border'        => 'rgba(15, 23, 42, 0.12)',
                    '--lp-radius-sm'     => '6px',
                    '--lp-radius-md'     => '10px',
                    '--lp-radius-lg'     => '14px',
                    '--lp-radius-btn'    => '8px',
                    '--lp-shadow'        => '0 10px 30px -12px rgba(15, 23, 42, 0.18)',
                ],
                'style' => ['hero' => 'split', 'navbar' => 'solid', 'card' => 'shadow', 'button' => 'sharp'],
            ],
            [
                'key'         => 'classic',
                'name'        => 'Classic Academic',
                'description' => 'Elegant, institusional, terpercaya — serif untuk judul, aksen emas.',
                'fonts' => [
                    'body'    => "'Inter', ui-sans-serif, system-ui, sans-serif",
                    'display' => "'Playfair Display', Georgia, serif",
                    'url'     => 'https://fonts.bunny.net/css?family=inter:400,500,600,700|playfair-display:500,600,700',
                ],
                'vars' => [
                    '--lp-primary'       => '#0b1d3a',
                    '--lp-primary-dark'  => '#081527',
                    '--lp-accent'        => '#b8860b',
                    '--lp-accent-soft'   => 'rgba(184, 134, 11, 0.12)',
                    '--lp-bg'            => '#f8f5ee',
                    '--lp-surface'       => '#ffffff',
                    '--lp-ink'           => '#1a1a1a',
                    '--lp-muted'         => '#6b6660',
                    '--lp-border'        => 'rgba(11, 29, 58, 0.16)',
                    '--lp-radius-sm'     => '4px',
                    '--lp-radius-md'     => '8px',
                    '--lp-radius-lg'     => '12px',
                    '--lp-radius-btn'    => '4px',
                    '--lp-shadow'        => '0 12px 32px -16px rgba(11, 29, 58, 0.24)',
                ],
                'style' => ['hero' => 'split', 'navbar' => 'solid', 'card' => 'bordered', 'button' => 'sharp'],
            ],
            [
                'key'         => 'minimal',
                'name'        => 'Minimalis',
                'description' => 'Sederhana, fokus tipografi, banyak whitespace — netral & bersih.',
                'fonts' => [
                    'body'    => "'Manrope', ui-sans-serif, system-ui, sans-serif",
                    'display' => "'Manrope', ui-sans-serif, system-ui, sans-serif",
                    'url'     => 'https://fonts.bunny.net/css?family=manrope:400,500,600,700,800',
                ],
                'vars' => [
                    '--lp-primary'       => '#0F172A',
                    '--lp-primary-dark'  => '#020617',
                    '--lp-accent'        => '#334155',
                    '--lp-accent-soft'   => 'rgba(51, 65, 85, 0.10)',
                    '--lp-bg'            => '#FFFFFF',
                    '--lp-surface'       => '#FFFFFF',
                    '--lp-ink'           => '#0F172A',
                    '--lp-muted'         => '#64748B',
                    '--lp-border'        => 'rgba(15, 23, 42, 0.10)',
                    '--lp-radius-sm'     => '4px',
                    '--lp-radius-md'     => '8px',
                    '--lp-radius-lg'     => '12px',
                    '--lp-radius-btn'    => '6px',
                    '--lp-shadow'        => 'none',
                ],
                'style' => ['hero' => 'centered', 'navbar' => 'minimal', 'card' => 'flat', 'button' => 'sharp'],
            ],
            [
                'key'         => 'islamic',
                'name'        => 'Islamic Education',
                'description' => 'Elegan, edukatif, hormat — hijau & emas, motif geometris halus.',
                'fonts' => [
                    'body'    => "'Inter', ui-sans-serif, system-ui, sans-serif",
                    'display' => "'Lora', Georgia, serif",
                    'url'     => 'https://fonts.bunny.net/css?family=inter:400,500,600,700|lora:500,600,700',
                ],
                'vars' => [
                    '--lp-primary'       => '#14532d',
                    '--lp-primary-dark'  => '#0f3d22',
                    '--lp-accent'        => '#b8860b',
                    '--lp-accent-soft'   => 'rgba(184, 134, 11, 0.14)',
                    '--lp-bg'            => '#f7f5ef',
                    '--lp-surface'       => '#ffffff',
                    '--lp-ink'           => '#1c2a22',
                    '--lp-muted'         => '#5f6b62',
                    '--lp-border'        => 'rgba(20, 83, 45, 0.16)',
                    '--lp-radius-sm'     => '8px',
                    '--lp-radius-md'     => '12px',
                    '--lp-radius-lg'     => '16px',
                    '--lp-radius-btn'    => '10px',
                    '--lp-shadow'        => '0 12px 32px -16px rgba(20, 83, 45, 0.22)',
                ],
                'style' => ['hero' => 'split', 'navbar' => 'solid', 'card' => 'bordered', 'button' => 'rounded', 'pattern' => true],
            ],
        ];
    }
}
