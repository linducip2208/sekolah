<?php

namespace App\Models\Finance;

use App\Models\SchoolModel;

class Supplier extends SchoolModel
{
    protected $table = 'suppliers';

    protected $fillable = [
        'school_id', 'name', 'contact_person', 'phone', 'email',
        'address', 'category', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function procurementItems()
    {
        return $this->hasMany(ProcurementItem::class);
    }
}
