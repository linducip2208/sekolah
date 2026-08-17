<?php

namespace App\Services\Inventory;

use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\StockMovement;
use App\Models\Inventory\StockOpname;
use Illuminate\Support\Facades\DB;

class InventoryStockService
{
    public function list(int $schoolId)
    {
        return InventoryItem::where('school_id', $schoolId)->orderBy('name')->get();
    }

    public function create(int $schoolId, array $data): InventoryItem
    {
        return InventoryItem::create(array_merge($data, ['school_id' => $schoolId]));
    }

    public function stockIn(InventoryItem $item, int $qty, ?string $note = null, ?string $reference = null, ?int $userId = null): InventoryItem
    {
        return $this->applyMovement($item, 'in', abs($qty), $note, $reference, $userId);
    }

    public function stockOut(InventoryItem $item, int $qty, ?string $note = null, ?string $reference = null, ?int $userId = null): InventoryItem
    {
        abort_if($item->quantity < $qty, 422, "Stok tidak cukup (tersedia {$item->quantity}).");

        return $this->applyMovement($item, 'out', -abs($qty), $note, $reference, $userId);
    }

    /** Stock opname: record actual count and adjust stock to match. */
    public function opname(InventoryItem $item, int $actualQty, ?string $note = null, ?int $userId = null, ?string $date = null): StockOpname
    {
        return DB::transaction(function () use ($item, $actualQty, $note, $userId, $date) {
            $recorded = $item->quantity;
            $diff     = $actualQty - $recorded;

            $opname = StockOpname::create([
                'school_id'         => $item->school_id,
                'inventory_item_id' => $item->id,
                'recorded_qty'      => $recorded,
                'actual_qty'        => $actualQty,
                'difference'        => $diff,
                'opname_date'       => $date ?? now()->toDateString(),
                'created_by'        => $userId,
                'note'              => $note,
            ]);

            if ($diff !== 0) {
                $this->applyMovement($item, 'adjustment', $diff, 'Penyesuaian stock opname', 'OPNAME-'.$opname->id, $userId);
            }

            return $opname;
        });
    }

    /** Transfer stock between two locations (out from one, in to another). */
    public function transfer(InventoryItem $fromItem, InventoryItem $toItem, int $qty, ?int $userId = null): void
    {
        DB::transaction(function () use ($fromItem, $toItem, $qty, $userId) {
            $this->stockOut($fromItem, $qty, 'Transfer keluar', 'TRANSFER', $userId);
            $this->stockIn($toItem, $qty, 'Transfer masuk', 'TRANSFER', $userId);
        });
    }

    protected function applyMovement(InventoryItem $item, string $type, int $qtyChange, ?string $note, ?string $reference, ?int $userId): InventoryItem
    {
        return DB::transaction(function () use ($item, $type, $qtyChange, $note, $reference, $userId) {
            $newQty = $item->quantity + $qtyChange;

            StockMovement::create([
                'school_id'         => $item->school_id,
                'inventory_item_id' => $item->id,
                'type'              => $type,
                'quantity'          => $qtyChange,
                'quantity_after'    => $newQty,
                'reference'         => $reference,
                'note'              => $note,
                'created_by'        => $userId ?? auth()->id(),
            ]);

            $item->update(['quantity' => $newQty]);

            return $item->fresh();
        });
    }
}
