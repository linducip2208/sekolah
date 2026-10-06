<?php

use App\Models\School;
use App\Models\User;
use App\Services\Security\TotpService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->school = School::factory()->create(['settings' => []]);
    $this->totp = new TotpService();
});

function make2faUser($school, $totp): array
{
    $secret = $totp->generateSecret();
    $codes = $totp->generateRecoveryCodes(8);
    $user = User::factory()->create([
        'school_id' => $school->id,
        'is_active' => true,
        'password' => Hash::make('Secret123!'),
        'two_factor_enabled' => true,
        'two_factor_secret' => Crypt::encryptString($secret),
        'two_factor_recovery_codes' => $totp->encryptRecoveryCodes($codes),
    ]);

    return [$user, $codes[0]];
}

test('api login with 2fa user returns challenge instead of token', function () {
    [$user] = make2faUser($this->school, $this->totp);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'Secret123!',
        'device_name' => 'mobile',
    ]);

    $response->assertStatus(202)
        ->assertJsonPath('two_factor_required', true)
        ->assertJsonStructure(['challenge_id']);
    expect($response->json('token', null))->toBeNull();
});

test('api 2fa verify with recovery code mints token', function () {
    [$user, $recovery] = make2faUser($this->school, $this->totp);

    $login = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'Secret123!',
        'device_name' => 'mobile',
    ]);
    $challenge = $login->json('challenge_id');

    $verify = $this->postJson('/api/v1/auth/2fa/verify', [
        'challenge_id' => $challenge,
        'recovery_code' => $recovery,
        'device_name' => 'mobile',
    ]);

    $verify->assertOk()->assertJsonStructure(['token', 'user']);
});

test('api 2fa verify with bad challenge fails', function () {
    [$user] = make2faUser($this->school, $this->totp);

    $this->postJson('/api/v1/auth/2fa/verify', [
        'challenge_id' => 'nope',
        'two_factor_code' => '123456',
    ])->assertStatus(401);
});
