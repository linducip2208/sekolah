<?php

namespace App\Models\Traits;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

trait AuditableModel
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName(strtolower(class_basename(static::class)));
    }

    /**
     * Stamp every audit entry with the tenant so the audit-log UI
     * (which filters on properties->school_id) can actually see it.
     */
    public function tapActivity(Activity $activity, string $eventName): void
    {
        $schoolId = $this->getAttribute('school_id') ?? auth()->user()?->school_id;

        if ($schoolId) {
            $activity->properties = ($activity->properties ?? collect())->merge([
                'school_id' => (int) $schoolId,
            ]);
        }
    }
}
