<?php

namespace App\Http\Controllers\Web\Admin\Hr;

use App\Http\Controllers\Controller;
use App\Models\Academic\Staff;
use App\Models\Hr\KpiAppraisal;
use App\Models\Hr\KpiCriteria;
use App\Models\Hr\KpiGoal;
use App\Models\Hr\KpiTemplate;
use App\Services\Hr\KpiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KpiController extends Controller
{
    public function __construct(private KpiService $service) {}

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function templates(): View
    {
        $templates = KpiTemplate::where('school_id', $this->schoolId())->with('criteria')->orderBy('name')->get();
        return view('school-admin.hr.kpi-templates', compact('templates'));
    }

    public function storeTemplate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:200',
            'description' => 'nullable|string',
            'max_score'   => 'required|integer|min:1|max:1000',
        ]);

        $template = KpiTemplate::create(array_merge($data, ['school_id' => $this->schoolId()]));

        // Add default criteria
        $criteriaNames = ['Kualitas Kerja', 'Ketepatan Waktu', 'Kerjasama Tim', 'Inisiatif', 'Disiplin'];
        foreach ($criteriaNames as $i => $name) {
            KpiCriteria::create([
                'school_id'   => $this->schoolId(),
                'template_id' => $template->id,
                'name'        => $name,
                'weight'      => 1,
                'max_score'   => 10,
                'sort_order'  => $i,
            ]);
        }

        return back()->with('success', 'Template KPI dibuat dengan 5 kriteria default.');
    }

    public function deleteTemplate(KpiTemplate $template): RedirectResponse
    {
        abort_unless($template->school_id === $this->schoolId(), 403);
        $template->delete();
        return back()->with('success', 'Template dihapus.');
    }

    public function storeCriteria(Request $request, KpiTemplate $template): RedirectResponse
    {
        abort_unless($template->school_id === $this->schoolId(), 403);

        $data = $request->validate([
            'name'        => 'required|string|max:200',
            'description' => 'nullable|string',
            'weight'      => 'required|integer|min:1|max:100',
            'max_score'   => 'required|integer|min:1|max:100',
        ]);

        $maxOrder = KpiCriteria::where('template_id', $template->id)->max('sort_order') ?? 0;

        KpiCriteria::create(array_merge($data, [
            'school_id'   => $this->schoolId(),
            'template_id' => $template->id,
            'sort_order'  => $maxOrder + 1,
        ]));

        return back()->with('success', 'Kriteria ditambahkan.');
    }

    public function deleteCriteria(KpiCriteria $criteria): RedirectResponse
    {
        abort_unless($criteria->school_id === $this->schoolId(), 403);
        $criteria->delete();
        return back()->with('success', 'Kriteria dihapus.');
    }

    /* ============== APPRAISALS ============== */

    public function index(): View
    {
        $schoolId = $this->schoolId();
        $appraisals = KpiAppraisal::where('school_id', $schoolId)
            ->with(['staff.user:id,name', 'template', 'reviewer:id,name'])
            ->orderByDesc('created_at')
            ->get();

        $staffs = Staff::where('school_id', $schoolId)->with('user:id,name')->orderBy('id')->get();
        $templates = KpiTemplate::where('school_id', $schoolId)->where('is_active', true)->get();

        return view('school-admin.hr.kpi-appraisals', compact('appraisals', 'staffs', 'templates'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'staff_id'    => 'required|exists:staffs,id',
            'template_id' => 'required|exists:kpi_templates,id',
            'period'      => 'required|string|max:50',
        ]);

        $this->service->createAppraisal($this->schoolId(), [
            'staff_id'    => $data['staff_id'],
            'template_id' => $data['template_id'],
            'reviewer_id' => auth()->id(),
            'period'      => $data['period'],
        ]);

        return back()->with('success', 'Appraisal baru dibuat.');
    }

    public function show(KpiAppraisal $appraisal): View
    {
        abort_unless($appraisal->school_id === $this->schoolId(), 403);

        $criteria = KpiCriteria::where('template_id', $appraisal->template_id)
            ->orderBy('sort_order')
            ->get();

        $existingScores = $appraisal->scores->keyBy('criteria_id');

        return view('school-admin.hr.kpi-detail', [
            'appraisal'     => $appraisal,
            'criteria'      => $criteria,
            'existingScores' => $existingScores,
        ]);
    }

    public function saveScores(Request $request, KpiAppraisal $appraisal): RedirectResponse
    {
        abort_unless($appraisal->school_id === $this->schoolId(), 403);

        $scores = [];
        foreach ($request->input('scores', []) as $criteriaId => $scoreData) {
            $scores[] = [
                'criteria_id' => $criteriaId,
                'score'       => $scoreData['score'] ?? 0,
                'evidence'    => $scoreData['evidence'] ?? null,
            ];
        }

        $this->service->saveScores($appraisal, $scores);

        $appraisal->update(['reviewer_notes' => $request->input('reviewer_notes')]);

        return back()->with('success', 'Penilaian disimpan. Skor: ' . $appraisal->fresh()->total_score);
    }

    public function submit(KpiAppraisal $appraisal): RedirectResponse
    {
        abort_unless($appraisal->school_id === $this->schoolId(), 403);
        $this->service->submitAppraisal($appraisal);
        return back()->with('success', 'Appraisal disubmit.');
    }

    public function finalize(KpiAppraisal $appraisal, Request $request): RedirectResponse
    {
        abort_unless($appraisal->school_id === $this->schoolId(), 403);
        $this->service->finalizeAppraisal($appraisal, $request->input('reviewer_notes'));
        return back()->with('success', 'Appraisal difinalisasi. Grade: ' . $appraisal->fresh()->grade);
    }

    /* ============== GOALS ============== */

    public function goals(): View
    {
        $schoolId = $this->schoolId();
        $goals = KpiGoal::where('school_id', $schoolId)
            ->with('staff.user:id,name')
            ->orderByDesc('created_at')
            ->get();

        $staffs = Staff::where('school_id', $schoolId)->with('user:id,name')->orderBy('id')->get();

        return view('school-admin.hr.kpi-goals', compact('goals', 'staffs'));
    }

    public function storeGoal(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'staff_id'     => 'required|exists:staffs,id',
            'title'        => 'required|string|max:200',
            'description'  => 'nullable|string',
            'target_value' => 'nullable|string|max:100',
            'due_date'     => 'nullable|date',
        ]);

        $this->service->createGoal($this->schoolId(), $data);
        return back()->with('success', 'Goal ditambahkan.');
    }

    public function updateGoal(Request $request, KpiGoal $goal): RedirectResponse
    {
        abort_unless($goal->school_id === $this->schoolId(), 403);

        $data = $request->validate([
            'actual_value' => 'nullable|string|max:100',
            'status'       => 'required|in:in_progress,achieved,missed',
        ]);

        $this->service->updateGoal($goal, $data);
        return back()->with('success', 'Goal diperbarui.');
    }

    public function deleteGoal(KpiGoal $goal): RedirectResponse
    {
        abort_unless($goal->school_id === $this->schoolId(), 403);
        $goal->delete();
        return back()->with('success', 'Goal dihapus.');
    }
}
