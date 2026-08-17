<?php

namespace App\Http\Controllers\Web\Admin\Audit;

use App\Http\Controllers\Controller;
use App\Models\Audit\AuditFinding;
use App\Models\Audit\InternalAudit;
use App\Services\Audit\InternalAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InternalAuditController extends Controller
{
    public function __construct(private InternalAuditService $service) {}

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    private function authorizeOwn($model): void
    {
        abort_unless($model->school_id === $this->schoolId(), 403);
    }

    public function index(): View
    {
        $audits = InternalAudit::where('school_id', $this->schoolId())
            ->withCount('findings')
            ->orderByDesc('id')
            ->get();

        return view('school-admin.audit.index', [
            'audits'  => $audits,
            'summary' => $this->service->summary($this->schoolId()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'    => 'required|string|max:200',
            'period'   => 'nullable|string|max:100',
            'auditor'  => 'nullable|string|max:200',
            'notes'    => 'nullable|string',
        ]);

        InternalAudit::create(array_merge($data, ['school_id' => $this->schoolId()]));

        return back()->with('success', 'Audit dibuat.');
    }

    public function show(InternalAudit $audit): View
    {
        $this->authorizeOwn($audit);

        $audit->load('findings');

        return view('school-admin.audit.show', ['audit' => $audit]);
    }

    public function start(InternalAudit $audit): RedirectResponse
    {
        $this->authorizeOwn($audit);
        $this->service->start($audit);
        return back()->with('success', 'Audit dimulai.');
    }

    public function complete(InternalAudit $audit): RedirectResponse
    {
        $this->authorizeOwn($audit);
        $this->service->complete($audit);
        return back()->with('success', 'Audit diselesaikan.');
    }

    public function storeFinding(Request $request, InternalAudit $audit): RedirectResponse
    {
        $this->authorizeOwn($audit);

        $data = $request->validate([
            'area'        => 'required|string|max:200',
            'description' => 'required|string',
            'severity'    => 'required|in:low,medium,high',
            'action'      => 'nullable|string',
            'due_date'    => 'nullable|date',
        ]);

        AuditFinding::create(array_merge($data, [
            'school_id'        => $this->schoolId(),
            'internal_audit_id'=> $audit->id,
            'status'           => 'open',
        ]));

        return back()->with('success', 'Temuan ditambahkan.');
    }

    public function updateFindingStatus(Request $request, AuditFinding $finding): RedirectResponse
    {
        $this->authorizeOwn($finding);

        $data = $request->validate(['status' => 'required|in:open,in_progress,resolved']);

        if ($data['status'] === 'resolved') {
            $this->service->resolve($finding);
        } else {
            $finding->update(['status' => $data['status'], 'resolved_at' => null]);
        }

        return back()->with('success', 'Status temuan diperbarui.');
    }

    public function deleteFinding(AuditFinding $finding): RedirectResponse
    {
        $this->authorizeOwn($finding);
        $finding->delete();
        return back()->with('success', 'Temuan dihapus.');
    }
}
