<?php

use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Role;

it('creates the expanded enterprise roles with permissions', function () {
    $this->seed(RolePermissionSeeder::class);

    $principal = Role::findByName('principal', 'web');
    expect($principal->hasPermissionTo('analytics.view'))->toBeTrue();
    expect($principal->hasPermissionTo('report.view'))->toBeTrue();

    $hr = Role::findByName('hr', 'web');
    expect($hr->hasPermissionTo('payroll.manage'))->toBeTrue();

    expect(Role::findByName('transport_admin', 'web')->hasPermissionTo('transport.manage'))->toBeTrue();
    expect(Role::findByName('hostel_admin', 'web')->hasPermissionTo('hostel.manage'))->toBeTrue();
    expect(Role::findByName('procurement_admin', 'web')->hasPermissionTo('inventory.manage'))->toBeTrue();
    expect(Role::findByName('homeroom_teacher', 'web')->hasPermissionTo('attendance.manage'))->toBeTrue();
    expect(Role::findByName('driver', 'web')->hasPermissionTo('gate.scan'))->toBeTrue();
});
