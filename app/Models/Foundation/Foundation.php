<?php

namespace App\Models\Foundation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Foundation extends Model
{
    use SoftDeletes;

    protected $table = 'foundations';

    protected $fillable = [
        'name','slug','logo_path','address','npwp','contact','is_active',
    ];

    protected $casts = [
        'contact'   => 'array',
        'is_active' => 'boolean',
    ];

    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\School::class, 'foundation_school_links')
            ->withPivot('joined_at', 'is_primary_school')
            ->withTimestamps();
    }

    public function admins(): HasMany
    {
        return $this->hasMany(FoundationAdmin::class);
    }
}
