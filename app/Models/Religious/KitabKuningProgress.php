<?php

namespace App\Models\Religious;

use App\Models\SchoolModel;

class KitabKuningProgress extends SchoolModel
{
    protected $table = 'kitab_kuning_progress';

    protected $fillable = [
        'school_id','student_id','teacher_id','kitab_name',
        'current_bab','halaman_terakhir','last_session','catatan_ustadz',
    ];

    protected $casts = [
        'halaman_terakhir' => 'integer',
        'last_session'     => 'date',
    ];
}
