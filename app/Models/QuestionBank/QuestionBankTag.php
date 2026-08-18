<?php

namespace App\Models\QuestionBank;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class QuestionBankTag extends SchoolModel
{
    protected $table = 'question_bank_tags';

    protected $fillable = ['school_id', 'name', 'color'];

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(
            QuestionBankItem::class,
            'question_tag_pivot',
            'question_tag_id',
            'question_bank_item_id',
        );
    }
}
