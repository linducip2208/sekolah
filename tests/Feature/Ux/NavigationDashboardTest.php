<?php

use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/*
 * UI/UX foundation regression tests:
 * navigation IA, My Work hub, dashboard command center, command palette data.
 */

function uxAdminUser(): array
{
    $school = School::factory()->create(['settings' => []]);
    app()->instance('current_school', $school);

    $admin = User::factory()->create([
        'school_id' => $school->id,
        'email' => 'ux-admin@test.local',
        'password' => Hash::make('password'),
        'is_active' => true,
    ]);
    $admin->assignRole('admin');

    return [$school, $admin];
}

test('dashboard renders as command center for admin', function () {
    [$school, $admin] = uxAdminUser();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk()
        ->assertSee('Perlu Perhatian')
        ->assertSee('Aksi Cepat');
});

test('sidebar renders domain groups not module dump', function () {
    [$school, $admin] = uxAdminUser();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk()
        ->assertSee('My Work')
        ->assertSee('>Akademik<', false)
        ->assertSee('>Pembelajaran<', false)
        ->assertSee('>Kesiswaan<', false)
        ->assertSee('>PPDB<', false)
        ->assertSee('>Keuangan<', false)
        ->assertSee('>SDM<', false)
        ->assertSee('>Operasional<', false)
        ->assertSee('>Komunikasi<', false)
        ->assertSee('>Administrasi<', false)
        ->assertSee('>Analitik<', false)
        ->assertSee('>Pengaturan<', false);
});

test('teacher role does not see finance or settings groups in sidebar', function () {
    [$school, ] = uxAdminUser();

    $homeroom = User::factory()->create([
        'school_id' => $school->id,
        'is_active' => true,
    ]);
    $homeroom->assignRole('homeroom_teacher');

    $response = $this->actingAs($homeroom)->get(route('admin.dashboard'));

    $response->assertOk()
        ->assertSee('>Akademik<', false)
        ->assertDontSee('>Keuangan<', false)
        ->assertDontSee('>Pengaturan<', false)
        ->assertDontSee('>SDM<', false);
});

test('my work hub renders grouped priorities', function () {
    [$school, $admin] = uxAdminUser();

    $response = $this->actingAs($admin)->get(route('admin.my-work'));

    $response->assertOk();
});

test('my work shows overdue invoice alert with cta for admin', function () {
    [$school, $admin] = uxAdminUser();

    // Minimal academic chain untuk memenuhi FK.
    $year = \App\Models\Academic\AcademicYear::create([
        'school_id' => $school->id, 'name' => '2026/2027',
        'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true,
    ]);
    $medium = \App\Models\Academic\Medium::create(['school_id' => $school->id, 'name' => 'Indonesia']);
    $classRoom = \App\Models\Academic\ClassRoom::create(['school_id' => $school->id, 'medium_id' => $medium->id, 'name' => 'Kelas 10']);
    $section = \App\Models\Academic\Section::create(['school_id' => $school->id, 'name' => 'A']);
    $classSection = \App\Models\Academic\ClassSection::create([
        'school_id' => $school->id, 'class_room_id' => $classRoom->id, 'section_id' => $section->id,
        'medium_id' => $medium->id, 'academic_year_id' => $year->id,
    ]);
    $studentUser = User::factory()->create(['school_id' => $school->id]);
    $student = \App\Models\Academic\Student::create([
        'user_id' => $studentUser->id, 'school_id' => $school->id, 'class_section_id' => $classSection->id,
    ]);

    $structure = \App\Models\Finance\FeeStructure::create([
        'school_id' => $school->id,
        'name' => 'SPP UX',
        'frequency' => 'monthly',
        'amount' => 50000000,
        'is_active' => true,
    ]);

    \App\Models\Finance\FeeInvoice::create([
        'school_id' => $school->id,
        'student_id' => $student->id,
        'fee_structure_id' => $structure->id,
        'invoice_no' => 'INV-UX-001',
        'period' => now()->format('Y-m'),
        'amount' => 50000000,
        'paid_amount' => 0,
        'due_date' => now()->subDays(10),
        'status' => 'overdue',
    ]);

    $service = app(\App\Services\Dashboard\MyWorkService::class);
    $items = collect($service->items($school->id, $admin));
    $invoiceItem = $items->firstWhere('key', 'invoices');

    expect($invoiceItem)->not->toBeNull()
        ->and($invoiceItem['priority'])->toBe('critical')
        ->and($invoiceItem['cta'])->toContain('Reminder');
});

test('my work service is empty when nothing pending', function () {
    [$school, $admin] = uxAdminUser();

    $total = app(\App\Services\Dashboard\MyWorkService::class)->totalCount($school->id, $admin);

    expect($total)->toBeInt()->toBe(0);
});

test('navigation service filters dead routes and maps breadcrumbs', function () {
    [$school, $admin] = uxAdminUser();

    $svc = app(\App\Services\Navigation\NavigationService::class);
    $groups = $svc->groupsFor($admin);

    // Semua item harus punya URL valid & label.
    foreach ($groups as $group) {
        foreach ($group['items'] as $item) {
            expect($item['url'])->toStartWith(url('/'))
                ->and($item['label'])->not->toBe('');
        }
    }

    expect($svc->groupForRoute('admin.students.index'))->toBe('Akademik')
        ->and($svc->groupForRoute('admin.branding.show'))->toBe('Pengaturan')
        ->and($svc->groupForRoute('admin.notices.index'))->toBe('Komunikasi');
});

test('command palette receives role-aware navigation payload', function () {
    [$school, $admin] = uxAdminUser();

    $nav = app(\App\Services\Navigation\NavigationService::class)->flatForPalette($admin);

    expect(count($nav))->toBeGreaterThan(50);

    foreach ($nav as $entry) {
        expect($entry)->toHaveKeys(['title', 'group', 'icon', 'url']);
    }
});

test('dashboard data service returns complete structure and caches', function () {
    [$school, $admin] = uxAdminUser();

    $svc = app(\App\Services\Dashboard\DashboardDataService::class);
    $data = $svc->for($admin);

    expect($data)->toHaveKeys(['context', 'kpis', 'alerts', 'charts', 'lists'])
        ->and(count($data['kpis']))->toBeLessThanOrEqual(6)
        ->and($data['context'])->toHaveKey('greeting');

    // Second call served from cache (same instance).
    expect($svc->for($admin))->toBe($data);
});

test('key module pages render with new navigation shell', function () {
    [$school, $admin] = uxAdminUser();

    $routes = [
        'admin.students.index',
        'admin.fee.invoices.index',
        'admin.staff.index',
        'admin.academic.years.index',
        'admin.notices.index',
    ];

    foreach ($routes as $name) {
        $this->actingAs($admin)->get(route($name))->assertOk();
    }
});
