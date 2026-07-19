<?php

namespace App\Models\Academic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PkgScore extends Model
{
    use SoftDeletes;

    protected $table = 'pkg_scores';

    protected $fillable = [
        'pkg_assessment_id', 'pkg_competency_id', 'score',
        'evidence_notes', 'file_path',
    ];

    protected $casts = [
        'score' => 'decimal:2',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(PkgAssessment::class);
    }

    public function competency(): BelongsTo
    {
        return $this->belongsTo(PkgCompetency::class);
    }
}
