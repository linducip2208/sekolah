<?php

namespace App\Http\Controllers\Web\Admin\Workflow;

use App\Http\Controllers\Controller;
use App\Models\Workflow\WorkflowRequest;
use App\Services\Workflow\WorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkflowController extends Controller
{
    public function __construct(private WorkflowService $workflow) {}

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $items = WorkflowRequest::where('school_id', $this->schoolId())
            ->with(['requester:id,name', 'approver:id,name'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->type, fn ($q) => $q->where('type', $request->type))
            ->latest()->paginate(20)->withQueryString();

        return view('school-admin.workflow.index', [
            'items'        => $items,
            'types'        => WorkflowRequest::TYPES,
            'statuses'     => WorkflowRequest::STATUSES,
            'pendingCount' => $this->workflow->pendingCount($this->schoolId()),
        ]);
    }

    public function create(): View
    {
        return view('school-admin.workflow.create', ['types' => WorkflowRequest::TYPES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type'        => 'required|in:'.implode(',', array_keys(WorkflowRequest::TYPES)),
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
        ]);

        $this->workflow->create($this->schoolId(), auth()->id(), $data);

        return redirect()->route('admin.workflow.index')->with('success', 'Permintaan persetujuan berhasil diajukan.');
    }

    public function show(WorkflowRequest $workflowRequest): View
    {
        abort_unless($workflowRequest->school_id === $this->schoolId(), 403);

        return view('school-admin.workflow.show', [
            'item'  => $workflowRequest->load(['requester:id,name', 'approver:id,name']),
            'types' => WorkflowRequest::TYPES,
        ]);
    }

    public function approve(WorkflowRequest $workflowRequest, Request $request): RedirectResponse
    {
        abort_unless($workflowRequest->school_id === $this->schoolId(), 403);
        abort_if(in_array($workflowRequest->status, ['approved', 'rejected'], true), 409, 'Permintaan sudah diputuskan.');

        $this->workflow->approve($workflowRequest, $request->input('note'));

        return back()->with('success', 'Permintaan disetujui.');
    }

    public function reject(WorkflowRequest $workflowRequest, Request $request): RedirectResponse
    {
        abort_unless($workflowRequest->school_id === $this->schoolId(), 403);
        abort_if(in_array($workflowRequest->status, ['approved', 'rejected'], true), 409, 'Permintaan sudah diputuskan.');

        $data = $request->validate(['note' => 'required|string|max:2000']);

        $this->workflow->reject($workflowRequest, $data['note']);

        return back()->with('success', 'Permintaan ditolak.');
    }
}
