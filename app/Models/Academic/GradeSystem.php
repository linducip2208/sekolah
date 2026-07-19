<?php

namespace App\Models\Academic;

use App\Models\SchoolModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradeSystem extends SchoolModel
{
    protected $fillable = ['school_id', 'name', 'is_active'];
    protected $casts    = ['is_active' => 'boolean'];

    public function rules(): HasMany
    {
        return $this->hasMany(GradeRule::class);
    }
}
