<?php

use App\Services\Security\TotpService;

beforeEach(fn() => $this->totp = new TotpService());

test('generated secret is base32 of reasonable length', function () {
    $s = $this->totp->generateSecret();
    expect($s)->toMatch('/^[A-Z2-7]{32}$/');
});

test('verify accepts code from generated secret', function () {
    $secret = $this->totp->generateSecret();
    $period = 30;
    $counter = intdiv(time(), $period);

    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $binary = '';
    foreach (str_split($secret) as $c) {
        $binary .= str_pad(decbin(strpos($alphabet, $c)), 5, '0', STR_PAD_LEFT);
    }
    $bin = '';
    foreach (str_split($binary, 8) as $byte) {
        if (strlen($byte) === 8) $bin .= chr(bindec($byte));
    }
    $binCounter = pack('N*', 0) . pack('N*', $counter);
    $hash = hash_hmac('sha1', $binCounter, $bin, true);
    $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
    $code = (
        ((ord($hash[$offset]) & 0x7F) << 24) |
        ((ord($hash[$offset + 1]) & 0xFF) << 16) |
        ((ord($hash[$offset + 2]) & 0xFF) << 8) |
        (ord($hash[$offset + 3]) & 0xFF)
    ) % 1_000_000;
    $codeStr = str_pad((string) $code, 6, '0', STR_PAD_LEFT);

    expect($this->totp->verify($secret, $codeStr))->toBeTrue();
});

test('verify rejects bad code', function () {
    $secret = $this->totp->generateSecret();
    expect($this->totp->verify($secret, '000000'))->toBeFalse();
    expect($this->totp->verify($secret, 'badcode'))->toBeFalse();
});

test('recovery codes encrypt and decrypt round trip', function () {
    $codes = $this->totp->generateRecoveryCodes(8);
    expect($codes)->toHaveCount(8);
    $enc = $this->totp->encryptRecoveryCodes($codes);
    expect($this->totp->decryptRecoveryCodes($enc))->toEqual($codes);
});

test('otpauth uri contains issuer and secret', function () {
    $uri = $this->totp->getOtpAuthUri('Foo', 'a@b.c', 'AAAA');
    expect($uri)->toContain('otpauth://totp/Foo:a%40b.c')
        ->and($uri)->toContain('secret=AAAA')
        ->and($uri)->toContain('issuer=Foo');
});
