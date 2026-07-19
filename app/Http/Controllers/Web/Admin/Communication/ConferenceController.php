<?php

namespace App\Http\Controllers\Web\Admin\Communication;

use App\Http\Controllers\Controller;
use App\Models\Academic\Student;
use App\Models\Communication\ConferenceBooking;
use App\Models\Communication\ConferenceSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConferenceController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(): View
    {
        $sessions = ConferenceSession::where('school_id', $this->schoolId())
            ->with('creator:id,name')
            ->withCount(['bookings as confirmed_count' => fn($q) => $q->whereNotIn('status', ['cancelled'])])
            ->orderByDesc('date')
            ->paginate(15);

        return view('school-admin.conferences.index', compact('sessions'));
    }

    public function create(): View
    {
        return view('school-admin.conferences.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string|max:5000',
            'conference_type'  => 'required|in:individual,group',
            'date'             => 'required|date|after_or_equal:today',
            'start_time'       => 'required|date_format:H:i',
            'end_time'         => 'required|date_format:H:i|after:start_time',
            'duration_minutes' => 'required|integer|min:5|max:120',
            'max_bookings'     => 'nullable|integer|min:1',
            'location'         => 'required|in:physical,online',
            'location_detail'  => 'nullable|string|max:255',
            'meeting_link'     => 'nullable|url|max:500',
            'is_published'     => 'nullable|boolean',
        ]);

        ConferenceSession::create([
            'school_id'        => $this->schoolId(),
            'created_by'       => auth()->id(),
            'title'            => $data['title'],
            'description'      => $data['description'] ?? null,
            'conference_type'  => $data['conference_type'],
            'date'             => $data['date'],
            'start_time'       => $data['start_time'],
            'end_time'         => $data['end_time'],
            'duration_minutes' => $data['duration_minutes'],
            'max_bookings'     => $data['max_bookings'] ?? null,
            'location'         => $data['location'],
            'location_detail'  => $data['location_detail'] ?? null,
            'meeting_link'     => $data['meeting_link'] ?? null,
            'is_published'     => (bool) ($data['is_published'] ?? false),
        ]);

        return redirect()->route('admin.conferences.index')->with('success', 'Sesi konferensi berhasil dibuat.');
    }

    public function edit(ConferenceSession $session): View
    {
        $this->authorizeOwn($session);
        return view('school-admin.conferences.edit', compact('session'));
    }

    public function update(Request $request, ConferenceSession $session): RedirectResponse
    {
        $this->authorizeOwn($session);
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string|max:5000',
            'conference_type'  => 'required|in:individual,group',
            'date'             => 'required|date',
            'start_time'       => 'required|date_format:H:i',
            'end_time'         => 'required|date_format:H:i|after:start_time',
            'duration_minutes' => 'required|integer|min:5|max:120',
            'max_bookings'     => 'nullable|integer|min:1',
            'location'         => 'required|in:physical,online',
            'location_detail'  => 'nullable|string|max:255',
            'meeting_link'     => 'nullable|url|max:500',
            'is_published'     => 'nullable|boolean',
        ]);

        $session->update([
            'title'            => $data['title'],
            'description'      => $data['description'] ?? null,
            'conference_type'  => $data['conference_type'],
            'date'             => $data['date'],
            'start_time'       => $data['start_time'],
            'end_time'         => $data['end_time'],
            'duration_minutes' => $data['duration_minutes'],
            'max_bookings'     => $data['max_bookings'] ?? null,
            'location'         => $data['location'],
            'location_detail'  => $data['location_detail'] ?? null,
            'meeting_link'     => $data['meeting_link'] ?? null,
            'is_published'     => (bool) ($data['is_published'] ?? false),
        ]);

        return redirect()->route('admin.conferences.index')->with('success', 'Sesi konferensi diperbarui.');
    }

    public function destroy(ConferenceSession $session): RedirectResponse
    {
        $this->authorizeOwn($session);
        $session->delete();
        return back()->with('success', 'Sesi konferensi dihapus.');
    }

    public function bookings(ConferenceSession $session): View
    {
        $this->authorizeOwn($session);
        $bookings = ConferenceBooking::where('conference_session_id', $session->id)
            ->with(['student.user', 'parent'])
            ->orderBy('booking_time')
            ->get();

        $students = Student::where('school_id', $this->schoolId())
            ->with('user:id,name')
            ->orderBy('admission_no')
            ->get();

        return view('school-admin.conferences.bookings', compact('session', 'bookings', 'students'));
    }

    public function confirmBooking(ConferenceBooking $booking): RedirectResponse
    {
        $this->authorizeBooking($booking);
        $booking->update(['status' => 'confirmed']);
        return back()->with('success', 'Booking dikonfirmasi.');
    }

    public function cancelBooking(ConferenceBooking $booking): RedirectResponse
    {
        $this->authorizeBooking($booking);
        $booking->update(['status' => 'cancelled', 'cancelled_at' => now()]);
        return back()->with('success', 'Booking dibatalkan.');
    }

    public function completeBooking(ConferenceBooking $booking): RedirectResponse
    {
        $this->authorizeBooking($booking);
        $booking->update(['status' => 'completed']);
        return back()->with('success', 'Booking ditandai selesai.');
    }

    public function updateBookingNotes(Request $request, ConferenceBooking $booking): RedirectResponse
    {
        $this->authorizeBooking($booking);
        $data = $request->validate(['teacher_notes' => 'nullable|string|max:5000']);
        $booking->update(['teacher_notes' => $data['teacher_notes'] ?? null]);
        return back()->with('success', 'Catatan diperbarui.');
    }

    public function printAttendance(ConferenceSession $session): View
    {
        $this->authorizeOwn($session);
        $bookings = ConferenceBooking::where('conference_session_id', $session->id)
            ->whereNotIn('status', ['cancelled'])
            ->with(['student.user', 'parent'])
            ->orderBy('booking_time')
            ->get();

        return view('school-admin.conferences.attendance-sheet', compact('session', 'bookings'));
    }

    private function authorizeOwn(ConferenceSession $session): void
    {
        abort_unless($session->school_id === $this->schoolId(), 403);
    }

    private function authorizeBooking(ConferenceBooking $booking): void
    {
        abort_unless($booking->school_id === $this->schoolId(), 403);
    }
}
