<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\School;
use App\Models\User;
use App\Services\Notification\EmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SchoolRegistrationController extends Controller
{
    public function __construct(private EmailService $email) {}

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'school_name'   => 'required|string|max:255',
            'subdomain'     => 'required|string|max:100|unique:schools,subdomain|alpha_dash',
            'admin_name'    => 'required|string|max:255',
            'admin_email'   => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8|confirmed',
            'phone'         => 'nullable|string|max:30',
            'address'       => 'nullable|string',
        ]);

        $trialDays = (int) config('multitenancy.trial_days', 14);
        $freePlan  = Plan::where('slug', 'free')->first();

        $result = DB::transaction(function () use ($validated, $trialDays, $freePlan) {
            $school = School::create([
                'name'            => $validated['school_name'],
                'subdomain'       => $validated['subdomain'],
                'phone'           => $validated['phone'] ?? null,
                'address'         => $validated['address'] ?? null,
                'plan_id'         => $freePlan?->id,
                'plan_expires_at' => now()->addDays($trialDays),
                'is_active'       => true,
                'settings'        => [],
            ]);

            $password = $validated['admin_password'];
            $admin    = User::create([
                'name'      => $validated['admin_name'],
                'email'     => $validated['admin_email'],
                'password'  => bcrypt($password),
                'school_id' => $school->id,
                'is_active' => true,
            ]);
            $admin->assignRole('admin');

            return ['school' => $school, 'admin' => $admin, 'password' => $password];
        });

        $this->email->sendWelcomeSchool(
            $validated['admin_email'],
            $validated['school_name'],
            $validated['subdomain'],
            $result['password']
        );

        return response()->json([
            'message'    => "Sekolah berhasil didaftarkan. Masa trial {$trialDays} hari dimulai sekarang.",
            'school'     => $result['school'],
            'login_url'  => 'https://' . $validated['subdomain'] . '.' . config('multitenancy.base_domain'),
            'expires_at' => now()->addDays($trialDays)->toDateString(),
        ], 201);
    }
}
