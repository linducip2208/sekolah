<?php

namespace App\Models\Canteen;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CanteenMenuItem extends SchoolModel
{
    public function category(): BelongsTo
    {
        return $this->belongsTo(CanteenCategory::class, 'canteen_category_id');
    }

    protected $table = 'canteen_menu_items';

    protected $fillable = [
        'school_id','canteen_category_id','name','description','price',
        'photo_path','allergens','is_available','stock_today',
    ];

    protected $casts = [
        'price'        => 'integer',
        'allergens'    => 'array',
        'is_available' => 'boolean',
        'stock_today'  => 'integer',
    ];
}
