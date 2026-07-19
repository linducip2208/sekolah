<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Staff;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class StaffWebController extends Controller
{
    private const ASSIGNABLE_ROLES = ['teacher', 'admin', 'accountant', 'librarian', 'counselor', 'nurse', 'receptionist'];

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $schoolId = $this->schoolId();

        $staffs = Staff::where('school_id', $schoolId)
            ->with(['user' => fn ($q) => $q->with('roles:id,name')])
            ->when($request->search, fn ($q) => $q->whereHas('user', fn ($u) =>
                $u->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%"))
                ->orWhere('employee_id', 'like', "%{$request->search}%"))
            ->when($request->department, fn ($q) => $q->where('department', $request->department))
            ->when($request->role, fn ($q) => $q->whereHas('user.roles', fn ($r) => $r->where('name', $request->role)))
            ->orderBy('department')->orderBy('id')
            ->paginate(25)
            ->withQueryString();

        $departments = Staff::where('school_id', $schoolId)->whereNotNull('department')->distinct()->pluck('department');

        return view('school-admin.staff.index', compact('staffs', 'departments'));
    }

    public function create(): View
    {
        return view('school-admin.staff.create', ['roles' => self::ASSIGNABLE_ROLES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'          => 'required|string|max:200',
            'email'         => 'required|email|max:200|unique:users,email',
            'phone'         => 'nullable|string|max:30',
            'password'      => 'required|string|min:6',
            'role'          => 'required|in:'.implode(',', self::ASSIGNABLE_ROLES),
            'employee_id'   => 'nullable|string|max:50',
            'department'    => 'nullable|string|max:100',
            'designation'   => 'nullable|string|max:100',
            'joining_date'  => 'nullable|date',
            'basic_salary'  => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name'      => $data['name'],
                'email'     => $data['email'],
                'phone'     => $data['phone'] ?? null,
                'password'  => Hash::make($data['password']),
                'school_id' => $this->schoolId(),
                'is_active' => true,
            ]);
            $user->assignRole($data['role']);

            Staff::create([
                'user_id'      => $user->id,
                'school_id'    => $this->schoolId(),
                'employee_id'  => $data['employee_id'] ?? null,
                'department'   => $data['department'] ?? null,
                'designation'  => $data['designation'] ?? null,
                'joining_date' => $data['joining_date'] ?? null,
                'basic_salary' => isset($data['basic_salary']) ? (int) ($data['basic_salary'] * 100) : null,
            ]);
        });

        return redirect()->route('admin.staff.index')->with('success', 'Staff berhasil ditambahkan.');
    }

    public function edit(Staff $staff): View
    {
        $this->authorizeOwn($staff);
        return view('school-admin.staff.edit', [
            'staff' => $staff->load('user.roles'),
            'roles' => self::ASSIGNABLE_ROLES,
        ]);
    }

    public function update(Request $request, Staff $staff): RedirectResponse
    {
        $this->authorizeOwn($staff);
        $data = $request->validate([
            'name'          => 'required|string|max:200',
            'email'         => 'required|email|max:200|unique:users,email,'.$staff->user_id,
            'phone'         => 'nullable|string|max:30',
            'role'          => 'required|in:'.implode(',', self::ASSIGNABLE_ROLES),
            'employee_id'   => 'nullable|string|max:50',
            'department'    => 'nullable|string|max:100',
            'designation'   => 'nullable|string|max:100',
            'joining_date'  => 'nullable|date',
            'basic_salary'  => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($staff, $data) {
            $staff->user->update([
                'name'  => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
            ]);
            $staff->user->syncRoles([$data['role']]);

            $staff->update([
                'employee_id'  => $data['employee_id'] ?? null,
                'department'   => $data['department'] ?? null,
                'designation'  => $data['designation'] ?? null,
                'joining_date' => $data['joining_date'] ?? null,
                'basic_salary' => isset($data['basic_salary']) ? (int) ($data['basic_salary'] * 100) : null,
            ]);
        });

        return redirect()->route('admin.staff.index')->with('success', 'Data staff diperbarui.');
    }

    public function destroy(Staff $staff): RedirectResponse
    {
        $this->authorizeOwn($staff);
        DB::transaction(function () use ($staff) {
            $staff->user?->update(['is_active' => false]);
            $staff->delete();
        });
        return back()->with('success', 'Staff dinonaktifkan.');
    }

    private function authorizeOwn(Staff $staff): void
    {
        abort_unless($staff->school_id === $this->schoolId(), 403);
    }
}
