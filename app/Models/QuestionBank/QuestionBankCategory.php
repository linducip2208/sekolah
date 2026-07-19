<?php

namespace App\Models\QuestionBank;

use App\Models\Academic\Subject;
use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionBankCategory extends SchoolModel
{
    protected $table = 'question_bank_categories';

    protected $fillable = ['school_id','subject_id','name','parent_id'];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
