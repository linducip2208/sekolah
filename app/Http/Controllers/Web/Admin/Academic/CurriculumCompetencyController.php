<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\ClassRoom;
use App\Models\Academic\Subject;
use App\Models\Curriculum\CurriculumCompetency;
use App\Models\Curriculum\CurriculumFramework;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CurriculumCompetencyController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $schoolId = $this->schoolId();

        $competencies = CurriculumCompetency::where('school_id', $schoolId)
            ->with(['subject:id,name', 'classRoom:id,name', 'parent:id,code'])
            ->when($request->level_type, fn ($q) => $q->where('level_type', $request->level_type))
            ->when($request->subject_id, fn ($q) => $q->where('subject_id', $request->subject_id))
            ->orderBy('code')
            ->paginate(40)
            ->withQueryString();

        return view('school-admin.curriculum.competencies', [
            'competencies' => $competencies,
            'frameworks'   => CurriculumFramework::where('school_id', $schoolId)->orderBy('name')->get(),
            'subjects'     => Subject::where('school_id', $schoolId)->orderBy('name')->get(),
            'classRooms'   => ClassRoom::where('school_id', $schoolId)->orderBy('name')->get(),
            'parents'      => CurriculumCompetency::where('school_id', $schoolId)->orderBy('code')->get(['id', 'code', 'description']),
            'levels'       => CurriculumCompetency::LEVELS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'curriculum_framework_id' => 'required|exists:curriculum_frameworks,id',
            'subject_id'              => 'required|exists:subjects,id',
            'class_room_id'           => 'required|exists:class_rooms,id',
            'code'                    => 'required|string|max:30',
            'description'             => 'required|string',
            'level_type'              => 'required|in:cp,tp,atp',
            'parent_id'               => 'nullable|exists:curriculum_competencies,id',
            'indicators'              => 'nullable|string',
        ]);

        $data['school_id']   = $this->schoolId();
        $data['indicators']  = $this->parseLines($data['indicators'] ?? '');

        CurriculumCompetency::create($data);

        return back()->with('success', 'Kompetensi ditambahkan.');
    }

    public function update(Request $request, CurriculumCompetency $competency): RedirectResponse
    {
        abort_unless($competency->school_id === $this->schoolId(), 403);

        $data = $request->validate([
            'code'        => 'required|string|max:30',
            'description' => 'required|string',
            'level_type'  => 'required|in:cp,tp,atp',
            'parent_id'   => 'nullable|exists:curriculum_competencies,id',
            'indicators'  => 'nullable|string',
        ]);

        $data['indicators'] = $this->parseLines($data['indicators'] ?? '');

        $competency->update($data);

        return back()->with('success', 'Kompetensi diperbarui.');
    }

    public function destroy(CurriculumCompetency $competency): RedirectResponse
    {
        abort_unless($competency->school_id === $this->schoolId(), 403);

        abort_if($competency->children()->exists(), 422, 'Kompetensi masih punya turunan. Hapus turunan dulu.');

        $competency->delete();

        return back()->with('success', 'Kompetensi dihapus.');
    }

    private function parseLines(string $raw): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $raw))
            ->map(fn ($l) => trim($l))
            ->filter(fn ($l) => $l !== '')
            ->values()
            ->all();
    }
}
