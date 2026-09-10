<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use App\Services\ProcurementService;
use Tests\TestCase;

class ProcurementWorkflowTest extends TestCase
{
    public function test_procurement_moves_through_approval_order_and_partial_receipt(): void
    {
        $school = School::factory()->create();
        $admin = User::factory()->create(['school_id' => $school->id]);
        $admin->assignRole('admin');
        $accountant = User::factory()->create(['school_id' => $school->id]);
        $accountant->assignRole('accountant');
        $this->actingAs($admin);
        $service = new ProcurementService($school->id);

        $request = $service->create([
            'requester_id' => $admin->id,
            'title' => 'Pengadaan perangkat kelas',
            'estimated_budget' => 5000000,
            'urgency' => 'medium',
            'items' => [[
                'item_name' => 'Proyektor',
                'quantity' => 5,
                'unit' => 'unit',
                'estimated_unit_price' => 1000000,
            ]],
        ]);

        $service->submitForApproval($request);
        $request->refresh();
        $this->assertSame('submitted', $request->status);

        foreach ($request->approvals as $approval) {
            $service->approveStep($approval, 'Disetujui.');
        }

        $request->refresh();
        $this->assertSame('approved', $request->status);
        $service->markAsOrdered($request);
        $this->assertSame('ordered', $request->fresh()->status);

        $item = $request->items()->firstOrFail();
        $service->receiveItems($request, [$item->id => 2]);
        $this->assertSame('ordered', $request->fresh()->status);
        $this->assertSame(2.0, (float) $item->fresh()->received_qty);

        $service->receiveItems($request, [$item->id => 5]);
        $this->assertSame('received', $request->fresh()->status);
    }

    public function test_procurement_rejects_receiving_more_than_ordered_quantity(): void
    {
        $school = School::factory()->create();
        $admin = User::factory()->create(['school_id' => $school->id]);
        $admin->assignRole('admin');
        $this->actingAs($admin);
        $service = new ProcurementService($school->id);

        $request = $service->create([
            'requester_id' => $admin->id,
            'title' => 'Pengadaan ATK',
            'estimated_budget' => 100000,
            'urgency' => 'low',
            'status' => 'ordered',
            'items' => [[
                'item_name' => 'Kertas',
                'quantity' => 2,
                'estimated_unit_price' => 50000,
            ]],
        ]);

        $item = $request->items()->firstOrFail();
        try {
            $service->receiveItems($request, [$item->id => 3]);
            $this->fail('Receiving more than ordered quantity must fail.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('tidak valid', $exception->getMessage());
        }
        $this->assertDatabaseHas('procurement_items', ['id' => $item->id, 'received_qty' => 0]);
    }
}
