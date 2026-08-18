<?php

namespace App\Http\Controllers\Web\Admin\Facilities;

use App\Http\Controllers\Controller;
use App\Models\Academic\Student;
use App\Models\Facilities\Hostel;
use App\Models\Facilities\HostelAllocation;
use App\Models\Facilities\HostelAttendance;
use App\Models\Facilities\HostelBed;
use App\Models\Facilities\HostelGatePass;
use App\Models\Facilities\HostelMessMenu;
use App\Models\Facilities\HostelRoom;
use App\Services\Facilities\HostelService;
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
            'name'         => 'required|string|max:200',
            'type'         => 'required|in:boys,girls,mixed',
            'warden_name'  => 'nullable|string|max:200',
            'warden_phone' => 'nullable|string|max:30',
            'warden_email' => 'nullable|email|max:255',
            'description'  => 'nullable|string|max:1000',
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
            'rooms'  => HostelRoom::where('hostel_id', $hostel->id)->withCount('beds')->orderBy('room_no')->get(),
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

        $room = HostelRoom::create([
            'school_id'     => $this->schoolId(),
            'hostel_id'     => $hostel->id,
            'room_no'       => $data['room_no'],
            'capacity'      => $data['capacity'],
            'occupied'      => 0,
            'status'        => 'available',
            'fee_per_month' => (int)($data['fee_per_month_rupiah'] * 100),
        ]);

        for ($i = 1; $i <= $data['capacity']; $i++) {
            HostelBed::create([
                'school_id'     => $this->schoolId(),
                'hostel_room_id' => $room->id,
                'bed_no'        => $data['room_no'] . '-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'status'        => 'available',
            ]);
        }

        return back()->with('success', 'Kamar ditambahkan.');
    }

    public function deleteRoom(HostelRoom $room): RedirectResponse
    {
        abort_unless($room->hostel->school_id === $this->schoolId(), 403);
        $room->delete();
        return back()->with('success', 'Kamar dihapus.');
    }

    // ── Beds ──
    public function beds(HostelRoom $room): View
    {
        abort_unless($room->hostel->school_id === $this->schoolId(), 403);
        return view('school-admin.hostel.beds', [
            'room'  => $room->load('hostel'),
            'beds'  => HostelBed::where('hostel_room_id', $room->id)->with('student.user:id,name')->orderBy('bed_no')->get(),
            'students' => Student::where('school_id', $this->schoolId())->where('has_hostel', true)->with('user:id,name')->get(),
        ]);
    }

    public function storeBed(Request $request, HostelRoom $room): RedirectResponse
    {
        abort_unless($room->hostel->school_id === $this->schoolId(), 403);
        $data = $request->validate([
            'bed_no' => 'required|string|max:20',
        ]);
        HostelBed::create([
            'school_id'      => $this->schoolId(),
            'hostel_room_id' => $room->id,
            'bed_no'         => $data['bed_no'],
            'status'         => 'available',
        ]);
        return back()->with('success', 'Tempat tidur ditambahkan.');
    }

    public function allocateBed(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'hostel_bed_id' => 'required|exists:hostel_beds,id',
            'student_id'    => 'required|exists:students,id',
        ]);
        $bed = HostelBed::findOrFail($data['hostel_bed_id']);
        abort_unless($bed->hostel_room->hostel->school_id === $this->schoolId(), 403);

        $service = new HostelService();
        $service->allocateBed($data['hostel_bed_id'], $data['student_id']);
        return back()->with('success', 'Siswa dialokasikan ke tempat tidur.');
    }

    public function deallocateBed(HostelBed $bed): RedirectResponse
    {
        abort_unless($bed->hostel_room->hostel->school_id === $this->schoolId(), 403);
        $service = new HostelService();
        $service->deallocateBed($bed->id);
        return back()->with('success', 'Alokasi tempat tidur dilepas.');
    }

    // ── Checkout ──
    public function checkout(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);
        $student = Student::findOrFail($data['student_id']);
        abort_unless($student->school_id === $this->schoolId(), 403);

        $service = new HostelService();
        $service->checkout($data['student_id']);
        return back()->with('success', 'Siswa checkout dari asrama.');
    }

    // ── Allocations ──
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
        $room = HostelRoom::findOrFail($data['hostel_room_id']);
        abort_unless($room->hostel->school_id === $this->schoolId(), 403);

        $service = new HostelService();
        $service->allocate($data['student_id'], $data['hostel_room_id'], $data['from_date']);
        return back()->with('success', 'Alokasi kamar dibuat.');
    }

    // ── Gate Pass ──
    public function gatePasses(): View
    {
        return view('school-admin.hostel.gate-passes', [
            'passes' => HostelGatePass::where('school_id', $this->schoolId())
                ->with(['student.user:id,name', 'requestedBy:id,name', 'approvedBy:id,name'])
                ->orderByDesc('created_at')->paginate(25),
            'students' => Student::where('school_id', $this->schoolId())->with('user:id,name')->get(),
        ]);
    }

    public function storeGatePass(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id'      => 'required|exists:students,id',
            'pass_type'       => 'required|in:in,out',
            'purpose'         => 'required|string|max:500',
            'visitor_name'    => 'nullable|string|max:200',
            'visitor_phone'   => 'nullable|string|max:30',
            'expected_return' => 'nullable|date',
            'note'            => 'nullable|string|max:1000',
        ]);
        HostelGatePass::create([
            'school_id'       => $this->schoolId(),
            'student_id'      => $data['student_id'],
            'pass_type'       => $data['pass_type'],
            'purpose'         => $data['purpose'],
            'visitor_name'    => $data['visitor_name'] ?? null,
            'visitor_phone'   => $data['visitor_phone'] ?? null,
            'requested_by'    => auth()->id(),
            'status'          => 'pending',
            'out_time'        => $data['pass_type'] === 'out' ? now() : null,
            'expected_return' => $data['expected_return'] ?? null,
            'note'            => $data['note'] ?? null,
        ]);
        return back()->with('success', 'Gate pass dibuat.');
    }

    public function approveGatePass(HostelGatePass $pass): RedirectResponse
    {
        abort_unless($pass->school_id === $this->schoolId(), 403);
        $pass->update(['status' => 'approved', 'approved_by' => auth()->id()]);
        return back()->with('success', 'Gate pass disetujui.');
    }

    public function rejectGatePass(HostelGatePass $pass): RedirectResponse
    {
        abort_unless($pass->school_id === $this->schoolId(), 403);
        $pass->update(['status' => 'rejected', 'approved_by' => auth()->id()]);
        return back()->with('success', 'Gate pass ditolak.');
    }

    public function completeGatePass(HostelGatePass $pass): RedirectResponse
    {
        abort_unless($pass->school_id === $this->schoolId(), 403);
        $pass->update(['status' => 'completed', 'actual_return' => now()]);
        return back()->with('success', 'Gate pass selesai.');
    }

    // ── Attendance ──
    public function attendances(): View
    {
        return view('school-admin.hostel.attendances', [
            'attendances' => HostelAttendance::where('school_id', $this->schoolId())
                ->with(['student.user:id,name', 'room:hostel_room_id,room_no', 'notedBy:id,name'])
                ->orderByDesc('date')->orderByDesc('created_at')->paginate(25),
            'rooms' => HostelRoom::whereHas('hostel', fn ($q) => $q->where('school_id', $this->schoolId()))->get(),
        ]);
    }

    public function storeAttendance(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id'      => 'required|exists:students,id',
            'hostel_room_id'  => 'required|exists:hostel_rooms,id',
            'date'            => 'required|date',
            'status'          => 'required|in:present,absent,late,permission',
            'check_in_time'   => 'nullable|date_format:H:i',
            'check_out_time'  => 'nullable|date_format:H:i',
            'note'            => 'nullable|string|max:1000',
        ]);

        $room = HostelRoom::findOrFail($data['hostel_room_id']);
        abort_unless($room->hostel->school_id === $this->schoolId(), 403);

        HostelAttendance::updateOrCreate(
            ['school_id' => $this->schoolId(), 'student_id' => $data['student_id'], 'date' => $data['date']],
            [
                'hostel_room_id' => $data['hostel_room_id'],
                'status'         => $data['status'],
                'check_in_time'  => $data['check_in_time'] ?? null,
                'check_out_time' => $data['check_out_time'] ?? null,
                'noted_by'       => auth()->id(),
                'note'           => $data['note'] ?? null,
            ]
        );

        return back()->with('success', 'Absensi asrama dicatat.');
    }

    // ── Mess Menu ──
    public function messMenus(): View
    {
        return view('school-admin.hostel.mess-menus', [
            'hostels' => Hostel::where('school_id', $this->schoolId())->orderBy('name')->get(),
            'menus'   => HostelMessMenu::where('school_id', $this->schoolId())
                ->with('hostel:id,name')
                ->orderBy('hostel_id')->orderBy('day_of_week')->orderBy('meal_type')->get(),
        ]);
    }

    public function storeMessMenu(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'hostel_id'         => 'required|exists:hostels,id',
            'day_of_week'       => 'required|integer|between:0,6',
            'meal_type'         => 'required|in:breakfast,lunch,dinner',
            'menu_description'  => 'required|string|max:1000',
        ]);
        $hostel = Hostel::findOrFail($data['hostel_id']);
        abort_unless($hostel->school_id === $this->schoolId(), 403);

        HostelMessMenu::updateOrCreate(
            ['school_id' => $this->schoolId(), 'hostel_id' => $data['hostel_id'], 'day_of_week' => $data['day_of_week'], 'meal_type' => $data['meal_type']],
            ['menu_description' => $data['menu_description'], 'is_active' => true]
        );
        return back()->with('success', 'Menu mess disimpan.');
    }

    public function deleteMessMenu(HostelMessMenu $menu): RedirectResponse
    {
        abort_unless($menu->school_id === $this->schoolId(), 403);
        $menu->delete();
        return back()->with('success', 'Menu mess dihapus.');
    }
}
