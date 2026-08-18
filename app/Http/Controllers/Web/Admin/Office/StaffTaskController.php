<?php

namespace App\Http\Controllers\Web\Admin\Office;

use App\Http\Controllers\Controller;
use App\Models\AdminOffice\StaffTask;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffTaskController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $statusFilter = $request->input('status');
        $assigneeFilter = $request->input('assigned_to');

        $tasks = StaffTask::where('school_id', $this->schoolId())
            ->with(['assignee:id,name', 'assigner:id,name'])
            ->when($statusFilter, fn($q) => $q->where('status', $statusFilter))
            ->when($assigneeFilter, fn($q) => $q->where('assigned_to', $assigneeFilter))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        $stats = [
            'todo'        => StaffTask::where('school_id', $this->schoolId())->where('status', 'todo')->count(),
            'in_progress' => StaffTask::where('school_id', $this->schoolId())->where('status', 'in_progress')->count(),
            'done'        => StaffTask::where('school_id', $this->schoolId())->where('status', 'done')->count(),
            'overdue'     => StaffTask::where('school_id', $this->schoolId())->where('status', 'overdue')->count(),
        ];

        return view('school-admin.office.tasks', [
            'tasks'  => $tasks,
            'stats'  => $stats,
            'staff'  => User::where('school_id', $this->schoolId())->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'        => 'required|string|max:300',
            'description'  => 'nullable|string',
            'assigned_to'  => 'required|exists:users,id',
            'due_date'     => 'nullable|date',
            'priority'     => 'required|in:low,medium,high,urgent',
        ]);

        StaffTask::create([
            'school_id'    => $this->schoolId(),
            'title'        => $data['title'],
            'description'  => $data['description'] ?? null,
            'assigned_to'  => $data['assigned_to'],
            'assigned_by'  => auth()->id(),
            'due_date'     => $data['due_date'] ?? null,
            'priority'     => $data['priority'],
            'status'       => 'todo',
        ]);

        return back()->with('success', 'Tugas ditambahkan.');
    }

    public function updateStatus(StaffTask $task, Request $request): RedirectResponse
    {
        abort_unless($task->school_id === $this->schoolId(), 403);

        $data = $request->validate([
            'status' => 'required|in:todo,in_progress,done,overdue',
        ]);

        $update = ['status' => $data['status']];
        if ($data['status'] === 'done') {
            $update['completed_at'] = now();
        }

        $task->update($update);
        return back()->with('success', 'Status tugas diperbarui.');
    }

    public function destroy(StaffTask $task): RedirectResponse
    {
        abort_unless($task->school_id === $this->schoolId(), 403);
        $task->delete();
        return back()->with('success', 'Tugas dihapus.');
    }
}
