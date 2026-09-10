<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Attendance;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Student;
use App\Services\Academic\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceWebController extends Controller
{
    public function __construct(private AttendanceService $service) {}

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $schoolId = $this->schoolId();
        $date = $request->date ? Carbon::parse($request->date)->toDateString() : now()->toDateString();
        $classSectionId = $request->class_section_id;

        $classSections = ClassSection::where('school_id', $schoolId)
            ->with(['classRoom', 'section'])
            ->orderBy('class_room_id')->orderBy('section_id')->get();

        $students = collect();
        $existing = collect();

        if ($classSectionId) {
            $students = Student::where('school_id', $schoolId)
                ->where('class_section_id', $classSectionId)
                ->with('user:id,name')
                ->orderBy('admission_no')->get();

            $existing = Attendance::where('school_id', $schoolId)
                ->where('class_section_id', $classSectionId)
                ->where('date', $date)
                ->get()
                ->keyBy('student_id');
        }

        return view('school-admin.attendance.index', compact('classSections', 'students', 'existing', 'date', 'classSectionId'));
    }

    public function save(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'class_section_id' => 'required|exists:class_sections,id',
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*' => 'required|in:present,absent,late,half_day,on_leave',
            'notes' => 'nullable|array',
        ]);

        $schoolId = $this->schoolId();
        $userId = auth()->id();

        $records = collect($data['attendance'])
            ->map(fn ($status, $studentId) => [
                'student_id' => (int) $studentId,
                'status' => $status,
                'note' => $data['notes'][$studentId] ?? null,
            ])->values()->all();

        $this->service->bulkMark($data['class_section_id'], $data['date'], $records, $request->user());

        return back()->with('success', 'Absensi tersimpan untuk '.count($data['attendance']).' siswa.');
    }

    public function recap(Request $request): View
    {
        $schoolId = $this->schoolId();
        $month = $request->month ? Carbon::parse($request->month.'-01') : now()->startOfMonth();
        $classSectionId = $request->class_section_id;

        $classSections = ClassSection::where('school_id', $schoolId)
            ->with(['classRoom', 'section'])->orderBy('class_room_id')->orderBy('section_id')->get();

        $recap = collect();
        if ($classSectionId) {
            $recap = Student::where('school_id', $schoolId)
                ->where('class_section_id', $classSectionId)
                ->with('user:id,name')
                ->withCount([
                    'attendances as present_count' => fn ($q) => $q->where('status', 'present')
                        ->whereYear('date', $month->year)->whereMonth('date', $month->month),
                    'attendances as absent_count' => fn ($q) => $q->where('status', 'absent')
                        ->whereYear('date', $month->year)->whereMonth('date', $month->month),
                    'attendances as late_count' => fn ($q) => $q->where('status', 'late')
                        ->whereYear('date', $month->year)->whereMonth('date', $month->month),
                    'attendances as on_leave_count' => fn ($q) => $q->where('status', 'on_leave')
                        ->whereYear('date', $month->year)->whereMonth('date', $month->month),
                ])
                ->orderBy('admission_no')->get();
        }

        return view('school-admin.attendance.recap', compact('classSections', 'recap', 'month', 'classSectionId'));
    }
}
