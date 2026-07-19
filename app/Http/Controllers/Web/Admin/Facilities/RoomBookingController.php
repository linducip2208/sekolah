<?php

namespace App\Http\Controllers\Web\Admin\Facilities;

use App\Http\Controllers\Controller;
use App\Models\RoomBooking\BookableRoom;
use App\Models\RoomBooking\RoomBooking;
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
        return view('school-admin.facilities.rooms.index', compact('branding', 'rooms'));
    }

    public function storeRoom(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'room_type' => 'required|in:classroom,lab,library,hall,meeting,sports,other',
            'capacity' => 'nullable|integer|min:1',
            'floor' => 'nullable|string|max:50',
            'building' => 'nullable|string|max:100',
            'facilities' => 'nullable|array',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string|max:1000',
        ]);

        $data['facilities'] = $data['facilities'] ?? [];
        $data['is_active'] = $request->boolean('is_active', true);

        BookableRoom::create($data);

        return redirect()->route('admin.facilities.rooms.index')
            ->with('success', 'Ruangan berhasil ditambahkan.');
    }

    public function updateRoom(Request $request, BookableRoom $room): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'room_type' => 'required|in:classroom,lab,library,hall,meeting,sports,other',
            'capacity' => 'nullable|integer|min:1',
            'floor' => 'nullable|string|max:50',
            'building' => 'nullable|string|max:100',
            'facilities' => 'nullable|array',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string|max:1000',
        ]);

        $data['facilities'] = $data['facilities'] ?? [];
        $data['is_active'] = $request->boolean('is_active', true);

        $room->update($data);

        return redirect()->route('admin.facilities.rooms.index')
            ->with('success', 'Ruangan berhasil diperbarui.');
    }

    public function deleteRoom(BookableRoom $room): RedirectResponse
    {
        $room->delete();
        return redirect()->route('admin.facilities.rooms.index')
            ->with('success', 'Ruangan berhasil dihapus.');
    }

    public function uploadRoomPhoto(Request $request, BookableRoom $room): RedirectResponse
    {
        $request->validate([
            'photo' => 'required|image|max:5120|mimes:png,jpg,jpeg,webp',
        ]);

        $path = $request->file('photo')->store(
            'rooms/' . auth()->user()->school_id,
            'public'
        );

        $room->update(['photo_path' => $path]);

        return back()->with('success', 'Foto ruangan berhasil diunggah.');
    }

    public function calendar(): View
    {
        $branding = $this->branding->getForSchool(auth()->user()->school_id);
        $rooms = $this->service->roomsForSchool(auth()->user()->school_id);
        return view('school-admin.facilities.rooms.calendar', compact('branding', 'rooms'));
    }

    public function calendarFeed(Request $request): mixed
    {
        $start = $request->input('start');
        $end = $request->input('end');
        $roomFilter = $request->input('room_id');

        $query = RoomBooking::with(['room', 'user'])
            ->where('school_id', auth()->user()->school_id)
            ->whereBetween('date', [$start, $end])
            ->whereIn('status', ['pending', 'approved']);

        if ($roomFilter) {
            $query->where('bookable_room_id', $roomFilter);
        }

        $bookings = $query->get()->map(function ($booking) {
            $color = $booking->status === 'pending' ? '#EAB308' : '#16A34A';
            return [
                'id' => $booking->id,
                'title' => $booking->room->name . ': ' . $booking->title,
                'start' => $booking->date->format('Y-m-d') . 'T' . $booking->start_time,
                'end' => $booking->date->format('Y-m-d') . 'T' . $booking->end_time,
                'color' => $color,
                'textColor' => '#fff',
                'extendedProps' => [
                    'room' => $booking->room->name,
                    'user' => $booking->user->name ?? '',
                    'status' => $booking->status,
                    'purpose' => $booking->purpose,
                ],
            ];
        });

        return response()->json($bookings);
    }

    public function storeBooking(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'bookable_room_id' => 'required|exists:bookable_rooms,id',
            'title' => 'required|string|max:200',
            'purpose' => 'nullable|string|max:1000',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_recurring' => 'nullable|boolean',
            'recurring_pattern' => 'nullable|in:weekly,biweekly,monthly',
            'recurring_until' => 'nullable|date|after:date',
        ]);

        try {
            if ($request->boolean('is_recurring') && $request->input('recurring_until')) {
                $data['is_recurring'] = true;
                $baseBooking = array_merge($data, [
                    'user_id' => auth()->id(),
                    'is_recurring' => false,
                    'recurring_pattern' => null,
                    'recurring_until' => null,
                ]);
                $booking = $this->service->createBooking($baseBooking);
                $this->service->generateRecurringBookings($data);
            } else {
                $data['user_id'] = auth()->id();
                $data['is_recurring'] = false;
                $data['recurring_pattern'] = null;
                $data['recurring_until'] = null;
                $booking = $this->service->createBooking($data);
            }
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['conflict' => $e->getMessage()]);
        }

        return redirect()->route('admin.facilities.rooms.calendar')
            ->with('success', 'Booking berhasil dibuat.');
    }

    public function approve(int $bookingId): RedirectResponse
    {
        try {
            $this->service->approve($bookingId, auth()->id());
        } catch (\RuntimeException $e) {
            return back()->withErrors(['conflict' => $e->getMessage()]);
        }

        return back()->with('success', 'Booking disetujui.');
    }

    public function reject(int $bookingId, Request $request): RedirectResponse
    {
        $reason = $request->input('reason', 'Ditolak oleh admin.');
        $this->service->reject($bookingId, $reason);

        return back()->with('success', 'Booking ditolak.');
    }

    public function cancel(int $bookingId): RedirectResponse
    {
        try {
            $this->service->cancel($bookingId, auth()->id());
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Booking dibatalkan.');
    }

    public function approvals(): View
    {
        $branding = $this->branding->getForSchool(auth()->user()->school_id);
        $pending = $this->service->getPendingApprovals();
        return view('school-admin.facilities.rooms.approvals', compact('branding', 'pending'));
    }

    public function rules(BookableRoom $room): View
    {
        $branding = $this->branding->getForSchool(auth()->user()->school_id);
        $rules = $this->service->getRoomRules($room->id);
        return view('school-admin.facilities.rooms.rules', compact('branding', 'room', 'rules'));
    }

    public function saveRules(Request $request, BookableRoom $room): RedirectResponse
    {
        $data = $request->validate([
            'rules' => 'nullable|array',
            'rules.*.rule_type' => 'required|in:max_duration_hours,max_advance_days,min_gap_minutes,allowed_roles',
            'rules.*.rule_value' => 'required|string',
        ]);

        $this->service->saveRoomRules($room->id, $data['rules'] ?? []);

        return redirect()->route('admin.facilities.rooms.index')
            ->with('success', 'Aturan booking diperbarui.');
    }
}
