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
        return $this->applyMovement($item, 'out', -abs($qty), $note, $reference, $userId);
    }

    /** Stock opname: record actual count and adjust stock to match. */
    public function opname(InventoryItem $item, int $actualQty, ?string $note = null, ?int $userId = null, ?string $date = null): StockOpname
    {
        return DB::transaction(function () use ($item, $actualQty, $note, $userId, $date) {
            $lockedItem = InventoryItem::where('school_id', $item->school_id)
                ->whereKey($item->id)
                ->lockForUpdate()
                ->firstOrFail();
            $recorded = $lockedItem->quantity;
            $diff = $actualQty - $recorded;

            $opname = StockOpname::create([
                'school_id' => $item->school_id,
                'inventory_item_id' => $item->id,
                'recorded_qty' => $recorded,
                'actual_qty' => $actualQty,
                'difference' => $diff,
                'opname_date' => $date ?? now()->toDateString(),
                'created_by' => $userId,
                'note' => $note,
            ]);

            if ($diff !== 0) {
                $this->recordMovement($lockedItem, 'adjustment', $diff, 'Penyesuaian stock opname', 'OPNAME-'.$opname->id, $userId);
            }

            return $opname;
        });
    }

    /** Transfer stock between two locations (out from one, in to another). */
    public function transfer(InventoryItem $fromItem, InventoryItem $toItem, int $qty, ?int $userId = null): void
    {
        abort_if($qty < 1, 422, 'Jumlah transfer harus lebih besar dari nol.');
        abort_unless($fromItem->school_id === $toItem->school_id, 403, 'Transfer lintas sekolah tidak diizinkan.');
        abort_if($fromItem->id === $toItem->id, 422, 'Lokasi sumber dan tujuan harus berbeda.');

        DB::transaction(function () use ($fromItem, $toItem, $qty, $userId) {
            $firstId = min($fromItem->id, $toItem->id);
            $secondId = max($fromItem->id, $toItem->id);
            $locked = InventoryItem::where('school_id', $fromItem->school_id)
                ->whereIn('id', [$firstId, $secondId])
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $from = $locked->get($fromItem->id);
            $to = $locked->get($toItem->id);
            abort_unless($from && $to, 404);
            abort_if($from->quantity < $qty, 422, "Stok tidak cukup (tersedia {$from->quantity}).");

            $reference = 'TRANSFER-'.$from->id.'-'.$to->id.'-'.now()->format('YmdHisv');
            $this->recordMovement($from, 'transfer_out', -abs($qty), 'Transfer keluar', $reference, $userId);
            $this->recordMovement($to, 'transfer_in', abs($qty), 'Transfer masuk', $reference, $userId);
        });
    }

    protected function applyMovement(InventoryItem $item, string $type, int $qtyChange, ?string $note, ?string $reference, ?int $userId): InventoryItem
    {
        return DB::transaction(function () use ($item, $type, $qtyChange, $note, $reference, $userId) {
            $lockedItem = InventoryItem::where('school_id', $item->school_id)
                ->whereKey($item->id)
                ->lockForUpdate()
                ->firstOrFail();

            return $this->recordMovement($lockedItem, $type, $qtyChange, $note, $reference, $userId);
        });
    }

    private function recordMovement(InventoryItem $item, string $type, int $qtyChange, ?string $note, ?string $reference, ?int $userId): InventoryItem
    {
        $newQty = $item->quantity + $qtyChange;
        abort_if($newQty < 0, 422, "Stok tidak cukup (tersedia {$item->quantity}).");

        StockMovement::create([
            'school_id' => $item->school_id,
            'inventory_item_id' => $item->id,
            'type' => $type,
            'quantity' => $qtyChange,
            'quantity_after' => $newQty,
            'reference' => $reference,
            'note' => $note,
            'created_by' => $userId ?? auth()->id(),
        ]);
        $item->update(['quantity' => $newQty]);

        return $item->fresh();
    }
}
