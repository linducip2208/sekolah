<?php

namespace App\Models\Religious;

use App\Models\Academic\Student;
use App\Models\SchoolModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HafalanProgress extends SchoolModel
{
    protected $table = 'hafalan_progress';

    protected $fillable = [
        'school_id','student_id','hafalan_target_id','verified_by',
        'surah','ayah_start','ayah_end','memorized_at','quality','note','audio_path',
    ];

    protected $casts = [
        'memorized_at' => 'date',
        'audio_path'   => 'array',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
    public function target(): BelongsTo
    {
        return $this->belongsTo(HafalanTarget::class, 'hafalan_target_id');
    }
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
