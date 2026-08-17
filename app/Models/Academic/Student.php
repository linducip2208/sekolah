<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use App\Models\Traits\AuditableModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends SchoolModel
{
    use AuditableModel;

    protected $fillable = [
        'user_id', 'school_id', 'class_section_id', 'status',
        'admission_no', 'admission_date', 'enrolled_at', 'graduated_at', 'transferred_at',
        'date_of_birth', 'gender', 'blood_group',
        'address', 'guardian_name', 'guardian_phone', 'whatsapp_phone',
        'has_hostel', 'has_transport',
    ];

    protected $casts = [
        'admission_date' => 'date',
        'enrolled_at'    => 'date',
        'graduated_at'   => 'date',
        'transferred_at' => 'date',
        'date_of_birth'  => 'date',
        'has_hostel'     => 'boolean',
        'has_transport'  => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'parent_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(StudentStatusHistory::class)->orderByDesc('created_at');
    }
}
