<?php

namespace App\Http\Controllers\Web\Admin\Lms;

use App\Http\Controllers\Controller;
use App\Models\Lms\Course;
use App\Models\Lms\Quiz;
use App\Models\Lms\QuizQuestion;
use App\Services\Lms\QuizService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function __construct(private QuizService $service) {}

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
        $quizzes = Quiz::where('school_id', $this->schoolId())
            ->withCount('questions')
            ->withCount('attempts')
            ->with('course')
            ->orderByDesc('id')
            ->get();

        return view('school-admin.lms.quizzes', ['quizzes' => $quizzes]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'       => 'required|string|max:200',
            'course_id'   => 'nullable|exists:courses,id',
            'description' => 'nullable|string',
            'pass_score'  => 'nullable|integer|min:0|max:100',
            'is_published'=> 'nullable|boolean',
        ]);

        Quiz::create([
            'school_id'    => $this->schoolId(),
            'title'        => $data['title'],
            'course_id'    => $data['course_id'] ?? null,
            'description'  => $data['description'] ?? null,
            'pass_score'   => $data['pass_score'] ?? 60,
            'is_published' => $request->boolean('is_published'),
        ]);

        return back()->with('success', 'Kuis dibuat.');
    }

    public function show(Quiz $quiz): View
    {
        $this->authorizeOwn($quiz);

        return view('school-admin.lms.quiz-show', [
            'quiz'     => $quiz->load('questions', 'attempts.student.user'),
            'courses'  => Course::where('school_id', $this->schoolId())->orderBy('title')->get(),
        ]);
    }

    public function storeQuestion(Request $request, Quiz $quiz): RedirectResponse
    {
        $this->authorizeOwn($quiz);

        $data = $request->validate([
            'question'       => 'required|string',
            'type'           => 'required|in:mcq,true_false',
            'options'        => 'nullable|string',
            'correct_answer' => 'required|string',
        ]);

        $options = collect(preg_split('/\r\n|\r|\n/', $data['options'] ?? ''))
            ->map(fn ($l) => trim($l))
            ->filter(fn ($l) => $l !== '')
            ->map(fn ($l) => ['text' => $l])
            ->values()
            ->all();

        $order = $quiz->questions()->max('order') ?? 0;

        QuizQuestion::create([
            'school_id'      => $this->schoolId(),
            'quiz_id'        => $quiz->id,
            'question'       => $data['question'],
            'type'           => $data['type'],
            'options'        => $options,
            'correct_answer' => $data['correct_answer'],
            'order'          => $order + 1,
        ]);

        return back()->with('success', 'Soal kuis ditambahkan.');
    }

    public function generate(Request $request, Quiz $quiz): RedirectResponse
    {
        $this->authorizeOwn($quiz);

        $count = $request->validate(['count' => 'required|integer|min:1|max:100'])['count'];

        $created = $this->service->generateFromBank($quiz, (int) $count);

        return back()->with('success', "$created soal kuis diambil dari bank.");
    }

    public function deleteQuestion(QuizQuestion $question): RedirectResponse
    {
        $this->authorizeOwn($question);
        $question->delete();
        return back()->with('success', 'Soal kuis dihapus.');
    }

    public function destroy(Quiz $quiz): RedirectResponse
    {
        $this->authorizeOwn($quiz);
        $quiz->delete();
        return back()->with('success', 'Kuis dihapus.');
    }
}
