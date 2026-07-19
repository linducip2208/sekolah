<?php

namespace App\Models\Inventory;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends SchoolModel
{
    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function maintenanceSchedules(): HasMany
    {
        return $this->hasMany(AssetMaintenanceSchedule::class);
    }

    public function writeOffs(): HasMany
    {
        return $this->hasMany(AssetWriteOff::class);
    }

    protected $table = 'assets';

    protected $fillable = [
        'school_id','asset_category_id','asset_code','name','description',
        'serial_number','purchased_at','purchase_price','warranty_until',
        'location','photo_path','condition','status','specs',
        'purchase_date','useful_life_years','salvage_value','depreciation_method',
        'monthly_depreciation','last_maintenance_date','next_maintenance_date',
        'qr_code','location_detail','warranty_expiry_date','supplier_name',
    ];

    protected $casts = [
        'purchased_at'             => 'date',
        'purchase_price'           => 'integer',
        'warranty_until'           => 'date',
        'specs'                    => 'array',
        'purchase_date'            => 'date',
        'useful_life_years'        => 'integer',
        'salvage_value'            => 'integer',
        'monthly_depreciation'     => 'integer',
        'last_maintenance_date'    => 'date',
        'next_maintenance_date'    => 'date',
        'warranty_expiry_date'     => 'date',
    ];
}
