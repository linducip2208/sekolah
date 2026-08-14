<?php

namespace App\Services;

/**
 * Registry of built-in public landing-page templates.
 *
 * All templates share the SIKAD brand identity — Deep Teal + Warm Amber accent +
 * Plus Jakarta Sans — and vary only in shade/neutrality and pattern. Content is
 * shared via LandingController + shared Blade components.
 */
class LandingThemeRegistry
{
    public const DEFAULT = 'modern';

    public const FONT = "'Manrope', ui-sans-serif, system-ui, sans-serif";
    public const FONT_URL = 'https://fonts.bunny.net/css?family=manrope:400,500,600,700,800';

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
                'description' => 'Teal segar — bersih, teknologi, startup SaaS.',
                'fonts' => [
                    'body'    => self::FONT,
                    'display' => self::FONT,
                    'url'     => self::FONT_URL,
                ],
                'vars' => [
                    '--lp-primary'        => '#2563EB',
                    '--lp-primary-dark'   => '#1D4ED8',
                    '--lp-accent'         => '#F59E0B',
                    '--lp-accent-soft'    => 'rgba(245, 158, 11, 0.12)',
                    '--lp-background'     => '#F8FAFC',
                    '--lp-surface'        => '#FFFFFF',
                    '--lp-surface-subtle' => '#F1F5F9',
                    '--lp-surface-muted'  => '#E2E8F0',
                    '--lp-surface-brand'  => '#EFF6FF',
                    '--lp-surface-dark'   => '#0F172A',
                    '--lp-ink'            => '#0F172A',
                    '--lp-muted'          => '#64748B',
                    '--lp-border'         => '#E2E8F0',
                    '--lp-hero-glow'      => 'rgba(37, 99, 235, 0.12)',
                    '--lp-pattern-color'  => 'rgba(37, 99, 235, 0.08)',
                    '--lp-pattern-opacity' => '0.5',
                    '--lp-radius-sm'      => '8px',
                    '--lp-radius-md'      => '10px',
                    '--lp-radius-lg'      => '14px',
                    '--lp-radius-btn'     => '10px',
                    '--lp-shadow'         => '0 12px 32px -16px rgba(23, 32, 30, 0.16)',
                ],
                'style' => ['hero' => 'split', 'navbar' => 'solid', 'card' => 'shadow', 'button' => 'sharp', 'pattern' => 'dots'],
            ],
            [
                'key'         => 'corporate',
                'name'        => 'Korporat',
                'description' => 'Teal + slate — enterprise, terstruktur, premium.',
                'fonts' => [
                    'body'    => self::FONT,
                    'display' => self::FONT,
                    'url'     => self::FONT_URL,
                ],
                'vars' => [
                    '--lp-primary'        => '#2563EB',
                    '--lp-primary-dark'   => '#1D4ED8',
                    '--lp-accent'         => '#475569',
                    '--lp-accent-soft'    => 'rgba(71, 85, 105, 0.10)',
                    '--lp-background'     => '#F8FAFC',
                    '--lp-surface'        => '#FFFFFF',
                    '--lp-surface-subtle' => '#F1F5F9',
                    '--lp-surface-muted'  => '#E8EDEB',
                    '--lp-surface-brand'  => '#EFF6FF',
                    '--lp-surface-dark'   => '#0F172A',
                    '--lp-ink'            => '#0F172A',
                    '--lp-muted'          => '#64748B',
                    '--lp-border'         => '#E2E8F0',
                    '--lp-hero-glow'      => 'rgba(37, 99, 235, 0.10)',
                    '--lp-pattern-color'  => 'rgba(37, 99, 235, 0.07)',
                    '--lp-pattern-opacity' => '0.6',
                    '--lp-radius-sm'      => '6px',
                    '--lp-radius-md'      => '10px',
                    '--lp-radius-lg'      => '14px',
                    '--lp-radius-btn'     => '10px',
                    '--lp-shadow'         => '0 10px 28px -14px rgba(23, 32, 30, 0.18)',
                ],
                'style' => ['hero' => 'split', 'navbar' => 'solid', 'card' => 'shadow', 'button' => 'sharp', 'pattern' => 'grid'],
            ],
            [
                'key'         => 'classic',
                'name'        => 'Classic Academic',
                'description' => 'Teal institusional yang dalam — elegan, akademik, terpercaya.',
                'fonts' => [
                    'body'    => self::FONT,
                    'display' => self::FONT,
                    'url'     => self::FONT_URL,
                ],
                'vars' => [
                    '--lp-primary'        => '#1E40AF',
                    '--lp-primary-dark'   => '#1E3A8A',
                    '--lp-accent'         => '#F59E0B',
                    '--lp-accent-soft'    => 'rgba(245, 158, 11, 0.12)',
                    '--lp-background'     => '#F8FAFC',
                    '--lp-surface'        => '#FFFFFF',
                    '--lp-surface-subtle' => '#F1F5F9',
                    '--lp-surface-muted'  => '#E2E8F0',
                    '--lp-surface-brand'  => '#EFF6FF',
                    '--lp-surface-dark'   => '#1E3A8A',
                    '--lp-ink'            => '#0F172A',
                    '--lp-muted'          => '#64748B',
                    '--lp-border'         => '#E2E8F0',
                    '--lp-hero-glow'      => 'rgba(30, 64, 175, 0.10)',
                    '--lp-pattern-color'  => 'rgba(30, 64, 175, 0.06)',
                    '--lp-pattern-opacity' => '0.5',
                    '--lp-radius-sm'      => '6px',
                    '--lp-radius-md'      => '10px',
                    '--lp-radius-lg'      => '14px',
                    '--lp-radius-btn'     => '10px',
                    '--lp-shadow'         => '0 12px 32px -16px rgba(23, 32, 30, 0.20)',
                ],
                'style' => ['hero' => 'split', 'navbar' => 'solid', 'card' => 'bordered', 'button' => 'sharp', 'pattern' => 'grid'],
            ],
            [
                'key'         => 'minimal',
                'name'        => 'Minimalis',
                'description' => 'Netral + teal — sederhana, fokus tipografi, banyak whitespace.',
                'fonts' => [
                    'body'    => self::FONT,
                    'display' => self::FONT,
                    'url'     => self::FONT_URL,
                ],
                'vars' => [
                    '--lp-primary'        => '#2563EB',
                    '--lp-primary-dark'   => '#1D4ED8',
                    '--lp-accent'         => '#64748B',
                    '--lp-accent-soft'    => 'rgba(100, 116, 139, 0.08)',
                    '--lp-background'     => '#FFFFFF',
                    '--lp-surface'        => '#FFFFFF',
                    '--lp-surface-subtle' => '#F8FAFC',
                    '--lp-surface-muted'  => '#F1F5F9',
                    '--lp-surface-brand'  => '#F0FDF9',
                    '--lp-surface-dark'   => '#0F172A',
                    '--lp-ink'            => '#0F172A',
                    '--lp-muted'          => '#64748B',
                    '--lp-border'         => '#E2E8F0',
                    '--lp-hero-glow'      => 'rgba(37, 99, 235, 0.06)',
                    '--lp-pattern-color'  => 'rgba(37, 99, 235, 0.05)',
                    '--lp-pattern-opacity' => '0',
                    '--lp-radius-sm'      => '4px',
                    '--lp-radius-md'      => '8px',
                    '--lp-radius-lg'      => '12px',
                    '--lp-radius-btn'     => '10px',
                    '--lp-shadow'         => 'none',
                ],
                'style' => ['hero' => 'flat', 'navbar' => 'minimal', 'card' => 'flat', 'button' => 'sharp', 'pattern' => 'none'],
            ],
            [
                'key'         => 'islamic',
                'name'        => 'Islamic Education',
                'description' => 'Teal + emas halus — edukatif, hormat, bermotif geometris lembut.',
                'fonts' => [
                    'body'    => self::FONT,
                    'display' => self::FONT,
                    'url'     => self::FONT_URL,
                ],
                'vars' => [
                    '--lp-primary'        => '#2563EB',
                    '--lp-primary-dark'   => '#1D4ED8',
                    '--lp-accent'         => '#F59E0B',
                    '--lp-accent-soft'    => 'rgba(245, 158, 11, 0.14)',
                    '--lp-background'     => '#F8FAFC',
                    '--lp-surface'        => '#FFFFFF',
                    '--lp-surface-subtle' => '#F1F5F9',
                    '--lp-surface-muted'  => '#E2E8F0',
                    '--lp-surface-brand'  => '#EFF6FF',
                    '--lp-surface-dark'   => '#1E3A8A',
                    '--lp-ink'            => '#0F172A',
                    '--lp-muted'          => '#64748B',
                    '--lp-border'         => '#E2E8F0',
                    '--lp-hero-glow'      => 'rgba(245, 158, 11, 0.10)',
                    '--lp-pattern-color'  => 'rgba(37, 99, 235, 0.08)',
                    '--lp-pattern-opacity' => '0.5',
                    '--lp-radius-sm'      => '8px',
                    '--lp-radius-md'      => '12px',
                    '--lp-radius-lg'      => '16px',
                    '--lp-radius-btn'     => '10px',
                    '--lp-shadow'         => '0 12px 32px -16px rgba(23, 32, 30, 0.18)',
                ],
                'style' => ['hero' => 'split', 'navbar' => 'solid', 'card' => 'bordered', 'button' => 'sharp', 'pattern' => 'geometric'],
            ],
        ];
    }
}
