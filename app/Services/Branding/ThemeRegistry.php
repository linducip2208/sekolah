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
    public const DEFAULT = 'corporate';

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
                'key'         => 'elegant',
                'name'        => 'Classic',
                'description' => 'Teal institusional yang dalam — elegan, tenang, dan terpercaya.',
                'palette' => [
                    'primary'      => '#1E40AF',
                    'secondary'    => '#1D4ED8',
                    'accent'       => '#F59E0B',
                    'sidebar'      => '#1E3A8A',
                    'sidebar_text' => '#F1F5F9',
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
                    'paper' => '#F8FAFC',
                    'ink'   => '#0F172A',
                    'muted' => '#64748B',
                    'rule'  => '#E2E8F0',
                ],
            ],
            [
                'key'         => 'corporate',
                'name'        => 'Korporat',
                'description' => 'Biru + slate — enterprise, terstruktur, dan profesional.',
                'palette' => [
                    'primary'      => '#2563EB',
                    'secondary'    => '#1D4ED8',
                    'accent'       => '#475569',
                    'sidebar'      => '#1E3A8A',
                    'sidebar_text' => '#F1F5F9',
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
                    'paper' => '#F8FAFC',
                    'ink'   => '#0F172A',
                    'muted' => '#64748B',
                    'rule'  => '#E2E8F0',
                ],
            ],
            [
                'key'         => 'modern',
                'name'        => 'Modern',
                'description' => 'Biru segar — bersih, teknologi, dan kontemporer.',
                'palette' => [
                    'primary'      => '#2563EB',
                    'secondary'    => '#1D4ED8',
                    'accent'       => '#3B82F6',
                    'sidebar'      => '#1E3A8A',
                    'sidebar_text' => '#DBEAFE',
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
                    'paper' => '#F8FAFC',
                    'ink'   => '#0F172A',
                    'muted' => '#64748B',
                    'rule'  => '#E2E8F0',
                ],
            ],
            [
                'key'         => 'minimal',
                'name'        => 'Minimalis',
                'description' => 'Netral + biru — sederhana, fokus tipografi, banyak whitespace.',
                'palette' => [
                    'primary'      => '#2563EB',
                    'secondary'    => '#1D4ED8',
                    'accent'       => '#64748B',
                    'sidebar'      => '#0F172A',
                    'sidebar_text' => '#F1F5F9',
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
                    'ink'   => '#0F172A',
                    'muted' => '#64748B',
                    'rule'  => '#E2E8F0',
                ],
            ],
            [
                'key'         => 'academic',
                'name'        => 'Akademik',
                'description' => 'Biru + emas hangat — edukatif, ramah, cocok untuk sekolah & pesantren.',
                'palette' => [
                    'primary'      => '#2563EB',
                    'secondary'    => '#1E40AF',
                    'accent'       => '#F59E0B',
                    'sidebar'      => '#1E3A8A',
                    'sidebar_text' => '#DBEAFE',
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
                    'paper' => '#EFF6FF',
                    'ink'   => '#1E3A8A',
                    'muted' => '#64748B',
                    'rule'  => '#DBEAFE',
                ],
            ],
        ];
    }
}
