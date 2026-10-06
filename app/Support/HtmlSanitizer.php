<?php

namespace App\Support;

/**
 * Dependency-free sanitizer for admin-authored HTML rendered unescaped
 * in Blade ({!! !!}). Allows basic formatting; strips scripts, event
 * handlers, javascript:/data: URLs and dangerous tags.
 */
final class HtmlSanitizer
{
    private const array ALLOWED_TAGS = [
        'p', 'br', 'b', 'strong', 'i', 'em', 'u', 's', 'blockquote',
        'ul', 'ol', 'li', 'h2', 'h3', 'h4', 'a', 'img', 'figure',
        'figcaption', 'hr', 'pre', 'code', 'table', 'thead', 'tbody',
        'tr', 'th', 'td', 'span', 'div',
    ];

    public static function clean(?string $html): ?string
    {
        if ($html === null || $html === '') {
            return $html;
        }

        $html = strip_tags($html, '<'.implode('><', self::ALLOWED_TAGS).'>');

        // Remove event-handler attributes (onclick=, onerror=, ...).
        $html = (string) preg_replace('/\s+on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);

        // Neutralize javascript:/data:/vbscript: URLs in href/src.
        $html = (string) preg_replace_callback(
            '/(href|src)\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s>]+))/i',
            static function (array $m): string {
                $url = $m[3] ?? $m[4] ?? $m[5] ?? '';
                $lower = strtolower(ltrim(trim($url)));
                if (str_starts_with($lower, 'javascript:') || str_starts_with($lower, 'vbscript:') || str_starts_with($lower, 'data:')) {
                    return $m[1].'="#"';
                }

                return $m[0];
            },
            $html
        );

        return $html;
    }
}
