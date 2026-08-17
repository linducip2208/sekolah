<?php

use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\StockMovement;
use App\Models\Inventory\StockOpname;
use App\Models\School;
use App\Services\Inventory\InventoryStockService;

beforeEach(function () {
    $this->service = app(InventoryStockService::class);
    $this->school = School::factory()->create();
    $this->item = $this->service->create($this->school->id, [
        'name' => 'Kertas A4', 'sku' => 'KA4', 'unit' => 'rim', 'quantity' => 10, 'min_quantity' => 2,
    ]);
});

it('creates an inventory item', function () {
    expect($this->item->quantity)->toBe(10);
    expect($this->item->isLowStock())->toBeFalse();
});

it('records stock in and out with movement history', function () {
    $this->service->stockIn($this->item, 5, 'Pembelian');
    expect($this->item->fresh()->quantity)->toBe(15);

    $this->service->stockOut($this->item->fresh(), 3, 'Pemakaian');
    expect($this->item->fresh()->quantity)->toBe(12);

    expect(StockMovement::where('inventory_item_id', $this->item->id)->count())->toBe(2);
});

it('rejects stock out beyond available quantity', function () {
    $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
    $this->service->stockOut($this->item, 999);
});

it('performs stock opname and adjusts stock', function () {
    $opname = $this->service->opname($this->item, 8, 'Stok fisik');

    expect($opname)->toBeInstanceOf(StockOpname::class);
    expect($opname->difference)->toBe(-2);
    expect($this->item->fresh()->quantity)->toBe(8);
});

it('flags low stock items', function () {
    $this->service->stockOut($this->item, 9); // quantity -> 1 (<= min 2)
    expect($this->item->fresh()->isLowStock())->toBeTrue();
});
