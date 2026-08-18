<?php

namespace App\Http\Controllers\Web\Admin\Foundation;

use App\Http\Controllers\Controller;
use App\Models\Foundation\Foundation;
use App\Models\Foundation\FoundationUserManagement;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FoundationUserController extends Controller
{
    private function getFoundation(): Foundation
    {
        $schoolId = auth()->user()->school_id;
        $school = School::findOrFail($schoolId);
        abort_unless($school->foundation_id, 404, 'Sekolah ini tidak terafiliasi dengan yayasan.');
        return Foundation::with('schools')->findOrFail($school->foundation_id);
    }

    public function index(): View
    {
        $foundation = $this->getFoundation();

        $assignments = FoundationUserManagement::where('foundation_id', $foundation->id)
            ->with('user:id,name,email')
            ->get();

        $users = User::where('school_id', auth()->user()->school_id)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('school-admin.foundation.user-management', [
            'foundation'  => $foundation,
            'assignments' => $assignments,
            'users'       => $users,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $foundation = $this->getFoundation();

        $data = $request->validate([
            'user_id'          => 'required|exists:users,id',
            'role'             => 'required|string|max:50',
            'assigned_schools' => 'nullable|array',
        ]);

        FoundationUserManagement::updateOrCreate(
            ['foundation_id' => $foundation->id, 'user_id' => $data['user_id']],
            [
                'role'             => $data['role'],
                'assigned_schools' => $data['assigned_schools'] ?? null,
            ]
        );

        return back()->with('success', 'User yayasan diperbarui.');
    }

    public function destroy(FoundationUserManagement $assignment): RedirectResponse
    {
        $foundation = $this->getFoundation();
        abort_unless($assignment->foundation_id === $foundation->id, 403);

        $assignment->delete();
        return back()->with('success', 'User dihapus dari yayasan.');
    }
}
