<?php

namespace App\Services;

/**
 * Shared font presets (Bunny CDN) used by both school branding and the
 * platform landing page — one source of truth, no duplication.
 */
class FontRegistry
{
    public const PRESETS = [
        'manrope' => ['name' => 'Manrope',           'family' => "'Manrope', ui-sans-serif, system-ui, sans-serif", 'url' => 'https://fonts.bunny.net/css?family=manrope:400,500,600,700,800'],
        'inter'   => ['name' => 'Inter',             'family' => "'Inter', ui-sans-serif, system-ui, sans-serif", 'url' => 'https://fonts.bunny.net/css?family=inter:400,500,600,700,800'],
        'jakarta' => ['name' => 'Plus Jakarta Sans', 'family' => "'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif", 'url' => 'https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800'],
        'figtree' => ['name' => 'Figtree',           'family' => "'Figtree', ui-sans-serif, system-ui, sans-serif", 'url' => 'https://fonts.bunny.net/css?family=figtree:400,500,600,700,800'],
        'dm-sans' => ['name' => 'DM Sans',           'family' => "'DM Sans', ui-sans-serif, system-ui, sans-serif", 'url' => 'https://fonts.bunny.net/css?family=dm-sans:400,500,600,700,800'],
        'sora'    => ['name' => 'Sora',              'family' => "'Sora', ui-sans-serif, system-ui, sans-serif", 'url' => 'https://fonts.bunny.net/css?family=sora:400,500,600,700,800'],
    ];

    public static function keys(): array
    {
        return array_keys(self::PRESETS);
    }

    public static function get(?string $key): ?array
    {
        return self::PRESETS[$key] ?? null;
    }
}
