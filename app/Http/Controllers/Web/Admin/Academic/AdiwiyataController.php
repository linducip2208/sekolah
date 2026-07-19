<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\AdiwiyataCategory;
use App\Models\Academic\AdiwiyataIndicator;
use App\Models\Academic\AdiwiyataEvidence;
use App\Models\Academic\AdiwiyataLevel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdiwiyataController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function dashboard(): View
    {
        $schoolId = $this->schoolId();
        $categories = AdiwiyataCategory::with(['indicators'])->orderBy('sort_order')->get();

        $progress = [];
        $totalScore = 0;
        $maxScore = 0;

        foreach ($categories as $cat) {
            $catMaxScore = 0;
            $catScore = 0;
            $catTotal = 0;
            $catDone = 0;

            foreach ($cat->indicators as $ind) {
                $catMaxScore += $ind->max_score;
                $catTotal++;

                $evidence = AdiwiyataEvidence::where('school_id', $schoolId)
                    ->where('adiwiyata_indicator_id', $ind->id)
                    ->whereIn('status', ['submitted', 'verified'])
                    ->latest()
                    ->first();

                if ($evidence) {
                    $catDone++;
                    $catScore += $evidence->score;
                }
            }

            $maxScore += $catMaxScore;
            $totalScore += $catScore;

            $progress[] = [
                'category' => $cat,
                'totalIndicators' => $catTotal,
                'completedIndicators' => $catDone,
                'score' => $catScore,
                'maxScore' => $catMaxScore,
                'percentage' => $catMaxScore > 0 ? round(($catScore / $catMaxScore) * 100, 1) : 0,
            ];
        }

        $overallPercentage = $maxScore > 0 ? round(($totalScore / $maxScore) * 100, 1) : 0;

        $predictedLevel = 'Calon';
        if ($overallPercentage >= 90) {
            $predictedLevel = 'Mandiri';
        } elseif ($overallPercentage >= 75) {
            $predictedLevel = 'Madya';
        } elseif ($overallPercentage >= 60) {
            $predictedLevel = 'Pratama';
        }

        return view('school-admin.academic.adiwiyata.dashboard', [
            'progress' => $progress,
            'totalScore' => $totalScore,
            'maxScore' => $maxScore,
            'overallPercentage' => $overallPercentage,
            'predictedLevel' => $predictedLevel,
            'levels' => AdiwiyataLevel::where('school_id', $schoolId)->orderByDesc('achieved_date')->get(),
        ]);
    }

    public function indicators(Request $request): View
    {
        $schoolId = $this->schoolId();
        $categories = AdiwiyataCategory::with(['indicators'])->orderBy('sort_order')->get();

        if ($request->filled('category')) {
            $categories = $categories->where('id', (int) $request->category);
        }

        $evidences = AdiwiyataEvidence::where('school_id', $schoolId)
            ->get()
            ->keyBy('adiwiyata_indicator_id');

        return view('school-admin.academic.adiwiyata.indicators', [
            'categories' => $categories,
            'evidences' => $evidences,
            'allCategories' => AdiwiyataCategory::orderBy('sort_order')->get(),
        ]);
    }

    public function evidence(AdiwiyataIndicator $indicator, Request $request): View
    {
        $schoolId = $this->schoolId();
        $existing = AdiwiyataEvidence::where('school_id', $schoolId)
            ->where('adiwiyata_indicator_id', $indicator->id)
            ->latest()
            ->first();

        $allEvidences = AdiwiyataEvidence::where('school_id', $schoolId)
            ->with('indicator.category')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('school-admin.academic.adiwiyata.evidence', [
            'indicator' => $indicator->load('category'),
            'existing' => $existing,
            'allEvidences' => $allEvidences,
        ]);
    }

    public function storeEvidence(Request $request, AdiwiyataIndicator $indicator): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'score' => 'required|integer|min:0|max:' . $indicator->max_score,
            'notes' => 'nullable|string',
        ]);

        $files = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $files[] = $file->store('adiwiyata/evidence', 'public');
            }
        }

        AdiwiyataEvidence::create([
            'school_id' => $this->schoolId(),
            'adiwiyata_indicator_id' => $indicator->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'file_path' => $files,
            'score' => $data['score'],
            'notes' => $data['notes'] ?? null,
            'status' => 'submitted',
        ]);

        return redirect()->route('admin.adiwiyata.indicators')->with('success', 'Bukti berhasil diunggah.');
    }

    public function verifyEvidence(AdiwiyataEvidence $evidence): RedirectResponse
    {
        abort_unless($evidence->school_id === $this->schoolId(), 403);

        $evidence->update([
            'status' => 'verified',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Bukti diverifikasi.');
    }

    public function rejectEvidence(AdiwiyataEvidence $evidence, Request $request): RedirectResponse
    {
        abort_unless($evidence->school_id === $this->schoolId(), 403);

        $evidence->update([
            'status' => 'rejected',
            'notes' => $request->notes ?? $evidence->notes,
        ]);

        return back()->with('success', 'Bukti ditolak.');
    }

    public function deleteEvidence(AdiwiyataEvidence $evidence): RedirectResponse
    {
        abort_unless($evidence->school_id === $this->schoolId(), 403);
        $evidence->delete();
        return back()->with('success', 'Bukti dihapus.');
    }

    public function storeLevel(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'achieved_level' => 'required|in:mandiri,madya,pratama,calon',
            'achieved_date' => 'required|date',
            'certificate_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $data['school_id'] = $this->schoolId();

        if ($request->hasFile('certificate_file')) {
            $data['certificate_file'] = $request->file('certificate_file')->store('adiwiyata/certificates', 'public');
        }

        AdiwiyataLevel::create($data);

        return redirect()->route('admin.adiwiyata.dashboard')->with('success', 'Level Adiwiyata dicatat.');
    }
}
