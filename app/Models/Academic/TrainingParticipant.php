<?php

namespace App\Models\Academic;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingParticipant extends Model
{
    protected $fillable = [
        'training_id', 'staff_id', 'status', 'certificate_number',
        'certificate_file', 'score', 'feedback',
    ];

    protected $casts = [
        'score' => 'integer',
    ];

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
