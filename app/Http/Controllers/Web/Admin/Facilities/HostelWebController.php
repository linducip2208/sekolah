<?php

namespace App\Http\Controllers\Web\Admin\Facilities;

use App\Http\Controllers\Controller;
use App\Models\Academic\Student;
use App\Models\Facilities\Hostel;
use App\Models\Facilities\HostelAllocation;
use App\Models\Facilities\HostelRoom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HostelWebController extends Controller
{
    private function schoolId(): int { return auth()->user()->school_id; }
    private function authorizeOwn($model): void { abort_unless($model->school_id === $this->schoolId(), 403); }

    public function hostels(): View
    {
        return view('school-admin.hostel.list', [
            'hostels' => Hostel::where('school_id', $this->schoolId())->withCount('rooms')->orderBy('name')->get(),
        ]);
    }

    public function storeHostel(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:200',
            'type'        => 'required|in:boys,girls,mixed',
            'warden_name' => 'nullable|string|max:200',
        ]);
        $data['school_id'] = $this->schoolId();
        Hostel::create($data);
        return back()->with('success', 'Asrama ditambahkan.');
    }

    public function deleteHostel(Hostel $hostel): RedirectResponse
    {
        $this->authorizeOwn($hostel);
        $hostel->delete();
        return back()->with('success', 'Asrama dihapus.');
    }

    public function rooms(Hostel $hostel): View
    {
        $this->authorizeOwn($hostel);
        return view('school-admin.hostel.rooms', [
            'hostel' => $hostel,
            'rooms'  => HostelRoom::where('hostel_id', $hostel->id)->orderBy('room_no')->get(),
        ]);
    }

    public function storeRoom(Request $request, Hostel $hostel): RedirectResponse
    {
        $this->authorizeOwn($hostel);
        $data = $request->validate([
            'room_no'              => 'required|string|max:50',
            'capacity'             => 'required|integer|min:1|max:20',
            'fee_per_month_rupiah' => 'required|numeric|min:0',
        ]);
        HostelRoom::create([
            'hostel_id'     => $hostel->id,
            'room_no'       => $data['room_no'],
            'capacity'      => $data['capacity'],
            'occupied'      => 0,
            'status'        => 'available',
            'fee_per_month' => (int)($data['fee_per_month_rupiah'] * 100),
        ]);
        return back()->with('success', 'Kamar ditambahkan.');
    }

    public function deleteRoom(HostelRoom $room): RedirectResponse
    {
        // Hostel rooms don't have school_id directly — verify via parent hostel
        abort_unless($room->hostel->school_id === $this->schoolId(), 403);
        $room->delete();
        return back()->with('success', 'Kamar dihapus.');
    }

    public function allocations(): View
    {
        return view('school-admin.hostel.allocations', [
            'allocations' => HostelAllocation::where('school_id', $this->schoolId())
                ->with(['student.user:id,name', 'room.hostel'])
                ->orderByDesc('from_date')->paginate(25),
            'students' => Student::where('school_id', $this->schoolId())->where('has_hostel', true)->with('user:id,name')->get(),
            'rooms'    => HostelRoom::whereHas('hostel', fn ($q) => $q->where('school_id', $this->schoolId()))
                ->whereColumn('occupied', '<', 'capacity')->with('hostel')->get(),
        ]);
    }

    public function storeAllocation(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id'     => 'required|exists:students,id',
            'hostel_room_id' => 'required|exists:hostel_rooms,id',
            'from_date'      => 'required|date',
            'to_date'        => 'nullable|date|after:from_date',
        ]);
        HostelAllocation::create([
            'school_id'      => $this->schoolId(),
            'student_id'     => $data['student_id'],
            'hostel_room_id' => $data['hostel_room_id'],
            'from_date'      => $data['from_date'],
            'to_date'        => $data['to_date'] ?? null,
            'is_active'      => true,
        ]);
        HostelRoom::where('id', $data['hostel_room_id'])->increment('occupied');
        return back()->with('success', 'Alokasi kamar dibuat.');
    }
}
