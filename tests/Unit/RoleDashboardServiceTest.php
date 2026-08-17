<?php

use App\Models\School;
use App\Models\User;
use App\Services\Dashboard\RoleDashboardService;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->service = app(RoleDashboardService::class);
    $this->school = School::factory()->create();
    $this->user = User::factory()->create(['school_id' => $this->school->id]);
});

it('maps user roles to dashboard roles', function () {
    $roles = [
        'admin' => 'principal', 'super_admin' => 'principal',
        'accountant' => 'finance', 'counselor' => 'counselor',
        'hr' => 'hr', 'teacher' => 'teacher', 'homeroom_teacher' => 'teacher',
    ];

    foreach ($roles as $spatieRole => $expected) {
        Role::firstOrCreate(['name' => $spatieRole]);
        $user = User::factory()->create(['school_id' => $this->school->id]);
        $user->assignRole($spatieRole);

        expect($this->service->roleFor($user))->toBe($expected);
    }
});

it('returns principal KPIs for an admin', function () {
    $data = $this->service->forRole($this->school->id, $this->user->id, 'principal');

    expect($data['role'])->toBe('principal');
    expect($data['kpis'])->toHaveCount(6);
    expect($data['kpis'][0]['label'])->toBe('Siswa Aktif');
});

it('returns finance KPIs for an accountant', function () {
    $data = $this->service->forRole($this->school->id, $this->user->id, 'finance');

    expect($data['kpis'])->toHaveCount(5);
    expect($data['kpis'][0]['label'])->toBe('Pendapatan Bulan Ini');
});
