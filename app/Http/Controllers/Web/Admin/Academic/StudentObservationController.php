<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\ObservationScore;
use App\Models\Academic\Rubric;
use App\Models\Academic\Staff;
use App\Models\Academic\Student;
use App\Models\Academic\StudentObservation;
use App\Models\Academic\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentObservationController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $schoolId = $this->schoolId();

        $query = StudentObservation::where('school_id', $schoolId)
            ->with(['student:id,admission_no,user_id', 'student.user:id,name', 'observer:id,name', 'subject:id,name', 'rubric:id,name', 'scores.rubricCriterion']);

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }
        if ($request->filled('observation_type')) {
            $query->where('observation_type', $request->observation_type);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        return view('school-admin.academic.student-observations', [
            'observations' => $query->orderByDesc('date')->paginate(30)->withQueryString(),
            'students'     => Student::where('school_id', $schoolId)->with('user:id,name')->get(),
            'subjects'     => Subject::where('school_id', $schoolId)->orderBy('name')->get(),
            'rubrics'      => Rubric::where('school_id', $schoolId)->orderBy('name')->get(),
            'obsTypes'     => ['akademik', 'non_akademik', 'sosial', 'emosional'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id'        => 'required|exists:students,id',
            'observer_id'       => 'required|exists:users,id',
            'subject_id'        => 'nullable|exists:subjects,id',
            'rubric_id'         => 'nullable|exists:rubrics,id',
            'date'              => 'required|date',
            'observation_type'  => 'required|in:akademik,non_akademik,sosial,emosional',
            'overall_notes'     => 'nullable|string',
        ]);

        $data['school_id'] = $this->schoolId();

        $observation = StudentObservation::create($data);

        if ($request->filled('criteria_scores')) {
            foreach ($request->criteria_scores as $cs) {
                $observation->scores()->create([
                    'school_id'           => $this->schoolId(),
                    'rubric_criteria_id'  => $cs['rubric_criteria_id'] ?? null,
                    'score'               => $cs['score'] ?? 0,
                    'notes'               => $cs['notes'] ?? null,
                ]);
            }
        }

        return back()->with('success', 'Observasi siswa disimpan.');
    }

    public function update(Request $request, StudentObservation $observation): RedirectResponse
    {
        abort_unless($observation->school_id === $this->schoolId(), 403);

        $data = $request->validate([
            'student_id'        => 'required|exists:students,id',
            'observer_id'       => 'required|exists:users,id',
            'subject_id'        => 'nullable|exists:subjects,id',
            'rubric_id'         => 'nullable|exists:rubrics,id',
            'date'              => 'required|date',
            'observation_type'  => 'required|in:akademik,non_akademik,sosial,emosional',
            'overall_notes'     => 'nullable|string',
        ]);

        $observation->update($data);

        if ($request->filled('criteria_scores')) {
            $observation->scores()->delete();
            foreach ($request->criteria_scores as $cs) {
                $observation->scores()->create([
                    'school_id'           => $this->schoolId(),
                    'rubric_criteria_id'  => $cs['rubric_criteria_id'] ?? null,
                    'score'               => $cs['score'] ?? 0,
                    'notes'               => $cs['notes'] ?? null,
                ]);
            }
        }

        return back()->with('success', 'Observasi siswa diperbarui.');
    }

    public function destroy(StudentObservation $observation): RedirectResponse
    {
        abort_unless($observation->school_id === $this->schoolId(), 403);
        $observation->delete();
        return back()->with('success', 'Observasi siswa dihapus.');
    }
}
