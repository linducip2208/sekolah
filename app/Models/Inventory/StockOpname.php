<?php

namespace App\Models\Inventory;

use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpname extends SchoolModel
{
    protected $table = 'stock_opnames';

    protected $fillable = [
        'school_id', 'inventory_item_id', 'recorded_qty', 'actual_qty', 'difference',
        'opname_date', 'created_by', 'note',
    ];

    protected $casts = [
        'recorded_qty' => 'integer',
        'actual_qty'   => 'integer',
        'difference'   => 'integer',
        'opname_date'  => 'date',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
