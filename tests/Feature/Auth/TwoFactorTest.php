<?php

use App\Models\School;
use App\Models\User;
use App\Services\Security\TotpService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->totp = app(TotpService::class);
});

test('user can enable 2fa via web flow', function () {
    $school = School::factory()->create();
    $user = User::factory()->create([
        'school_id' => $school->id,
        'password'  => Hash::make('password'),
        'is_active' => true,
    ]);
    $user->assignRole('admin');

    $this->actingAs($user);

    $response = $this->get('/2fa/enable');
    $response->assertOk();

    $secret = session('2fa.pending_secret');
    expect($secret)->not->toBeEmpty();

    $code = generateValidTotp($secret);

    $this->post('/2fa/enable', ['code' => $code])
        ->assertOk();

    $user->refresh();
    expect($user->two_factor_enabled)->toBeTrue();
    expect($user->two_factor_secret)->not->toBeNull();
});

test('login redirects to 2fa challenge when enabled', function () {
    $school = School::factory()->create();
    $secret = app(TotpService::class)->generateSecret();
    $user = User::factory()->create([
        'school_id'                  => $school->id,
        'email'                      => 'two@factor.com',
        'password'                   => Hash::make('password'),
        'is_active'                  => true,
        'two_factor_enabled'         => true,
        'two_factor_secret'          => Crypt::encryptString($secret),
        'two_factor_confirmed_at'    => now(),
    ]);
    $user->assignRole('admin');

    $this->post('/admin/login', [
        'email'    => 'two@factor.com',
        'password' => 'password',
    ])->assertRedirect('/2fa/challenge');

    expect(session('2fa.pending_user_id'))->toBe($user->id);
});

test('2fa challenge accepts valid code', function () {
    $school = School::factory()->create();
    $secret = app(TotpService::class)->generateSecret();
    $user = User::factory()->create([
        'school_id'              => $school->id,
        'password'               => Hash::make('password'),
        'is_active'              => true,
        'two_factor_enabled'     => true,
        'two_factor_secret'      => Crypt::encryptString($secret),
        'two_factor_confirmed_at'=> now(),
    ]);
    $user->assignRole('admin');

    $code = generateValidTotp($secret);

    $this->withSession(['2fa.pending_user_id' => $user->id])
        ->post('/2fa/challenge', ['code' => $code])
        ->assertRedirect();

    expect(auth()->id())->toBe($user->id);
});

test('2fa challenge rejects invalid code', function () {
    $school = School::factory()->create();
    $secret = app(TotpService::class)->generateSecret();
    $user = User::factory()->create([
        'school_id'              => $school->id,
        'is_active'              => true,
        'two_factor_enabled'     => true,
        'two_factor_secret'      => Crypt::encryptString($secret),
    ]);
    $user->assignRole('admin');

    $this->withSession(['2fa.pending_user_id' => $user->id])
        ->from('/2fa/challenge')
        ->post('/2fa/challenge', ['code' => '000000'])
        ->assertRedirect('/2fa/challenge')
        ->assertSessionHasErrors('code');

    expect(auth()->check())->toBeFalse();
});

function generateValidTotp(string $secret): string
{
    $period = 30;
    $counter = intdiv(time(), $period);

    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $clean = strtoupper(preg_replace('/[^A-Z2-7]/', '', $secret));
    $binary = '';
    foreach (str_split($clean) as $c) {
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
    return str_pad((string) $code, 6, '0', STR_PAD_LEFT);
}
