<?php

namespace App\Helpers;

class NumberToWords
{
    private static array $units = [
        '', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh',
        'sebelas',
    ];

    public static function toIndonesian(int $n): string
    {
        if ($n < 0) return 'minus ' . self::toIndonesian(abs($n));
        if ($n < 12) return self::$units[$n];
        if ($n < 20) return self::toIndonesian($n - 10) . ' belas';
        if ($n < 100) {
            return self::toIndonesian(intdiv($n, 10)) . ' puluh' .
                   ($n % 10 ? ' ' . self::toIndonesian($n % 10) : '');
        }
        if ($n < 200) return 'seratus' . ($n - 100 ? ' ' . self::toIndonesian($n - 100) : '');
        if ($n < 1000) {
            return self::toIndonesian(intdiv($n, 100)) . ' ratus' .
                   ($n % 100 ? ' ' . self::toIndonesian($n % 100) : '');
        }
        if ($n < 2000) return 'seribu' . ($n - 1000 ? ' ' . self::toIndonesian($n - 1000) : '');
        if ($n < 1_000_000) {
            return self::toIndonesian(intdiv($n, 1000)) . ' ribu' .
                   ($n % 1000 ? ' ' . self::toIndonesian($n % 1000) : '');
        }
        if ($n < 1_000_000_000) {
            return self::toIndonesian(intdiv($n, 1_000_000)) . ' juta' .
                   ($n % 1_000_000 ? ' ' . self::toIndonesian($n % 1_000_000) : '');
        }
        if ($n < 1_000_000_000_000) {
            return self::toIndonesian(intdiv($n, 1_000_000_000)) . ' miliar' .
                   ($n % 1_000_000_000 ? ' ' . self::toIndonesian($n % 1_000_000_000) : '');
        }
        return (string) $n;
    }
}
