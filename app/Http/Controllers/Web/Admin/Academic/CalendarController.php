<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\CalendarEvent;
use App\Models\Academic\ClassSection;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CalendarController extends Controller
{
    private const EVENT_TYPES = ['holiday', 'exam', 'meeting', 'extracurricular', 'other'];

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(): View
    {
        $classSections = ClassSection::where('school_id', $this->schoolId())
            ->with(['classRoom', 'section'])
            ->orderBy('class_room_id')
            ->orderBy('section_id')
            ->get();

        return view('school-admin.academic.calendar', compact('classSections'));
    }

    public function feed(Request $request): JsonResponse
    {
        $schoolId = $this->schoolId();
        $start = $request->get('start');
        $end = $request->get('end');

        $query = CalendarEvent::where('school_id', $schoolId)
            ->where('is_published', true);

        if ($start && $end) {
            $query->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                  ->orWhereBetween('end_date', [$start, $end])
                  ->orWhere(function ($q2) use ($start, $end) {
                      $q2->where('start_date', '<=', $start)
                         ->where('end_date', '>=', $end);
                  });
            });
        }

        if ($request->get('class_section_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('class_section_id', $request->get('class_section_id'))
                  ->orWhereNull('class_section_id');
            });
        }

        if ($request->get('event_type')) {
            $query->where('event_type', $request->get('event_type'));
        }

        $events = $query->get()->map(fn (CalendarEvent $e) => [
            'id'              => $e->id,
            'title'           => $e->title,
            'start'           => $e->start_date->toIso8601String(),
            'end'             => $e->end_date ? $e->end_date->toIso8601String() : $e->start_date->toIso8601String(),
            'allDay'          => $e->all_day,
            'color'           => $e->color ?? $this->eventTypeColor($e->event_type),
            'extendedProps'   => [
                'description'     => $e->description,
                'event_type'      => $e->event_type,
                'class_section_id' => $e->class_section_id,
                'is_published'    => $e->is_published,
            ],
        ]);

        return response()->json($events);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string|max:5000',
            'event_type'       => 'required|in:' . implode(',', self::EVENT_TYPES),
            'start_date'       => 'required|date',
            'end_date'         => 'nullable|date|after_or_equal:start_date',
            'all_day'          => 'nullable|boolean',
            'color'            => 'nullable|string|max:20',
            'class_section_id' => 'nullable|exists:class_sections,id',
            'is_published'     => 'nullable|boolean',
        ]);

        CalendarEvent::create([
            'school_id'        => $this->schoolId(),
            'created_by'       => auth()->id(),
            'title'            => $data['title'],
            'description'      => $data['description'] ?? null,
            'event_type'       => $data['event_type'],
            'start_date'       => Carbon::parse($data['start_date']),
            'end_date'         => isset($data['end_date']) ? Carbon::parse($data['end_date']) : null,
            'all_day'          => (bool) ($data['all_day'] ?? true),
            'color'            => $data['color'] ?? null,
            'class_section_id' => $data['class_section_id'] ?? null,
            'is_published'     => (bool) ($data['is_published'] ?? true),
        ]);

        return redirect()->route('admin.calendar.index')->with('success', 'Event berhasil ditambahkan.');
    }

    public function update(Request $request, CalendarEvent $event): RedirectResponse
    {
        $this->authorizeOwn($event);

        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string|max:5000',
            'event_type'       => 'required|in:' . implode(',', self::EVENT_TYPES),
            'start_date'       => 'required|date',
            'end_date'         => 'nullable|date|after_or_equal:start_date',
            'all_day'          => 'nullable|boolean',
            'color'            => 'nullable|string|max:20',
            'class_section_id' => 'nullable|exists:class_sections,id',
            'is_published'     => 'nullable|boolean',
        ]);

        $event->update([
            'title'            => $data['title'],
            'description'      => $data['description'] ?? null,
            'event_type'       => $data['event_type'],
            'start_date'       => Carbon::parse($data['start_date']),
            'end_date'         => isset($data['end_date']) ? Carbon::parse($data['end_date']) : null,
            'all_day'          => (bool) ($data['all_day'] ?? true),
            'color'            => $data['color'] ?? null,
            'class_section_id' => $data['class_section_id'] ?? null,
            'is_published'     => (bool) ($data['is_published'] ?? true),
        ]);

        return redirect()->route('admin.calendar.index')->with('success', 'Event berhasil diperbarui.');
    }

    public function destroy(CalendarEvent $event): RedirectResponse
    {
        $this->authorizeOwn($event);
        $event->delete();
        return redirect()->route('admin.calendar.index')->with('success', 'Event berhasil dihapus.');
    }

    public function ical(): Response
    {
        $schoolId = $this->schoolId();

        $events = CalendarEvent::where('school_id', $schoolId)
            ->where('is_published', true)
            ->orderBy('start_date')
            ->get();

        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//Sikad Pro//Calendar//ID\r\n";
        $ical .= "CALSCALE:GREGORIAN\r\n";
        $ical .= "METHOD:PUBLISH\r\n";
        $ical .= "X-WR-CALNAME:Kalender Sekolah\r\n";
        $ical .= "X-WR-TIMEZONE:Asia/Jakarta\r\n";

        foreach ($events as $event) {
            $ical .= "BEGIN:VEVENT\r\n";
            $ical .= "UID:" . $event->id . "@sikadpro\r\n";
            $ical .= "DTSTAMP:" . $event->created_at->format('Ymd\THis\Z') . "\r\n";

            if ($event->all_day) {
                $ical .= "DTSTART;VALUE=DATE:" . $event->start_date->format('Ymd') . "\r\n";
                if ($event->end_date) {
                    $end = Carbon::parse($event->end_date)->addDay();
                    $ical .= "DTEND;VALUE=DATE:" . $end->format('Ymd') . "\r\n";
                }
            } else {
                $ical .= "DTSTART:" . $event->start_date->format('Ymd\THis\Z') . "\r\n";
                if ($event->end_date) {
                    $ical .= "DTEND:" . $event->end_date->format('Ymd\THis\Z') . "\r\n";
                }
            }

            $escapedTitle = str_replace(['\\', ';', ','], ['\\\\', '\;', '\,'], $event->title);

            $ical .= "SUMMARY:" . $escapedTitle . "\r\n";
            if ($event->description) {
                $desc = str_replace(['\\', ';', ',', "\r\n", "\n"], ['\\\\', '\;', '\,', '\n', '\n'], $event->description);
                $ical .= "DESCRIPTION:" . $desc . "\r\n";
            }
            $ical .= "CATEGORIES:" . $event->event_type . "\r\n";
            $ical .= "END:VEVENT\r\n";
        }

        $ical .= "END:VCALENDAR\r\n";

        return response($ical, 200, [
            'Content-Type'        => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="kalender-sekolah.ics"',
        ]);
    }

    private function authorizeOwn(CalendarEvent $event): void
    {
        abort_if($event->school_id !== $this->schoolId(), 403);
    }

    private function eventTypeColor(string $type): string
    {
        return match ($type) {
            'holiday'         => '#DC2626',
            'exam'            => '#7C3AED',
            'meeting'         => '#2563EB',
            'extracurricular' => '#059669',
            'other'           => '#64748B',
            default           => '#64748B',
        };
    }
}
