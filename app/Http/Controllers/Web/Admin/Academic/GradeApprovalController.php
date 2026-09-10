<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\ReportCard;
use App\Models\Academic\Semester;
use App\Services\Academic\GradeApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GradeApprovalController extends Controller
{
    public function __construct(private GradeApprovalService $service) {}

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('marks.view'), 403);
        $schoolId = $this->schoolId();

        $cards = ReportCard::where('school_id', $schoolId)
            ->with(['student.user', 'semester'])
            ->when($request->semester_id, fn ($q) => $q->where('semester_id', $request->semester_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $semesters = Semester::where('school_id', $schoolId)->orderByDesc('start_date')->get();

        return view('school-admin.grades.approval', compact('cards', 'semesters'));
    }

    public function submit(ReportCard $card): RedirectResponse
    {
        abort_unless(auth()->user()->can('marks.manage'), 403);
        abort_unless($card->school_id === $this->schoolId(), 403);
        $this->service->submit($card);

        return back()->with('success', 'Rapor diajukan untuk disetujui.');
    }

    public function approve(ReportCard $card): RedirectResponse
    {
        abort_unless(auth()->user()->can('marks.approve'), 403);
        abort_unless($card->school_id === $this->schoolId(), 403);
        $this->service->approve($card, auth()->id());

        return back()->with('success', 'Rapor disetujui.');
    }

    public function reject(ReportCard $card): RedirectResponse
    {
        abort_unless(auth()->user()->can('marks.approve'), 403);
        abort_unless($card->school_id === $this->schoolId(), 403);
        $this->service->reject($card);

        return back()->with('success', 'Rapor ditolak (kembali ke draft).');
    }

    public function lock(ReportCard $card): RedirectResponse
    {
        abort_unless(auth()->user()->can('marks.lock'), 403);
        abort_unless($card->school_id === $this->schoolId(), 403);
        $this->service->lock($card);

        return back()->with('success', 'Rapor dikunci.');
    }

    public function reopen(ReportCard $card, Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('marks.reopen'), 403);
        abort_unless($card->school_id === $this->schoolId(), 403);
        $data = $request->validate(['reason' => 'required|string|max:2000']);
        $this->service->reopen($card, $data['reason']);

        return back()->with('success', 'Rapor dibuka kembali dan wajib diajukan ulang.');
    }
}
