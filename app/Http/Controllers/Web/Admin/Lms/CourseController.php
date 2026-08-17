<?php

namespace App\Http\Controllers\Web\Admin\Lms;

use App\Http\Controllers\Controller;
use App\Models\Academic\Student;
use App\Models\Lms\Course;
use App\Models\Lms\CourseCertificate;
use App\Models\Lms\CourseEnrollment;
use App\Models\Lms\CourseLesson;
use App\Models\Lms\CourseModule;
use App\Services\Lms\CourseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function __construct(private CourseService $service) {}

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    private function authorizeOwn($model): void
    {
        abort_unless($model->school_id === $this->schoolId(), 403);
    }

    public function index(): View
    {
        $courses = Course::where('school_id', $this->schoolId())
            ->withCount('enrollments')
            ->withCount('modules')
            ->withCount('lessons')
            ->orderByDesc('created_at')
            ->get();

        return view('school-admin.lms.courses', ['courses' => $courses]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'       => 'required|string|max:200',
            'subject_id'  => 'nullable|exists:subjects,id',
            'description' => 'nullable|string',
            'icon'        => 'nullable|string|max:50',
            'is_published' => 'nullable|boolean',
        ]);

        Course::create([
            'school_id'    => $this->schoolId(),
            'title'        => $data['title'],
            'subject_id'   => $data['subject_id'] ?? null,
            'teacher_id'   => auth()->id(),
            'description'  => $data['description'] ?? null,
            'icon'         => $data['icon'] ?? null,
            'is_published' => $request->boolean('is_published'),
        ]);

        return back()->with('success', 'Kursus dibuat.');
    }

    public function show(Course $course): View
    {
        $this->authorizeOwn($course);

        $course->load(['modules.lessons', 'enrollments.student.user', 'enrollments.certificate']);

        $students = Student::where('school_id', $this->schoolId())
            ->with('user:id,name')
            ->orderBy('admission_no')
            ->get();

        $enrolledIds = $course->enrollments->pluck('student_id')->all();

        return view('school-admin.lms.course-show', [
            'course'      => $course,
            'students'    => $students,
            'enrolledIds' => $enrolledIds,
        ]);
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $this->authorizeOwn($course);

        $data = $request->validate([
            'title'       => 'required|string|max:200',
            'subject_id'  => 'nullable|exists:subjects,id',
            'description' => 'nullable|string',
            'icon'        => 'nullable|string|max:50',
            'is_published' => 'nullable|boolean',
        ]);

        $course->update([
            'title'        => $data['title'],
            'subject_id'   => $data['subject_id'] ?? null,
            'description'  => $data['description'] ?? null,
            'icon'         => $data['icon'] ?? null,
            'is_published' => $request->boolean('is_published'),
        ]);

        return back()->with('success', 'Kursus diperbarui.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $this->authorizeOwn($course);
        $course->delete();
        return back()->with('success', 'Kursus dihapus.');
    }

    public function storeModule(Request $request, Course $course): RedirectResponse
    {
        $this->authorizeOwn($course);

        $data = $request->validate([
            'title'       => 'required|string|max:200',
            'description' => 'nullable|string',
        ]);

        $order = $course->modules()->max('order') ?? 0;

        CourseModule::create([
            'school_id'   => $this->schoolId(),
            'course_id'   => $course->id,
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'order'       => $order + 1,
        ]);

        return back()->with('success', 'Modul ditambahkan.');
    }

    public function deleteModule(CourseModule $module): RedirectResponse
    {
        $this->authorizeOwn($module);
        $module->delete();
        return back()->with('success', 'Modul dihapus.');
    }

    public function storeLesson(Request $request, CourseModule $module): RedirectResponse
    {
        $this->authorizeOwn($module);

        $data = $request->validate([
            'title'            => 'required|string|max:200',
            'content_html'     => 'nullable|string',
            'video_url'        => 'nullable|url|max:500',
            'duration_minutes' => 'nullable|integer|min:0|max:600',
        ]);

        $order = $module->lessons()->max('order') ?? 0;

        CourseLesson::create([
            'school_id'        => $this->schoolId(),
            'course_module_id' => $module->id,
            'title'            => $data['title'],
            'content_html'     => $data['content_html'] ?? null,
            'video_url'        => $data['video_url'] ?? null,
            'duration_minutes' => $data['duration_minutes'] ?? 0,
            'order'            => $order + 1,
        ]);

        return back()->with('success', 'Materi ditambahkan.');
    }

    public function deleteLesson(CourseLesson $lesson): RedirectResponse
    {
        $this->authorizeOwn($lesson);
        $lesson->delete();
        return back()->with('success', 'Materi dihapus.');
    }

    public function enroll(Request $request, Course $course): RedirectResponse
    {
        $this->authorizeOwn($course);

        $data = $request->validate([
            'student_ids'   => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
        ]);

        $count = $this->service->bulkEnroll($this->schoolId(), $course->id, $data['student_ids']);

        return back()->with('success', "$count siswa didaftarkan.");
    }

    public function unenroll(CourseEnrollment $enrollment): RedirectResponse
    {
        $this->authorizeOwn($enrollment);
        $enrollment->delete();
        return back()->with('success', 'Pendaftaran dihapus.');
    }

    public function markComplete(CourseEnrollment $enrollment, CourseLesson $lesson): RedirectResponse
    {
        $this->authorizeOwn($enrollment);
        $this->service->completeLesson($enrollment, $lesson->id, $enrollment->student_id);
        return back()->with('success', 'Materi ditandai selesai.');
    }

    public function issueCertificate(CourseEnrollment $enrollment): RedirectResponse
    {
        $this->authorizeOwn($enrollment);
        $this->service->issueCertificate($enrollment, auth()->id());
        return back()->with('success', 'Sertifikat diterbitkan.');
    }

    public function certificate(CourseCertificate $certificate): View
    {
        $this->authorizeOwn($certificate);

        return view('school-admin.lms.certificate', [
            'certificate' => $certificate->load('enrollment.course', 'enrollment.student.user', 'issuedBy'),
        ]);
    }
}
