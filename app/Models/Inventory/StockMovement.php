<?php

namespace App\Models\Inventory;

use App\Models\SchoolModel;
use App\Models\Traits\AuditableModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends SchoolModel
{
    use AuditableModel;

    protected $table = 'stock_movements';

    protected $fillable = [
        'school_id', 'inventory_item_id', 'type', 'quantity', 'quantity_after',
        'reference', 'note', 'created_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'quantity_after' => 'integer',
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
