<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('school-admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $creds = $request->validate(['email' => 'required|email', 'password' => 'required']);
        $user  = User::where('email', $creds['email'])->first();

        if (!$user || !Hash::check($creds['password'], $user->password)) {
            return back()->withErrors(['email' => 'Email atau kata sandi salah.'])->withInput($request->only('email'));
        }

        // 2FA challenge before completing login.
        if ($user->two_factor_enabled && $user->two_factor_confirmed_at) {
            $request->session()->put('2fa.pending_user_id', $user->id);
            return redirect()->route('2fa.challenge');
        }

        auth()->login($user, $request->boolean('remember'));

        if ($user->hasRole('super_admin')) {
            $request->session()->regenerate();
            return redirect()->route('super.dashboard')
                ->with('success', 'Anda login sebagai Super Admin — diarahkan ke panel platform.');
        }

        if ($user->hasAnyRole(['admin', 'accountant'])) {
            $request->session()->regenerate();
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('parent')) {
            $request->session()->regenerate();
            return redirect()->route('portal.dashboard')
                ->with('success', 'Selamat datang di Portal Wali.');
        }

        if ($user->hasRole('student')) {
            $request->session()->regenerate();
            return redirect()->route('student.dashboard')
                ->with('success', 'Selamat datang.');
        }

        if ($user->hasRole('teacher')) {
            $request->session()->regenerate();
            return redirect()->route('teacher.dashboard')
                ->with('success', 'Selamat datang.');
        }

        $role = $user->getRoleNames()->first() ?? 'tanpa role';
        auth()->logout();
        return back()->withErrors([
            'email' => "Akun ini ber-role '{$role}'. Hubungi admin sekolah.",
        ])->withInput($request->only('email'));
    }

    public function logout(Request $request): RedirectResponse
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }

    public function dashboard(): View
    {
        return view('school-admin.dashboard');
    }

    public function showSuperLogin(): View
    {
        return view('super-admin.login');
    }

    public function superLogin(Request $request): RedirectResponse
    {
        $creds = $request->validate(['email' => 'required|email', 'password' => 'required']);
        $user  = User::where('email', $creds['email'])->first();

        if (!$user || !Hash::check($creds['password'], $user->password)) {
            return back()->withErrors(['email' => 'Kredensial tidak valid.']);
        }

        if (!$user->hasRole('super_admin')) {
            return back()->withErrors(['email' => 'Anda bukan super admin.']);
        }

        auth()->login($user);
        $request->session()->regenerate();
        return redirect()->route('super.dashboard');
    }

    public function alumniTracer(): View
    {
        return view('alumni.tracer');
    }

    public function kontak(): Response
    {
        return response('');
    }
}
