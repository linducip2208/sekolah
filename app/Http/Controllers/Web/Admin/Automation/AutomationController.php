<?php

namespace App\Http\Controllers\Web\Admin\Automation;

use App\Http\Controllers\Controller;
use App\Models\Automation\AutomationRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AutomationController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(): View
    {
        $rules = AutomationRule::where('school_id', $this->schoolId())->orderBy('name')->get();

        return view('school-admin.automation.rules', ['rules' => $rules]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'          => 'required|string|max:200',
            'trigger_type'  => 'required|in:fee_due_soon,fee_overdue,student_absent_streak,birthday',
            'action_type'   => 'required|in:notify,email',
            'title'         => 'nullable|string|max:200',
            'body'          => 'nullable|string|max:1000',
            'is_active'     => 'nullable|boolean',
        ]);

        AutomationRule::create([
            'school_id'     => $this->schoolId(),
            'name'          => $data['name'],
            'trigger_type'  => $data['trigger_type'],
            'action_type'   => $data['action_type'],
            'action_config' => [
                'title' => $data['title'] ?? '',
                'body'  => $data['body'] ?? '',
            ],
            'config'        => [],
            'is_active'     => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Aturan otomasi ditambahkan.');
    }

    public function toggle(AutomationRule $rule): RedirectResponse
    {
        abort_unless($rule->school_id === $this->schoolId(), 403);

        $rule->update(['is_active' => !$rule->is_active]);

        return back()->with('success', 'Aturan otomasi diperbarui.');
    }

    public function destroy(AutomationRule $rule): RedirectResponse
    {
        abort_unless($rule->school_id === $this->schoolId(), 403);

        $rule->delete();

        return back()->with('success', 'Aturan otomasi dihapus.');
    }
}
