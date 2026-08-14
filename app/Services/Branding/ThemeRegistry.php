<?php

namespace App\Services\Branding;

/**
 * Registry of built-in white-label themes.
 *
 * All themes share the SIKAD brand identity — Deep Teal primary + Warm Amber
 * accent + Plus Jakarta Sans — and vary only in shade/neutrality so white-label
 * schools stay visually consistent with the platform.
 */
class ThemeRegistry
{
    public const DEFAULT = 'elegant';

    public const FONT = "'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif";
    public const FONT_URL = 'https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800';

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
                'name'        => 'Classic',
                'description' => 'Teal institusional yang dalam — elegan, tenang, dan terpercaya.',
                'palette' => [
                    'primary'      => '#0B4F49',
                    'secondary'    => '#134E4A',
                    'accent'       => '#F59E0B',
                    'sidebar'      => '#0B4F49',
                    'sidebar_text' => '#F1F5F4',
                    'success'      => '#15803D',
                    'warning'      => '#D97706',
                    'danger'       => '#DC2626',
                ],
                'fonts' => [
                    'body'    => self::FONT,
                    'display' => self::FONT,
                    'url'     => self::FONT_URL,
                ],
                'radius' => ['sm' => '6px', 'md' => '10px', 'lg' => '14px'],
                'surface' => [
                    'paper' => '#F8FAF9',
                    'ink'   => '#17201E',
                    'muted' => '#66736F',
                    'rule'  => '#E2E8E5',
                ],
            ],
            [
                'key'         => 'corporate',
                'name'        => 'Korporat',
                'description' => 'Teal + slate — enterprise, terstruktur, dan profesional.',
                'palette' => [
                    'primary'      => '#0F766E',
                    'secondary'    => '#134E4A',
                    'accent'       => '#475569',
                    'sidebar'      => '#134E4A',
                    'sidebar_text' => '#F1F5F4',
                    'success'      => '#15803D',
                    'warning'      => '#D97706',
                    'danger'       => '#DC2626',
                ],
                'fonts' => [
                    'body'    => self::FONT,
                    'display' => self::FONT,
                    'url'     => self::FONT_URL,
                ],
                'radius' => ['sm' => '6px', 'md' => '10px', 'lg' => '14px'],
                'surface' => [
                    'paper' => '#F8FAF9',
                    'ink'   => '#17201E',
                    'muted' => '#66736F',
                    'rule'  => '#E2E8E5',
                ],
            ],
            [
                'key'         => 'modern',
                'name'        => 'Modern',
                'description' => 'Teal segar — bersih, teknologi, dan kontemporer.',
                'palette' => [
                    'primary'      => '#0F766E',
                    'secondary'    => '#0D9488',
                    'accent'       => '#14B8A6',
                    'sidebar'      => '#134E4A',
                    'sidebar_text' => '#CCFBF1',
                    'success'      => '#15803D',
                    'warning'      => '#D97706',
                    'danger'       => '#DC2626',
                ],
                'fonts' => [
                    'body'    => self::FONT,
                    'display' => self::FONT,
                    'url'     => self::FONT_URL,
                ],
                'radius' => ['sm' => '8px', 'md' => '12px', 'lg' => '16px'],
                'surface' => [
                    'paper' => '#F8FAF9',
                    'ink'   => '#17201E',
                    'muted' => '#66736F',
                    'rule'  => '#E2E8E5',
                ],
            ],
            [
                'key'         => 'minimal',
                'name'        => 'Minimalis',
                'description' => 'Netral + teal — sederhana, fokus tipografi, banyak whitespace.',
                'palette' => [
                    'primary'      => '#0F766E',
                    'secondary'    => '#134E4A',
                    'accent'       => '#64748B',
                    'sidebar'      => '#17201E',
                    'sidebar_text' => '#F1F5F4',
                    'success'      => '#15803D',
                    'warning'      => '#D97706',
                    'danger'       => '#DC2626',
                ],
                'fonts' => [
                    'body'    => self::FONT,
                    'display' => self::FONT,
                    'url'     => self::FONT_URL,
                ],
                'radius' => ['sm' => '4px', 'md' => '8px', 'lg' => '12px'],
                'surface' => [
                    'paper' => '#FFFFFF',
                    'ink'   => '#17201E',
                    'muted' => '#66736F',
                    'rule'  => '#E2E8E5',
                ],
            ],
            [
                'key'         => 'academic',
                'name'        => 'Akademik',
                'description' => 'Teal + emas hangat — edukatif, ramah, cocok untuk sekolah & pesantren.',
                'palette' => [
                    'primary'      => '#0F766E',
                    'secondary'    => '#047857',
                    'accent'       => '#F59E0B',
                    'sidebar'      => '#134E4A',
                    'sidebar_text' => '#CCFBF1',
                    'success'      => '#15803D',
                    'warning'      => '#D97706',
                    'danger'       => '#DC2626',
                ],
                'fonts' => [
                    'body'    => self::FONT,
                    'display' => self::FONT,
                    'url'     => self::FONT_URL,
                ],
                'radius' => ['sm' => '8px', 'md' => '12px', 'lg' => '16px'],
                'surface' => [
                    'paper' => '#F0FDFA',
                    'ink'   => '#134E4A',
                    'muted' => '#5F7161',
                    'rule'  => '#D1E5DE',
                ],
            ],
        ];
    }
}
