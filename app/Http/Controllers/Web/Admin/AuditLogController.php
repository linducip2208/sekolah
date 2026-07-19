<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $schoolUserIds = User::where('school_id', $schoolId)->pluck('id');

        $logs = Activity::query()
            ->with(['causer:id,name,email,school_id', 'subject'])
            ->where(function ($q) use ($schoolUserIds) {
                $q->whereIn('causer_id', $schoolUserIds)
                  ->orWhereJsonContains('properties->school_id', auth()->user()->school_id);
            })
            ->when($request->event, fn($q) => $q->where('event', $request->event))
            ->when($request->log_name, fn($q) => $q->where('log_name', $request->log_name))
            ->when($request->causer_id, fn($q) => $q->where('causer_id', $request->causer_id))
            ->when($request->date_from, fn($q) => $q->where('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->where('created_at', '<=', $request->date_to . ' 23:59:59'))
            ->orderByDesc('created_at')
            ->paginate(50)
            ->withQueryString();

        $events = Activity::query()->whereNotNull('event')->distinct()->pluck('event');
        $logNames = Activity::query()->whereNotNull('log_name')->distinct()->pluck('log_name');
        $users  = User::where('school_id', $schoolId)->orderBy('name')->get(['id', 'name']);

        return view('school-admin.audit.index', compact('logs', 'events', 'logNames', 'users'));
    }

    public function show(Activity $activity)
    {
        $schoolId = auth()->user()->school_id;
        $causerSchool = $activity->causer?->school_id;
        $subjectSchool = data_get($activity->properties, 'school_id');

        abort_unless(
            ($causerSchool && $causerSchool == $schoolId) || ($subjectSchool && $subjectSchool == $schoolId),
            403
        );

        return view('school-admin.audit.show', ['log' => $activity]);
    }
}
