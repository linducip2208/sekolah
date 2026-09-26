<?php

use App\Models\Inventory\Asset;
use App\Models\Inventory\AssetCategory;
use App\Models\Inventory\MaintenanceRequest;
use App\Models\School;
use App\Models\User;
use App\Services\Inventory\InventoryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

beforeEach(function () {
    $this->school = School::factory()->create(['settings' => []]);
    $this->otherSchool = School::factory()->create(['settings' => []]);
    app()->instance('current_school', $this->school);

    $this->category = AssetCategory::create([
        'school_id' => $this->school->id,
        'name' => 'Perangkat TIK',
    ]);
    $this->asset = Asset::create([
        'school_id' => $this->school->id,
        'asset_category_id' => $this->category->id,
        'asset_code' => 'AST-001',
        'name' => 'Laptop Operator',
        'status' => 'available',
        'condition' => 'good',
    ]);
    $this->borrower = User::factory()->create(['school_id' => $this->school->id]);
    $this->approver = User::factory()->create(['school_id' => $this->school->id]);
    $this->service = app(InventoryService::class);
});

it('keeps asset loan approval and return tenant-bound and lifecycle-safe', function () {
    $loan = $this->service->requestLoan(
        $this->school->id,
        $this->asset->id,
        $this->borrower->id,
        now()->addDays(7),
    );

    $approved = $this->service->approveLoan($loan, $this->approver->id);
    expect($approved->status)->toBe('active')
        ->and($this->asset->fresh()->status)->toBe('borrowed');

    expect(fn () => $this->service->approveLoan($approved, $this->approver->id))
        ->toThrow(RuntimeException::class);

    $returned = $this->service->returnAsset($approved);
    expect($returned->status)->toBe('returned')
        ->and($this->asset->fresh()->status)->toBe('available');

    expect(fn () => $this->service->returnAsset($returned))
        ->toThrow(RuntimeException::class);
});

it('rejects foreign approvers and categories at the service boundary', function () {
    $foreignApprover = User::factory()->create(['school_id' => $this->otherSchool->id]);
    $foreignCategory = AssetCategory::create([
        'school_id' => $this->otherSchool->id,
        'name' => 'Foreign',
    ]);
    $loan = $this->service->requestLoan(
        $this->school->id,
        $this->asset->id,
        $this->borrower->id,
        now()->addDays(7),
    );

    expect(fn () => $this->service->approveLoan($loan, $foreignApprover->id))
        ->toThrow(ModelNotFoundException::class);
    expect(fn () => $this->service->createAsset($this->school->id, [
        'asset_category_id' => $foreignCategory->id,
        'name' => 'Invalid asset',
    ]))->toThrow(ModelNotFoundException::class);
});

it('rejects foreign maintenance assets and closed requests', function () {
    $foreignAsset = Asset::create([
        'school_id' => $this->otherSchool->id,
        'asset_category_id' => AssetCategory::create([
            'school_id' => $this->otherSchool->id,
            'name' => 'Foreign',
        ])->id,
        'asset_code' => 'AST-FOREIGN',
        'name' => 'Foreign asset',
        'status' => 'available',
        'condition' => 'good',
    ]);

    expect(fn () => $this->service->reportMaintenance($this->school->id, $this->borrower->id, [
        'asset_id' => $foreignAsset->id,
        'issue_description' => 'Invalid cross-school report',
    ]))->toThrow(ModelNotFoundException::class);

    $request = MaintenanceRequest::create([
        'school_id' => $this->school->id,
        'asset_id' => $this->asset->id,
        'reported_by' => $this->borrower->id,
        'issue_description' => 'Broken charger',
        'priority' => 'medium',
        'status' => 'resolved',
    ]);

    expect(fn () => $this->service->assignMaintenance($request, $this->approver->id))
        ->toThrow(RuntimeException::class);
});
