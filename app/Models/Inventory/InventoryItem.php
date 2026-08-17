<?php

namespace App\Models\Inventory;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends SchoolModel
{
    protected $table = 'inventory_items';

    protected $fillable = [
        'school_id', 'name', 'sku', 'unit', 'quantity', 'min_quantity', 'location',
    ];

    protected $casts = [
        'quantity'     => 'integer',
        'min_quantity' => 'integer',
    ];

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'inventory_item_id')->orderByDesc('created_at');
    }

    public function isLowStock(): bool
    {
        return $this->min_quantity > 0 && $this->quantity <= $this->min_quantity;
    }
}
