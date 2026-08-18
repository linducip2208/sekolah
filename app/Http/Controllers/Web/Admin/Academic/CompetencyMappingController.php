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

class CompetencyMappingController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $schoolId = $this->schoolId();

        $subjectId = $request->subject_id;
        $classRoomId = $request->class_room_id;

        $tpQuery = CurriculumCompetency::where('school_id', $schoolId)
            ->where('level_type', 'tp')
            ->with('subject:id,name', 'classRoom:id,name');

        $cpQuery = CurriculumCompetency::where('school_id', $schoolId)
            ->where('level_type', 'cp')
            ->with('subject:id,name', 'classRoom:id,name');

        if ($subjectId) {
            $tpQuery->where('subject_id', $subjectId);
            $cpQuery->where('subject_id', $subjectId);
        }
        if ($classRoomId) {
            $tpQuery->where('class_room_id', $classRoomId);
            $cpQuery->where('class_room_id', $classRoomId);
        }

        $tpItems = $tpQuery->orderBy('code')->get();
        $cpItems = $cpQuery->orderBy('code')->get();

        return view('school-admin.curriculum.competency-mapping', [
            'tpItems'     => $tpItems,
            'cpItems'     => $cpItems,
            'subjects'    => Subject::where('school_id', $schoolId)->orderBy('name')->get(),
            'classRooms'  => ClassRoom::where('school_id', $schoolId)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tp_id' => 'required|exists:curriculum_competencies,id',
            'cp_id' => 'required|exists:curriculum_competencies,id',
        ]);

        $tp = CurriculumCompetency::findOrFail($data['tp_id']);
        abort_unless($tp->school_id === $this->schoolId(), 403);
        abort_unless($tp->level_type === 'tp', 422, 'ID harus berupa TP.');

        $cp = CurriculumCompetency::findOrFail($data['cp_id']);
        abort_unless($cp->school_id === $this->schoolId(), 403);
        abort_unless($cp->level_type === 'cp', 422, 'Target harus berupa CP.');

        $mapping = $tp->mapping_rules ?? [];
        $mapping[] = $cp->id;
        $mapping = array_unique($mapping);

        $tp->update(['mapping_rules' => $mapping]);

        return back()->with('success', 'Mapping TP → CP disimpan.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tp_id' => 'required|exists:curriculum_competencies,id',
            'cp_id' => 'required|exists:curriculum_competencies,id',
        ]);

        $tp = CurriculumCompetency::findOrFail($data['tp_id']);
        abort_unless($tp->school_id === $this->schoolId(), 403);

        $mapping = $tp->mapping_rules ?? [];
        $mapping = array_values(array_filter($mapping, fn($id) => $id != $data['cp_id']));

        $tp->update(['mapping_rules' => $mapping]);

        return back()->with('success', 'Mapping dihapus.');
    }
}
