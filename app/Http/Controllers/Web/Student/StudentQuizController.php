<?php

namespace App\Http\Controllers\Web\Student;

use App\Http\Controllers\Controller;
use App\Models\Academic\Student;
use App\Models\Lms\Quiz;
use App\Services\Lms\QuizService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentQuizController extends Controller
{
    public function __construct(private QuizService $service) {}

    private function student(): Student
    {
        $student = Student::where('user_id', auth()->id())->first();
        abort_unless($student, 404, 'Profil siswa tidak ditemukan.');
        return $student;
    }

    public function index(): View
    {
        $student = $this->student();

        $quizzes = Quiz::where('school_id', $student->school_id)
            ->where('is_published', true)
            ->with('course')
            ->withCount('questions')
            ->orderByDesc('id')
            ->get();

        return view('student-portal.quizzes.index', compact('student', 'quizzes'));
    }

    public function take(Quiz $quiz): View
    {
        $student = $this->student();
        abort_if($quiz->school_id !== $student->school_id, 403);

        return view('student-portal.quizzes.take', [
            'student' => $student,
            'quiz'    => $quiz->load('questions'),
        ]);
    }

    public function submit(Request $request, Quiz $quiz): View
    {
        $student = $this->student();
        abort_if($quiz->school_id !== $student->school_id, 403);

        $result = $this->service->submit($quiz, $student->id, $request->input('answers', []));

        return view('student-portal.quizzes.result', [
            'student' => $student,
            'quiz'    => $quiz,
            'result'  => $result,
        ]);
    }
}
