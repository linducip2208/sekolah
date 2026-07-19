<?php

namespace App\Http\Controllers\Web\Teacher;

use App\Http\Controllers\Controller;
use App\Services\Branding\BrandingService;
use App\Services\RoomBooking\RoomBookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomBookingController extends Controller
{
    public function __construct(
        private BrandingService $branding,
        private RoomBookingService $service,
    ) {}

    public function index(): View
    {
        $branding = $this->branding->getForSchool(auth()->user()->school_id);
        $rooms = $this->service->roomsForSchool(auth()->user()->school_id);
        $myBookings = $this->service->getMyBookings(auth()->id());
        return view('teacher-portal.room-booking', compact('branding', 'rooms', 'myBookings'));
    }

    public function calendarFeed(Request $request): mixed
    {
        $start = $request->input('start');
        $end = $request->input('end');

        $query = \App\Models\RoomBooking\RoomBooking::with('room')
            ->where('school_id', auth()->user()->school_id)
            ->whereBetween('date', [$start, $end])
            ->whereIn('status', ['pending', 'approved']);

        $bookings = $query->get()->map(function ($booking) {
            $color = $booking->status === 'pending' ? '#EAB308' : '#16A34A';
            if ($booking->user_id === auth()->id()) {
                $color = '#3B82F6';
            }
            return [
                'id' => $booking->id,
                'title' => $booking->room->name . ': ' . $booking->title,
                'start' => $booking->date->format('Y-m-d') . 'T' . $booking->start_time,
                'end' => $booking->date->format('Y-m-d') . 'T' . $booking->end_time,
                'color' => $color,
                'textColor' => '#fff',
                'extendedProps' => [
                    'room' => $booking->room->name,
                    'status' => $booking->status,
                    'mine' => $booking->user_id === auth()->id(),
                ],
            ];
        });

        return response()->json($bookings);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'bookable_room_id' => 'required|exists:bookable_rooms,id',
            'title' => 'required|string|max:200',
            'purpose' => 'nullable|string|max:1000',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $data['user_id'] = auth()->id();

        try {
            $this->service->createBooking($data);
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['conflict' => $e->getMessage()]);
        }

        return redirect()->route('teacher.room-booking')
            ->with('success', 'Booking berhasil diajukan. Menunggu persetujuan admin.');
    }

    public function cancel(int $bookingId): RedirectResponse
    {
        try {
            $this->service->cancel($bookingId, auth()->id());
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('teacher.room-booking')
            ->with('success', 'Booking dibatalkan.');
    }
}
