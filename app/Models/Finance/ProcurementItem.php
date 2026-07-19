<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcurementItem extends Model
{
    use SoftDeletes;

    protected $table = 'procurement_items';

    protected $fillable = [
        'procurement_request_id', 'item_name', 'quantity', 'unit',
        'estimated_unit_price', 'actual_unit_price', 'supplier_id',
        'supplier_name', 'received_qty',
    ];

    protected $casts = [
        'quantity'              => 'decimal:2',
        'estimated_unit_price'  => 'integer',
        'actual_unit_price'     => 'integer',
        'received_qty'          => 'decimal:2',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(ProcurementRequest::class, 'procurement_request_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function subtotalEstimated(): int
    {
        return (int) ($this->estimated_unit_price * $this->quantity);
    }

    public function subtotalActual(): ?int
    {
        return $this->actual_unit_price !== null
            ? (int) ($this->actual_unit_price * $this->quantity)
            : null;
    }
}
