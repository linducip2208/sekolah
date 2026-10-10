<?php

// ROLE × MENU matrix (implementation-truth, not nav cosmetics).
// Web admin group gate: admin|accountant|principal|hr|transport_admin|
// hostel_admin|procurement_admin|homeroom_teacher. Teacher/parent/student
// are NOT in the gate and must be blocked from admin pages AND actions.

use App\Models\School;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->school = School::factory()->create();
    foreach (['admin', 'teacher', 'parent', 'student', 'accountant'] as $r) {
        Role::firstOrCreate(['name' => $r]);
    }
    $this->users = [];
    foreach (['admin', 'teacher', 'parent', 'student', 'accountant'] as $r) {
        $u = User::factory()->create(['school_id' => $this->school->id]);
        $u->assignRole($r);
        $this->users[$r] = $u;
    }
});

test('admin group gate allows gate roles, denies others on dashboard', function () {
    foreach (['admin', 'accountant'] as $r) {
        $this->actingAs($this->users[$r])
            ->get(route('admin.dashboard'))
            ->assertOk();
    }
    foreach (['teacher', 'parent', 'student'] as $r) {
        $this->actingAs($this->users[$r])
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }
});

test('admin POST actions deny non-gate roles', function () {
    foreach (['teacher', 'parent', 'student'] as $r) {
        $this->actingAs($this->users[$r])->post(route('admin.osis.programs.store'), [
            'title' => 'X',
        ])->assertForbidden();
    }
});

test('guest is redirected to login, not leaked', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
});
