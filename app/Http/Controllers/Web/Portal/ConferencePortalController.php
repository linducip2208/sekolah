<?php

namespace App\Http\Controllers\Web\Portal;

use App\Http\Controllers\Controller;
use App\Models\Communication\ConferenceBooking;
use App\Models\Communication\ConferenceSession;
use App\Services\ConferenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConferencePortalController extends Controller
{
    public function __construct(private ConferenceService $conferenceService) {}

    public function index(): View
    {
        return view('parent-portal.conferences.index');
    }

    public function book(Request $request, ConferenceSession $session): RedirectResponse
    {
        $data = $request->validate([
            'student_id'   => 'required|exists:students,id',
            'booking_time' => 'required|string',
            'notes'        => 'nullable|string|max:2000',
        ]);

        try {
            $this->conferenceService->bookSlot(
                $session,
                $data['student_id'],
                auth()->id(),
                $data['booking_time'],
                $data['notes'] ?? null
            );

            return back()->with('success', 'Slot berhasil dibooking! Silakan hadir sesuai waktu yang dipilih.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function cancel(ConferenceBooking $booking): RedirectResponse
    {
        abort_unless($booking->parent_id === auth()->id(), 403);
        $this->conferenceService->cancelBooking($booking);
        return back()->with('success', 'Booking dibatalkan.');
    }
}
