<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdiwiyataCategory extends Model
{
    use HasFactory;

    protected $table = 'adiwiyata_categories';

    protected $fillable = ['name', 'description', 'weight', 'sort_order'];

    protected $casts = [
        'weight' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function indicators(): HasMany
    {
        return $this->hasMany(AdiwiyataIndicator::class);
    }
}
