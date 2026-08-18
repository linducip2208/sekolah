<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentTransfer extends SchoolModel
{
    protected $table = 'student_transfers';

    protected $fillable = [
        'school_id', 'student_id', 'from_school_name', 'to_school_name',
        'transfer_date', 'reason', 'document_no',
    ];

    protected $casts = [
        'transfer_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
