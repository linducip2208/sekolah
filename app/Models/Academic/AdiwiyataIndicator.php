<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdiwiyataIndicator extends Model
{
    use HasFactory;

    protected $table = 'adiwiyata_indicators';

    protected $fillable = [
        'adiwiyata_category_id', 'code', 'description',
        'evidence_hint', 'max_score', 'evidence_type',
    ];

    protected $casts = [
        'max_score' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(AdiwiyataCategory::class, 'adiwiyata_category_id');
    }

    public function evidences()
    {
        return $this->hasMany(AdiwiyataEvidence::class);
    }
}
