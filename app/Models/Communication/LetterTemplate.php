<?php

namespace App\Models\Communication;

use App\Models\SchoolModel;

class LetterTemplate extends SchoolModel
{
    protected $table = 'letter_templates';

    protected $fillable = [
        'school_id', 'name', 'code', 'content', 'variables',
        'category', 'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'sk'              => 'SK',
            'surat-keterangan' => 'Surat Keterangan',
            'surat-izin'      => 'Surat Izin',
            'surat-panggilan' => 'Surat Panggilan',
            default           => 'Lainnya',
        };
    }
}
