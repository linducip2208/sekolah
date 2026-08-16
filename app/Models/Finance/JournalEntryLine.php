<?php

namespace App\Models\Finance;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntryLine extends SchoolModel
{
    protected $table = 'journal_entry_lines';

    protected $fillable = [
        'school_id', 'journal_entry_id', 'chart_of_account_id', 'debit', 'credit', 'description',
    ];

    protected $casts = [
        'debit'  => 'integer',
        'credit' => 'integer',
    ];

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }
}
