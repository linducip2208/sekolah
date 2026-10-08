<?php

namespace App\Http\Controllers\Api\Lms;

use App\Http\Controllers\Controller;
use App\Models\Academic\Student;
use App\Models\Lms\Course;
use App\Models\Lms\CourseCertificate;
use App\Models\Lms\CourseEnrollment;
use App\Models\Lms\Quiz;
use App\Services\Lms\CourseService;
use App\Services\Lms\QuizService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LmsController extends Controller
{
    public function __construct(
        private CourseService $courses,
        private QuizService $quizzes,
    ) {}

    private function student(Request $request): Student
    {
        $student = Student::where('school_id', $request->user()->school_id)
            ->where('user_id', $request->user()->id)
            ->first();

        abort_unless($student, 404, 'Profil siswa tidak ditemukan.');
        return $student;
    }

    private function staffOnly(Request $request): void
    {
        abort_unless(
            $request->user()->hasRole(['super_admin', 'admin', 'principal', 'teacher', 'homeroom_teacher']),
            403
        );
    }

    public function courses(Request $request): JsonResponse
    {
        $courses = Course::where('school_id', $request->user()->school_id)
            ->where('is_published', true)
            ->withCount('modules')
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json($courses);
    }

    public function showCourse(Request $request, int $courseId): JsonResponse
    {
        $course = Course::where('school_id', $request->user()->school_id)
            ->where('is_published', true)
            ->with(['modules.lessons'])
            ->findOrFail($courseId);

        return response()->json($course);
    }

    public function enroll(Request $request): JsonResponse
    {
        $data = $request->validate(['course_id' => 'required|integer']);
        $student = $this->student($request);

        $enrollment = $this->courses->enroll(
            (int) $request->user()->school_id, (int) $data['course_id'], $student->id
        );

        return response()->json($enrollment, 201);
    }

    public function myProgress(Request $request): JsonResponse
    {
        $student = $this->student($request);

        return response()->json(
            $this->courses->progressForStudent((int) $request->user()->school_id, $student->id)
        );
    }

    public function completeLesson(Request $request): JsonResponse
    {
        $data = $request->validate([
            'enrollment_id' => 'required|integer',
            'lesson_id' => 'required|integer',
        ]);
        $student = $this->student($request);

        $enrollment = CourseEnrollment::where('school_id', $request->user()->school_id)
            ->whereKey($data['enrollment_id'])
            ->firstOrFail();

        $enrollment = $this->courses->completeLesson($enrollment, (int) $data['lesson_id'], $student->id);

        return response()->json($enrollment);
    }

    /**
     * Published quiz questions WITHOUT answer keys (anti-cheat).
     * Students attempt via POST /lms/quiz/submit {quiz_id, answers}.
     */
    public function questions(Request $request, int $quizId): JsonResponse
    {
        $quiz = Quiz::where('school_id', $request->user()->school_id)
            ->where('is_published', true)
            ->findOrFail($quizId);

        $questions = $quiz->questions()->get()->map(fn ($q) => [
            'id' => $q->id,
            'question' => $q->question,
            'type' => $q->type,
            'options' => $q->options,
            'order' => $q->order,
        ]);

        return response()->json(['data' => $questions]);
    }

    public function quizzes(Request $request): JsonResponse
    {
        $quizzes = Quiz::where('school_id', $request->user()->school_id)
            ->where('is_published', true)
            ->withCount('questions')
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json($quizzes);
    }

    public function submitQuiz(Request $request): JsonResponse
    {
        $data = $request->validate([
            'quiz_id' => 'required|integer',
            'answers' => 'required|array',
        ]);
        $student = $this->student($request);

        $quiz = Quiz::where('school_id', $request->user()->school_id)
            ->where('is_published', true)
            ->findOrFail($data['quiz_id']);

        return response()->json($this->quizzes->submit($quiz, $student->id, $data['answers']));
    }

    public function certificate(Request $request, int $enrollmentId): JsonResponse
    {
        $enrollment = CourseEnrollment::where('school_id', $request->user()->school_id)
            ->whereKey($enrollmentId)
            ->firstOrFail();

        $user = $request->user();
        if (! $user->hasRole(['super_admin', 'admin', 'principal', 'teacher', 'homeroom_teacher'])) {
            $student = $this->student($request);
            abort_unless((int) $enrollment->student_id === (int) $student->id, 403);
        }

        $certificate = $this->courses->certificateFor($enrollment);
        abort_unless($certificate, 404, 'Sertifikat belum diterbitkan.');

        return response()->json($certificate->load('enrollment.course'));
    }

    public function issueCertificate(Request $request, int $enrollmentId): JsonResponse
    {
        $this->staffOnly($request);

        $enrollment = CourseEnrollment::where('school_id', $request->user()->school_id)
            ->whereKey($enrollmentId)
            ->firstOrFail();

        return response()->json(
            $this->courses->issueCertificate($enrollment, (int) $request->user()->id), 201
        );
    }

    public function verifyCertificate(string $certificateNo): JsonResponse
    {
        $certificate = CourseCertificate::withoutGlobalScopes()
            ->where('certificate_no', $certificateNo)
            ->with(['enrollment.course', 'enrollment.student.user:id,name'])
            ->firstOrFail();

        return response()->json([
            'certificate_no' => $certificate->certificate_no,
            'course' => $certificate->enrollment->course?->title,
            'student' => $certificate->enrollment->student?->user?->name,
            'issued_at' => $certificate->issued_at?->toDateString(),
            'school_id' => $certificate->school_id,
        ]);
    }
}
