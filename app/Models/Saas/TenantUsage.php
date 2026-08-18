<?php

namespace App\Models\Saas;

use App\Models\School;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenantUsage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'school_id', 'month', 'active_students', 'active_teachers',
        'total_logins', 'api_calls', 'storage_used_bytes',
        'sms_sent', 'emails_sent',
    ];

    protected $casts = [
        'active_students'    => 'integer',
        'active_teachers'    => 'integer',
        'total_logins'       => 'integer',
        'api_calls'          => 'integer',
        'storage_used_bytes' => 'integer',
        'sms_sent'           => 'integer',
        'emails_sent'        => 'integer',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function getStorageUsedMbAttribute(): float
    {
        return round($this->storage_used_bytes / (1024 * 1024), 2);
    }
}
