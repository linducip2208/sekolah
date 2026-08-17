<?php

use App\Models\Audit\AuditFinding;
use App\Models\Audit\InternalAudit;
use App\Models\School;
use App\Services\Audit\InternalAuditService;

beforeEach(function () {
    $this->service = app(InternalAuditService::class);
    $this->school = School::factory()->create();
    $this->audit = InternalAudit::create([
        'school_id' => $this->school->id, 'title' => 'Audit Semester 1', 'status' => 'planned',
    ]);
});

it('starts an audit', function () {
    $audit = $this->service->start($this->audit);

    expect($audit->status)->toBe('in_progress');
    expect($audit->started_at)->not->toBeNull();
});

it('resolves a finding', function () {
    $finding = AuditFinding::create([
        'school_id' => $this->school->id, 'internal_audit_id' => $this->audit->id,
        'area' => 'Keuangan', 'description' => 'Pembukuan belum rapi', 'severity' => 'medium', 'status' => 'open',
    ]);

    $resolved = $this->service->resolve($finding);

    expect($resolved->status)->toBe('resolved');
    expect($resolved->resolved_at)->not->toBeNull();
});

it('refuses to complete an audit with open findings', function () {
    AuditFinding::create([
        'school_id' => $this->school->id, 'internal_audit_id' => $this->audit->id,
        'area' => 'Aset', 'description' => 'Aset belum diinventaris', 'severity' => 'high', 'status' => 'open',
    ]);

    $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
    $this->service->complete($this->audit);
});

it('summarizes findings by status and severity', function () {
    AuditFinding::create([
        'school_id' => $this->school->id, 'internal_audit_id' => $this->audit->id,
        'area' => 'A', 'description' => 'x', 'severity' => 'high', 'status' => 'open',
    ]);
    AuditFinding::create([
        'school_id' => $this->school->id, 'internal_audit_id' => $this->audit->id,
        'area' => 'B', 'description' => 'y', 'severity' => 'low', 'status' => 'resolved',
    ]);

    $summary = $this->service->summary($this->school->id);

    expect($summary['open'])->toBe(1);
    expect($summary['resolved'])->toBe(1);
    expect($summary['high'])->toBe(1);
});
