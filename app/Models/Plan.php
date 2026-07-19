<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'price', 'max_students',
        'max_teachers', 'features', 'is_active',
    ];

    protected $casts = [
        'features'   => 'array',
        'is_active'  => 'boolean',
        'price'      => 'integer',
        'max_students' => 'integer',
        'max_teachers' => 'integer',
    ];

    public function schools(): HasMany
    {
        return $this->hasMany(School::class);
    }

    public function hasFeature(string $feature): bool
    {
        $features = $this->features ?? [];
        return in_array('*', $features) || in_array($feature, $features);
    }

    public function getPriceRupiahAttribute(): string
    {
        return 'Rp ' . number_format($this->price / 100, 0, ',', '.');
    }
}
