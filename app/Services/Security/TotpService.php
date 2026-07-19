<?php

namespace App\Services\Security;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class TotpService
{
    private const PERIOD = 30;
    private const DIGITS = 6;
    private const ALGORITHM = 'sha1';
    private const WINDOW = 1;

    public function generateSecret(int $bytes = 20): string
    {
        return $this->base32Encode(random_bytes($bytes));
    }

    public function getOtpAuthUri(string $issuer, string $accountName, string $secret): string
    {
        $label = rawurlencode($issuer) . ':' . rawurlencode($accountName);
        $params = http_build_query([
            'secret'    => $secret,
            'issuer'    => $issuer,
            'algorithm' => strtoupper(self::ALGORITHM),
            'digits'    => self::DIGITS,
            'period'    => self::PERIOD,
        ]);
        return "otpauth://totp/{$label}?{$params}";
    }

    public function getQrCodeUrl(string $otpAuthUri, int $size = 220): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . '&data=' . urlencode($otpAuthUri);
    }

    public function verify(string $secret, string $code, int $window = self::WINDOW): bool
    {
        $code = preg_replace('/\s+/', '', $code);
        if (!preg_match('/^\d{6}$/', $code)) {
            return false;
        }
        $now = intdiv(time(), self::PERIOD);
        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals($this->generateCode($secret, $now + $i), $code)) {
                return true;
            }
        }
        return false;
    }

    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(Str::random(5) . '-' . Str::random(5));
        }
        return $codes;
    }

    public function encryptRecoveryCodes(array $codes): string
    {
        return Crypt::encryptString(json_encode($codes));
    }

    public function decryptRecoveryCodes(?string $encrypted): array
    {
        if (!$encrypted) {
            return [];
        }
        try {
            return json_decode(Crypt::decryptString($encrypted), true) ?? [];
        } catch (\Throwable) {
            return [];
        }
    }

    public function consumeRecoveryCode(User $user, string $code): bool
    {
        $codes = $this->decryptRecoveryCodes($user->two_factor_recovery_codes);
        $normalized = strtoupper(trim($code));
        $index = array_search($normalized, array_map('strtoupper', $codes), true);
        if ($index === false) {
            return false;
        }
        unset($codes[$index]);
        $user->two_factor_recovery_codes = $this->encryptRecoveryCodes(array_values($codes));
        $user->save();
        return true;
    }

    private function generateCode(string $secret, int $counter): string
    {
        $binSecret = $this->base32Decode($secret);
        $binCounter = pack('N*', 0) . pack('N*', $counter);
        $hash = hash_hmac(self::ALGORITHM, $binCounter, $binSecret, true);
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $code = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        ) % (10 ** self::DIGITS);
        return str_pad((string) $code, self::DIGITS, '0', STR_PAD_LEFT);
    }

    private function base32Encode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binary = '';
        foreach (str_split($data) as $char) {
            $binary .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }
        $binary = str_pad($binary, (int) (ceil(strlen($binary) / 5) * 5), '0', STR_PAD_RIGHT);
        $result = '';
        foreach (str_split($binary, 5) as $chunk) {
            $result .= $alphabet[bindec($chunk)];
        }
        return $result;
    }

    private function base32Decode(string $encoded): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $encoded = strtoupper(preg_replace('/[^A-Z2-7]/', '', $encoded));
        $binary = '';
        foreach (str_split($encoded) as $char) {
            $pos = strpos($alphabet, $char);
            if ($pos === false) {
                continue;
            }
            $binary .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }
        $result = '';
        foreach (str_split($binary, 8) as $byte) {
            if (strlen($byte) === 8) {
                $result .= chr(bindec($byte));
            }
        }
        return $result;
    }
}
