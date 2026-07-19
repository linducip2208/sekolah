<?php

namespace App\Services;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Security\TotpService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthService
{
    public function __construct(private ?TotpService $totp = null) {}

    public function login(string $email, string $password, string $deviceName, ?string $code = null, ?string $recovery = null): array|null
    {
        $user = User::where('email', $email)
            ->where('is_active', true)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        $user->tokens()->where('name', $deviceName)->delete();
        $token = $user->createToken($deviceName)->plainTextToken;

        return [
            'token' => $token,
            'user'  => new UserResource($user->load('school', 'roles', 'permissions')),
        ];
    }

    public function verifyTwoFactor(string $challengeId, ?string $code, ?string $recovery, string $deviceName): ?array
    {
        $userId = Cache::pull("2fa:api:{$challengeId}");
        if (!$userId) {
            return null;
        }
        $user = User::find($userId);
        if (!$user || !$user->two_factor_enabled || !$user->two_factor_secret) {
            return null;
        }
        $totp = $this->totp ?? app(TotpService::class);
        $passed = false;
        if ($code) {
            try {
                $secret = Crypt::decryptString($user->two_factor_secret);
                $passed = $totp->verify($secret, $code);
            } catch (\Throwable) {}
        } elseif ($recovery) {
            $passed = $totp->consumeRecoveryCode($user, $recovery);
        }
        if (!$passed) {
            return null;
        }
        $user->tokens()->where('name', $deviceName)->delete();
        $token = $user->createToken($deviceName)->plainTextToken;
        return [
            'token' => $token,
            'user'  => new UserResource($user->load('school', 'roles', 'permissions')),
        ];
    }

    public function sendPasswordResetLink(string $email): string
    {
        return Password::sendResetLink(['email' => $email]);
    }

    public function resetPassword(array $data): string
    {
        return Password::reset($data, function (User $user, string $password) {
            $user->forceFill(['password' => Hash::make($password)])->save();
            $user->tokens()->delete();
        });
    }
}
