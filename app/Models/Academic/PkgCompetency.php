<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PkgCompetency extends Model
{
    use SoftDeletes;

    protected $table = 'pkg_competencies';

    protected $fillable = [
        'code', 'name', 'description', 'competency_type',
        'weight', 'is_active',
    ];

    protected $casts = [
        'weight'   => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function scores(): HasMany
    {
        return $this->hasMany(PkgScore::class);
    }
}
