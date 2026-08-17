<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\ReportCard;
use App\Models\Academic\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TranscriptController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $schoolId = $this->schoolId();

        $students = Student::where('school_id', $schoolId)
            ->with('user:id,name')
            ->orderBy('admission_no')
            ->get();

        $selected  = null;
        $cards     = collect();
        $cumulative = ['gpa' => 0, 'avg_pct' => 0, 'count' => 0];

        if ($request->filled('student_id')) {
            $selected = Student::where('school_id', $schoolId)
                ->where('id', $request->input('student_id'))
                ->with('user:id,name', 'classSection.classRoom', 'classSection.section')
                ->first();

            if ($selected) {
                $cards = ReportCard::where('school_id', $schoolId)
                    ->where('student_id', $selected->id)
                    ->with('semester')
                    ->orderBy('semester_id')
                    ->get();

                $gpaValues = $cards->whereNotNull('gpa')->pluck('gpa')->map(fn ($v) => (float) $v);

                $cumulative = [
                    'gpa'     => $gpaValues->isNotEmpty() ? round($gpaValues->avg(), 2) : 0,
                    'avg_pct' => $cards->isNotEmpty() ? round($cards->avg('total_percentage'), 2) : 0,
                    'count'   => $cards->count(),
                ];
            }
        }

        return view('school-admin.grades.transcript', compact('students', 'selected', 'cards', 'cumulative'));
    }
}
