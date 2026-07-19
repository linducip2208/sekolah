<?php

namespace App\Http\Controllers\Web\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('profile.edit', [
            'user' => auth()->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $data = $request->validate([
            'name'   => 'required|string|max:200',
            'email'  => 'required|email|max:200|unique:users,email,'.$user->id,
            'phone'  => 'nullable|string|max:30',
            'locale' => 'nullable|in:id,en',
        ]);
        $user->update($data);
        return back()->with('success', 'Profil diperbarui.');
    }

    public function changePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($data['current_password'], auth()->user()->password)) {
            return back()->withErrors(['current_password' => 'Password lama salah.']);
        }

        auth()->user()->update(['password' => Hash::make($data['new_password'])]);
        return back()->with('success', 'Password berhasil diubah.');
    }
}
