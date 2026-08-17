<?php

namespace App\Http\Controllers\Web\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\StockOpname;
use App\Services\Inventory\InventoryStockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryStockController extends Controller
{
    public function __construct(private InventoryStockService $service) {}

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $schoolId = $this->schoolId();

        $items = $this->service->list($schoolId);
        $itemId = $request->item_id;
        $movements = collect();
        $opnames = StockOpname::where('school_id', $schoolId)
            ->with('item:id,name', 'creator:id,name')
            ->orderByDesc('opname_date')
            ->limit(30)
            ->get();

        if ($itemId) {
            $movements = \App\Models\Inventory\StockMovement::where('school_id', $schoolId)
                ->where('inventory_item_id', $itemId)
                ->with('creator:id,name')
                ->orderByDesc('created_at')
                ->paginate(20)
                ->withQueryString();
        }

        return view('school-admin.inventory.stock', compact('items', 'itemId', 'movements', 'opnames'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'         => 'required|string|max:200',
            'sku'          => 'nullable|string|max:50',
            'unit'         => 'required|string|max:20',
            'quantity'     => 'nullable|integer|min:0',
            'min_quantity' => 'nullable|integer|min:0',
            'location'     => 'nullable|string|max:100',
        ]);

        $this->service->create($this->schoolId(), $data);

        return back()->with('success', 'Item stok ditambahkan.');
    }

    public function stockIn(Request $request, InventoryItem $item): RedirectResponse
    {
        $this->authorizeOwn($item);
        $data = $request->validate(['quantity' => 'required|integer|min:1', 'note' => 'nullable|string']);
        $this->service->stockIn($item, (int) $data['quantity'], $data['note'] ?? null, null, auth()->id());
        return back()->with('success', 'Stok masuk dicatat.');
    }

    public function stockOut(Request $request, InventoryItem $item): RedirectResponse
    {
        $this->authorizeOwn($item);
        $data = $request->validate(['quantity' => 'required|integer|min:1', 'note' => 'nullable|string']);
        $this->service->stockOut($item, (int) $data['quantity'], $data['note'] ?? null, null, auth()->id());
        return back()->with('success', 'Stok keluar dicatat.');
    }

    public function opname(Request $request, InventoryItem $item): RedirectResponse
    {
        $this->authorizeOwn($item);
        $data = $request->validate(['actual_qty' => 'required|integer|min:0', 'note' => 'nullable|string']);
        $this->service->opname($item, (int) $data['actual_qty'], $data['note'] ?? null, auth()->id());
        return back()->with('success', 'Stock opname disimpan.');
    }

    public function destroy(InventoryItem $item): RedirectResponse
    {
        $this->authorizeOwn($item);
        $item->delete();
        return back()->with('success', 'Item stok dihapus.');
    }

    private function authorizeOwn(InventoryItem $item): void
    {
        abort_unless($item->school_id === $this->schoolId(), 403);
    }
}
