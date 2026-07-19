<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccreditationStandard extends Model
{
    protected $fillable = [
        'code', 'name', 'description', 'max_score', 'weight_percent',
    ];

    protected $casts = [
        'max_score'      => 'integer',
        'weight_percent' => 'float',
    ];

    public function instruments(): HasMany
    {
        return $this->hasMany(AccreditationInstrument::class);
    }
}
