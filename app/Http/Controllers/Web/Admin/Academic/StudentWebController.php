<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Student;
use App\Models\User;
use App\Services\ActivityTimelineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StudentWebController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $schoolId = $this->schoolId();

        $students = Student::where('school_id', $schoolId)
            ->with(['user:id,name,email,phone,is_active', 'classSection.classRoom', 'classSection.section'])
            ->when($request->search, fn ($q) => $q->whereHas('user', fn ($u) =>
                $u->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%"))
                ->orWhere('admission_no', 'like', "%{$request->search}%"))
            ->when($request->class_section_id, fn ($q) => $q->where('class_section_id', $request->class_section_id))
            ->when($request->gender, fn ($q) => $q->where('gender', $request->gender))
            ->orderBy('admission_no')
            ->paginate(25)
            ->withQueryString();

        $classSections = ClassSection::where('school_id', $schoolId)
            ->with(['classRoom', 'section'])->orderBy('class_room_id')->orderBy('section_id')->get();

        return view('school-admin.students.index', compact('students', 'classSections'));
    }

    public function create(): View
    {
        return view('school-admin.students.create', [
            'classSections' => ClassSection::where('school_id', $this->schoolId())
                ->with(['classRoom', 'section'])->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'             => 'required|string|max:200',
            'email'            => 'required|email|max:200|unique:users,email',
            'phone'            => 'nullable|string|max:30',
            'password'         => 'required|string|min:6',
            'admission_no'     => 'required|string|max:50',
            'admission_date'   => 'nullable|date',
            'date_of_birth'    => 'nullable|date',
            'gender'           => 'required|in:male,female,other',
            'blood_group'      => 'nullable|string|max:10',
            'address'          => 'nullable|string',
            'guardian_name'    => 'nullable|string|max:200',
            'guardian_phone'   => 'nullable|string|max:30',
            'whatsapp_phone'   => 'nullable|string|max:30',
            'class_section_id' => 'nullable|exists:class_sections,id',
        ]);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name'      => $data['name'],
                'email'     => $data['email'],
                'phone'     => $data['phone'] ?? null,
                'password'  => Hash::make($data['password']),
                'school_id' => $this->schoolId(),
                'is_active' => true,
            ]);
            $user->assignRole('student');

            Student::create([
                'user_id'          => $user->id,
                'school_id'        => $this->schoolId(),
                'class_section_id' => $data['class_section_id'] ?? null,
                'admission_no'     => $data['admission_no'],
                'admission_date'   => $data['admission_date'] ?? null,
                'date_of_birth'    => $data['date_of_birth'] ?? null,
                'gender'           => $data['gender'],
                'blood_group'      => $data['blood_group'] ?? null,
                'address'          => $data['address'] ?? null,
                'guardian_name'    => $data['guardian_name'] ?? null,
                'guardian_phone'   => $data['guardian_phone'] ?? null,
                'whatsapp_phone'   => $data['whatsapp_phone'] ?? null,
            ]);
        });

        return redirect()->route('admin.students.index')->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function edit(Student $student): View
    {
        $this->authorizeOwn($student);
        return view('school-admin.students.edit', [
            'student'       => $student->load('user'),
            'classSections' => ClassSection::where('school_id', $this->schoolId())
                ->with(['classRoom', 'section'])->get(),
        ]);
    }

    /**
     * Student 360 — unified, tabbed profile aggregating data across all modules.
     */
    public function show(Student $student): View
    {
        $this->authorizeOwn($student);
        $student->load(['user', 'classSection.classRoom', 'classSection.section', 'parents']);

        $safe = function (callable $fn, $default = null) {
            try { return $fn(); } catch (\Throwable $e) { return $default; }
        };

        $attendance = $safe(fn () => \App\Models\Academic\Attendance::where('student_id', $student->id)->get(), collect());
        $attSummary = [
            'total'   => $attendance->count(),
            'present' => $attendance->whereIn('status', ['present', 'late'])->count(),
            'absent'  => $attendance->whereIn('status', ['absent'])->count(),
            'pct'     => $attendance->count() > 0
                ? round($attendance->whereIn('status', ['present', 'late'])->count() / $attendance->count() * 100)
                : 0,
        ];

        $marks = $safe(fn () => \App\Models\Academic\Mark::where('student_id', $student->id)
            ->with(['subject:id,name', 'exam:id,name'])->latest()->limit(20)->get(), collect());
        $avgMark = $marks->isNotEmpty()
            ? round($marks->avg(fn ($m) => $m->total_marks > 0 ? ($m->obtained_marks / $m->total_marks) * 100 : 0))
            : null;

        $discipline   = $safe(fn () => \App\Models\Discipline\DisciplineRecord::where('student_id', $student->id)->latest('incident_date')->limit(20)->get(), collect());
        $counseling   = $safe(fn () => \App\Models\Counseling\CounselingSession::where('student_id', $student->id)->latest('scheduled_at')->limit(20)->get(), collect());
        $health       = $safe(fn () => \App\Models\Medical\ClinicVisit::where('student_id', $student->id)->latest('visit_at')->limit(20)->get(), collect());
        $achievements = $safe(fn () => \App\Models\Achievement\StudentAchievement::where('student_id', $student->id)->latest('achieved_at')->limit(20)->get(), collect());
        $activities   = $safe(fn () => \App\Models\Activity\StudentActivityLog::where('student_id', $student->id)->latest()->limit(20)->get(), collect());

        $invoices = $safe(fn () => \App\Models\Finance\FeeInvoice::where('student_id', $student->id)->latest('due_date')->get(), collect());
        $financeSummary = [
            'total'       => $invoices->count(),
            'unpaid'      => $invoices->whereIn('status', ['unpaid', 'partial', 'overdue'])->count(),
            'outstanding' => $invoices->whereIn('status', ['unpaid', 'partial', 'overdue'])->sum(fn ($i) => $i->amount - $i->paid_amount),
        ];

        $risk    = $safe(fn () => \App\Models\Analytics\StudentRiskScore::where('student_id', $student->id)->latest('snapshot_date')->first());
        $dropout = $safe(fn () => \App\Models\Analytics\AiDropoutPrediction::where('student_id', $student->id)->latest('prediction_date')->first());

        return view('school-admin.students.show', compact(
            'student', 'attSummary', 'marks', 'avgMark', 'discipline', 'counseling',
            'health', 'achievements', 'activities', 'invoices', 'financeSummary', 'risk', 'dropout'
        ));
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeOwn($student);
        $data = $request->validate([
            'name'             => 'required|string|max:200',
            'email'            => 'required|email|max:200|unique:users,email,'.$student->user_id,
            'phone'            => 'nullable|string|max:30',
            'admission_no'     => 'required|string|max:50',
            'admission_date'   => 'nullable|date',
            'date_of_birth'    => 'nullable|date',
            'gender'           => 'required|in:male,female,other',
            'blood_group'      => 'nullable|string|max:10',
            'address'          => 'nullable|string',
            'guardian_name'    => 'nullable|string|max:200',
            'guardian_phone'   => 'nullable|string|max:30',
            'whatsapp_phone'   => 'nullable|string|max:30',
            'class_section_id' => 'nullable|exists:class_sections,id',
        ]);

        DB::transaction(function () use ($student, $data) {
            $student->user->update([
                'name'  => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
            ]);
            $student->update([
                'class_section_id' => $data['class_section_id'] ?? null,
                'admission_no'     => $data['admission_no'],
                'admission_date'   => $data['admission_date'] ?? null,
                'date_of_birth'    => $data['date_of_birth'] ?? null,
                'gender'           => $data['gender'],
                'blood_group'      => $data['blood_group'] ?? null,
                'address'          => $data['address'] ?? null,
                'guardian_name'    => $data['guardian_name'] ?? null,
                'guardian_phone'   => $data['guardian_phone'] ?? null,
                'whatsapp_phone'   => $data['whatsapp_phone'] ?? null,
            ]);
        });

        return redirect()->route('admin.students.index')->with('success', 'Data siswa diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $this->authorizeOwn($student);
        DB::transaction(function () use ($student) {
            $student->user?->update(['is_active' => false]);
            $student->delete();
        });
        return back()->with('success', 'Siswa dinonaktifkan.');
    }

    private function authorizeOwn(Student $student): void
    {
        abort_unless($student->school_id === $this->schoolId(), 403);
    }

    public function timeline(Request $request): View
    {
        $schoolId = $this->schoolId();
        $students = Student::where('school_id', $schoolId)
            ->with('user:id,name')->orderBy('id')->get();

        return view('school-admin.students.timeline', compact('students'));
    }
}
