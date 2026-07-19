<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Security\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\RateLimiter;

class TwoFactorController extends Controller
{
    public function __construct(private TotpService $totp) {}

    public function showEnable(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('admin.login');
        }

        $secret = $request->session()->get('2fa.pending_secret');
        if (!$secret || $user->two_factor_enabled) {
            $secret = $this->totp->generateSecret();
            $request->session()->put('2fa.pending_secret', $secret);
        }

        $issuer = config('app.name', 'eSchool');
        $uri    = $this->totp->getOtpAuthUri($issuer, $user->email, $secret);
        $qrUrl  = $this->totp->getQrCodeUrl($uri);

        return view('auth.2fa.enable', [
            'secret'      => $secret,
            'qrUrl'       => $qrUrl,
            'enabled'     => (bool) $user->two_factor_enabled,
            'recoveryCnt' => count($this->totp->decryptRecoveryCodes($user->two_factor_recovery_codes)),
        ]);
    }

    public function confirm(Request $request)
    {
        $data = $request->validate(['code' => 'required|string|size:6']);
        $user = $request->user();
        $secret = $request->session()->get('2fa.pending_secret');

        if (!$secret) {
            return back()->withErrors(['code' => 'Sesi enable 2FA tidak ditemukan. Coba lagi.']);
        }

        if (!$this->totp->verify($secret, $data['code'])) {
            return back()->withErrors(['code' => 'Kode tidak valid. Pastikan jam perangkat sinkron.']);
        }

        $recovery = $this->totp->generateRecoveryCodes();

        $user->two_factor_secret         = Crypt::encryptString($secret);
        $user->two_factor_recovery_codes = $this->totp->encryptRecoveryCodes($recovery);
        $user->two_factor_confirmed_at   = now();
        $user->two_factor_enabled        = true;
        $user->save();

        $request->session()->forget('2fa.pending_secret');
        $request->session()->put('2fa.passed', true);

        return view('auth.2fa.recovery-codes', ['codes' => $recovery]);
    }

    public function disable(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);
        $user = $request->user();

        if (!$user->two_factor_enabled || !$user->two_factor_secret) {
            return back()->withErrors(['code' => '2FA tidak aktif.']);
        }

        $secret = Crypt::decryptString($user->two_factor_secret);
        if (!$this->totp->verify($secret, $request->code)) {
            return back()->withErrors(['code' => 'Kode tidak valid.']);
        }

        $user->two_factor_secret         = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at   = null;
        $user->two_factor_enabled        = false;
        $user->save();

        return redirect()->route('2fa.enable')->with('success', '2FA telah dinonaktifkan.');
    }

    public function regenerateRecovery(Request $request)
    {
        $user = $request->user();
        if (!$user->two_factor_enabled) {
            return back()->withErrors(['code' => '2FA tidak aktif.']);
        }
        $codes = $this->totp->generateRecoveryCodes();
        $user->two_factor_recovery_codes = $this->totp->encryptRecoveryCodes($codes);
        $user->save();
        return view('auth.2fa.recovery-codes', ['codes' => $codes]);
    }

    public function showChallenge(Request $request)
    {
        if (!$request->session()->has('2fa.pending_user_id')) {
            return redirect()->route('admin.login');
        }
        return view('auth.2fa.challenge');
    }

    public function verifyChallenge(Request $request)
    {
        $data = $request->validate([
            'code'     => 'nullable|string',
            'recovery' => 'nullable|string',
        ]);

        $userId = $request->session()->get('2fa.pending_user_id');
        if (!$userId) {
            return redirect()->route('admin.login');
        }

        $throttleKey = '2fa:' . $userId . ':' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return back()->withErrors(['code' => 'Terlalu banyak percobaan. Tunggu beberapa menit.']);
        }

        $user = User::find($userId);
        if (!$user || !$user->two_factor_enabled || !$user->two_factor_secret) {
            $request->session()->forget('2fa.pending_user_id');
            return redirect()->route('admin.login');
        }

        $passed = false;

        if (!empty($data['code'])) {
            try {
                $secret = Crypt::decryptString($user->two_factor_secret);
                $passed = $this->totp->verify($secret, $data['code']);
            } catch (\Throwable) {
                $passed = false;
            }
        } elseif (!empty($data['recovery'])) {
            $passed = $this->totp->consumeRecoveryCode($user, $data['recovery']);
        }

        if (!$passed) {
            RateLimiter::hit($throttleKey, 300);
            return back()->withErrors(['code' => 'Kode atau recovery tidak valid.']);
        }

        RateLimiter::clear($throttleKey);
        Auth::loginUsingId($user->id, $request->boolean('remember'));
        $request->session()->forget('2fa.pending_user_id');
        $request->session()->put('2fa.passed', true);
        $request->session()->regenerate();

        return $this->redirectToRoleDashboard($user);
    }

    private function redirectToRoleDashboard(User $user)
    {
        if ($user->hasRole('super_admin')) {
            return redirect()->route('super.dashboard');
        }
        if ($user->hasAnyRole(['admin', 'accountant'])) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->hasRole('teacher')) {
            return redirect()->route('teacher.dashboard');
        }
        if ($user->hasRole('student')) {
            return redirect()->route('student.dashboard');
        }
        if ($user->hasRole('parent')) {
            return redirect()->route('portal.dashboard');
        }
        return redirect('/');
    }
}
