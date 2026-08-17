<?php

use App\Models\AccreditationActionPlan;
use App\Models\AccreditationInstrument;
use App\Models\AccreditationStandard;
use App\Models\School;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->school = School::factory()->create();
    $this->admin  = User::factory()->create(['school_id' => $this->school->id]);
    Role::firstOrCreate(['name' => 'admin']);
    $this->admin->assignRole('admin');

    $this->standard = AccreditationStandard::create([
        'code' => '1', 'name' => 'Mutu Lulusan', 'max_score' => 100, 'weight_percent' => 35.00,
    ]);
    $this->instrument = AccreditationInstrument::create([
        'accreditation_standard_id' => $this->standard->id,
        'number' => '1.1', 'description' => 'Siswa disiplin', 'max_score' => 4,
    ]);
});

it('lists and creates an accreditation action plan', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.accreditation.action-plans'))
        ->assertOk()
        ->assertSee('Rencana Perbaikan Akreditasi');

    $this->actingAs($this->admin)
        ->post(route('admin.accreditation.action-plans.store'), [
            'title' => 'Perbaiki kedisiplinan',
            'action' => 'Sosialisasi tata tertib dan penegakan aturan.',
            'accreditation_standard_id' => $this->standard->id,
            'accreditation_instrument_id' => $this->instrument->id,
            'responsible_id' => $this->admin->id,
            'due_date' => '2026-12-31',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('accreditation_action_plans', [
        'school_id' => $this->school->id,
        'title' => 'Perbaiki kedisiplinan',
        'status' => 'pending',
    ]);
});

it('updates action plan status through workflow', function () {
    $plan = AccreditationActionPlan::create([
        'school_id' => $this->school->id,
        'title' => 'Plan A',
        'action' => 'Lakukan perbaikan.',
        'status' => 'pending',
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.accreditation.action-plans.status', $plan), ['status' => 'in_progress'])
        ->assertRedirect();

    expect($plan->fresh()->status)->toBe('in_progress');

    $this->actingAs($this->admin)
        ->post(route('admin.accreditation.action-plans.status', $plan), ['status' => 'completed'])
        ->assertRedirect();

    expect($plan->fresh()->status)->toBe('completed');
});
