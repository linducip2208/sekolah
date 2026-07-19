<?php

namespace App\Models\Religious;

use App\Models\SchoolModel;

class IbadahLog extends SchoolModel
{
    protected $table = 'ibadah_logs';

    protected $fillable = [
        'school_id','student_id','log_date',
        'subuh','dzuhur','ashar','maghrib','isya',
        'puasa_sunnah','tilawah_done','tilawah_ayah_count','extra_amalan','verified_by',
    ];

    protected $casts = [
        'log_date'           => 'date',
        'puasa_sunnah'       => 'boolean',
        'tilawah_done'       => 'boolean',
        'extra_amalan'       => 'array',
    ];
}
