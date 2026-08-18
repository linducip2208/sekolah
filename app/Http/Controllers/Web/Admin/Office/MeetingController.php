<?php

namespace App\Http\Controllers\Web\Admin\Office;

use App\Http\Controllers\Controller;
use App\Models\AdminOffice\MeetingAgenda;
use App\Models\AdminOffice\MeetingMinutes;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MeetingController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $agendas = MeetingAgenda::where('school_id', $this->schoolId())
            ->with('organizer:id,name')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderByDesc('meeting_date')
            ->paginate(25)
            ->withQueryString();

        return view('school-admin.office.meetings', [
            'agendas' => $agendas,
            'staff'   => User::where('school_id', $this->schoolId())->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'        => 'required|string|max:300',
            'description'  => 'nullable|string',
            'meeting_date' => 'required|date',
            'start_time'   => 'nullable',
            'end_time'     => 'nullable',
            'location'     => 'nullable|string|max:200',
        ]);

        MeetingAgenda::create([
            'school_id'    => $this->schoolId(),
            'title'        => $data['title'],
            'description'  => $data['description'] ?? null,
            'meeting_date' => $data['meeting_date'],
            'start_time'   => $data['start_time'] ?? null,
            'end_time'     => $data['end_time'] ?? null,
            'location'     => $data['location'] ?? null,
            'organizer_id' => auth()->id(),
            'status'       => 'planned',
        ]);

        return back()->with('success', 'Agenda rapat ditambahkan.');
    }

    public function updateStatus(MeetingAgenda $agenda, Request $request): RedirectResponse
    {
        abort_unless($agenda->school_id === $this->schoolId(), 403);

        $data = $request->validate([
            'status' => 'required|in:planned,in_progress,completed,cancelled',
        ]);

        $agenda->update(['status' => $data['status']]);
        return back()->with('success', 'Status rapat diperbarui.');
    }

    public function destroy(MeetingAgenda $agenda): RedirectResponse
    {
        abort_unless($agenda->school_id === $this->schoolId(), 403);
        $agenda->delete();
        return back()->with('success', 'Agenda rapat dihapus.');
    }

    public function storeMinutes(Request $request, MeetingAgenda $agenda): RedirectResponse
    {
        abort_unless($agenda->school_id === $this->schoolId(), 403);

        $data = $request->validate([
            'content'          => 'required|string',
            'attendees'        => 'nullable|array',
            'decisions'        => 'nullable|array',
            'follow_up_items'  => 'nullable|array',
        ]);

        MeetingMinutes::create([
            'school_id'       => $this->schoolId(),
            'agenda_id'       => $agenda->id,
            'content'         => $data['content'],
            'attendees'       => $data['attendees'] ?? null,
            'decisions'       => $data['decisions'] ?? null,
            'follow_up_items' => $data['follow_up_items'] ?? null,
            'created_by'      => auth()->id(),
        ]);

        return back()->with('success', 'Notulensi rapat disimpan.');
    }
}
