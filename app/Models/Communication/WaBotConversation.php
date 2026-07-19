<?php

namespace App\Models\Communication;

use App\Models\Academic\Student;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaBotConversation extends SchoolModel
{
    protected $fillable = [
        'school_id', 'phone', 'student_id', 'message_direction',
        'message_text', 'matched_command', 'response_text', 'session_active',
    ];

    protected $casts = [
        'session_active' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
