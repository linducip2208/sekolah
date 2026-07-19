<?php

namespace App\Http\Controllers\Api\Event;

use App\Http\Controllers\Controller;
use App\Models\Event\EventRsvp;
use App\Models\Event\SchoolEvent;
use App\Models\School;
use App\Services\Event\EventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct(private EventService $service) {}

    public function publicList(string $subdomain): JsonResponse
    {
        $school = School::where('subdomain', $subdomain)->firstOrFail();
        $events = SchoolEvent::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('is_published', true)
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')->get();

        return response()->json(['data' => $events]);
    }

    public function publicShow(string $subdomain, string $slug): JsonResponse
    {
        $school = School::where('subdomain', $subdomain)->firstOrFail();
        $event = SchoolEvent::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json($event);
    }

    public function adminList(Request $request): JsonResponse
    {
        return response()->json([
            'data' => SchoolEvent::where('school_id', $request->user()->school_id)
                ->orderByDesc('starts_at')->paginate(50),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title'             => 'required|string|max:200',
            'description'       => 'required|string',
            'event_type'        => 'required|in:parent_meeting,field_trip,festival,competition,workshop,seminar',
            'starts_at'         => 'required|date',
            'ends_at'           => 'required|date|after:starts_at',
            'venue'             => 'required|string|max:200',
            'city'              => 'nullable|string|max:100',
            'venue_lat'         => 'nullable|numeric',
            'venue_lng'         => 'nullable|numeric',
            'capacity'          => 'nullable|integer|min:1',
            'ticket_price'      => 'nullable|integer|min:0',
            'target_audience'   => 'nullable|array',
            'cover_image_path'  => 'nullable|string|max:500',
            'require_rsvp'      => 'nullable|boolean',
            'is_published'      => 'nullable|boolean',
        ]);

        return response()->json(
            $this->service->createEvent($request->user()->school_id, $data),
            201,
        );
    }

    public function rsvp(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status'        => 'required|in:going,maybe,not_going,cancelled',
            'guests_count'  => 'nullable|integer|min:0|max:10',
        ]);

        $event = SchoolEvent::where('school_id', $request->user()->school_id)->findOrFail($id);

        try {
            return response()->json($this->service->rsvp(
                $event, $request->user()->id, $request->input('status'),
                (int) $request->input('guests_count', 0),
            ));
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function checkIn(Request $request): JsonResponse
    {
        $request->validate(['qr_token' => 'required|string']);

        try {
            $rsvp = $this->service->checkIn($request->input('qr_token'));
            return response()->json($rsvp);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function rsvps(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'data' => EventRsvp::where('school_event_id', $id)->paginate(100),
        ]);
    }
}
