<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Academic\CalendarEvent;
use Carbon\Carbon;
use Illuminate\Http\Response;

class CalendarController extends Controller
{
    public function ical(): Response
    {
        $schoolId = auth()->user()->school_id;

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

            $ical .= "SUMMARY:" . $this->escapeIcal($event->title) . "\r\n";
            if ($event->description) {
                $ical .= "DESCRIPTION:" . $this->escapeIcal($event->description) . "\r\n";
            }
            $ical .= "CATEGORIES:" . $event->event_type . "\r\n";
            $ical .= "END:VEVENT\r\n";
        }

        $ical .= "END:VCALENDAR\r\n";

        return response($ical, 200, [
            'Content-Type'        => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="kalender-sekolah.ics"',
        ]);
    }

    private function escapeIcal(string $text): string
    {
        $text = str_replace(['\\', ';', ',', "\r\n", "\n"], ['\\\\', '\;', '\,', '\n', '\n'], $text);
        $text = wordwrap($text, 70, "\r\n ", true);
        return $text;
    }
}
