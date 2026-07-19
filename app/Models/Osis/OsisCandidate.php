<?php

namespace App\Models\Osis;

use App\Models\Academic\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OsisCandidate extends Model
{
    protected $fillable = [
        'osis_election_id', 'student_id', 'position',
        'vision', 'mission', 'photo_path', 'status', 'vote_count',
    ];

    public function election(): BelongsTo
    {
        return $this->belongsTo(OsisElection::class, 'osis_election_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
