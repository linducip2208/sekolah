<?php

namespace App\Http\Controllers\Web\Admin\Communication;

use App\Http\Controllers\Controller;
use App\Models\Academic\Student;
use App\Models\Academic\Subject;
use App\Models\Communication\SurveyAnswer;
use App\Models\Communication\SurveyQuestion;
use App\Models\Communication\SurveyResponse;
use App\Models\Communication\SurveyTemplate;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SurveyController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    private function authorizeOwn($model): void
    {
        abort_unless($model->school_id === $this->schoolId(), 403);
    }

    /* =================== TEMPLATES =================== */

    public function templates(): View
    {
        $templates = SurveyTemplate::where('school_id', $this->schoolId())
            ->withCount(['questions', 'responses'])
            ->orderByDesc('created_at')->get();

        return view('school-admin.surveys.templates', compact('templates'));
    }

    public function storeTemplate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'survey_type' => 'required|string|in:guru,staff,kepsek,fasilitas',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
        ]);
        $data['school_id'] = $this->schoolId();
        $data['is_active'] = true;
        SurveyTemplate::create($data);
        return back()->with('success', 'Template survei berhasil dibuat.');
    }

    public function updateTemplate(Request $request, SurveyTemplate $template): RedirectResponse
    {
        $this->authorizeOwn($template);
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'survey_type' => 'required|string|in:guru,staff,kepsek,fasilitas',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'is_active'   => 'nullable|boolean',
        ]);
        $data['is_active'] = (bool) ($data['is_active'] ?? $template->is_active);
        $template->update($data);
        return back()->with('success', 'Template survei diperbarui.');
    }

    public function deleteTemplate(SurveyTemplate $template): RedirectResponse
    {
        $this->authorizeOwn($template);
        $template->delete();
        return back()->with('success', 'Template survei dihapus.');
    }

    /* =================== QUESTIONS =================== */

    public function questions(SurveyTemplate $template): View
    {
        $this->authorizeOwn($template);
        $questions = $template->questions()->orderBy('sort_order')->get();

        return view('school-admin.surveys.questions', compact('template', 'questions'));
    }

    public function storeQuestion(Request $request, SurveyTemplate $template): RedirectResponse
    {
        $this->authorizeOwn($template);
        $data = $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|string|in:rating_1_5,text,multiple_choice',
            'options'       => 'nullable|string',
            'sort_order'    => 'nullable|integer|min:0',
        ]);
        $data['school_id'] = $this->schoolId();
        $data['survey_template_id'] = $template->id;
        $data['options'] = $data['options'] ? json_decode($data['options'], true) : null;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        SurveyQuestion::create($data);
        return back()->with('success', 'Pertanyaan berhasil ditambahkan.');
    }

    public function updateQuestion(Request $request, SurveyTemplate $template, SurveyQuestion $question): RedirectResponse
    {
        $this->authorizeOwn($template);
        $this->authorizeOwn($question);
        $data = $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|string|in:rating_1_5,text,multiple_choice',
            'options'       => 'nullable|string',
            'sort_order'    => 'nullable|integer|min:0',
        ]);
        $data['options'] = $data['options'] ? json_decode($data['options'], true) : null;
        $question->update($data);
        return back()->with('success', 'Pertanyaan diperbarui.');
    }

    public function deleteQuestion(SurveyTemplate $template, SurveyQuestion $question): RedirectResponse
    {
        $this->authorizeOwn($template);
        $this->authorizeOwn($question);
        $question->delete();
        return back()->with('success', 'Pertanyaan dihapus.');
    }

    /* =================== RESPONSES =================== */

    public function responses(SurveyTemplate $template): View
    {
        $this->authorizeOwn($template);
        $responses = $template->responses()
            ->with('answers.question')
            ->orderByDesc('submitted_at')
            ->paginate(30);

        return view('school-admin.surveys.responses', compact('template', 'responses'));
    }

    public function deleteResponse(SurveyTemplate $template, SurveyResponse $response): RedirectResponse
    {
        $this->authorizeOwn($template);
        $this->authorizeOwn($response);
        $response->delete();
        return back()->with('success', 'Respons dihapus.');
    }

    /* =================== ANALYTICS =================== */

    public function analytics(SurveyTemplate $template): View
    {
        $this->authorizeOwn($template);
        $questions = $template->questions()->orderBy('sort_order')->get();

        $questionAnalytics = [];
        foreach ($questions as $q) {
            $answers = SurveyAnswer::where('survey_question_id', $q->id)
                ->whereHas('response', fn($r) => $r->where('survey_template_id', $template->id))
                ->get();

            $avgRating = null;
            $distribution = [];
            if ($q->question_type === 'rating_1_5') {
                $ratings = $answers->pluck('answer_rating')->filter();
                $avgRating = $ratings->count() > 0 ? round($ratings->avg(), 2) : null;
                $distribution = $ratings->countBy()->toArray();
            }

            $questionAnalytics[] = [
                'question'      => $q->question_text,
                'type'          => $q->question_type,
                'response_count' => $answers->count(),
                'avg_rating'    => $avgRating,
                'distribution'  => $distribution,
                'text_answers'  => $q->question_type === 'text' ? $answers->pluck('answer_text')->filter()->toArray() : [],
            ];
        }

        $targetAnalytics = [];
        if (in_array($template->survey_type, ['guru', 'staff'])) {
            $users = User::where('school_id', $this->schoolId())
                ->whereHas('roles', fn($q) => $q->where('name', $template->survey_type === 'guru' ? 'teacher' : 'staff'))
                ->get();

            foreach ($users as $user) {
                $userResponseIds = SurveyResponse::where('survey_template_id', $template->id)
                    ->where('target_type', $template->survey_type === 'guru' ? 'teacher' : 'staff')
                    ->where('target_id', $user->id)
                    ->pluck('id');

                $avgForUser = SurveyAnswer::whereIn('survey_response_id', $userResponseIds)
                    ->whereNotNull('answer_rating')
                    ->avg('answer_rating');

                $targetAnalytics[] = [
                    'name'    => $user->name,
                    'count'   => $userResponseIds->count(),
                    'avg'     => $avgForUser ? round($avgForUser, 2) : null,
                ];
            }

            usort($targetAnalytics, fn($a, $b) => ($b['avg'] ?? 0) <=> ($a['avg'] ?? 0));
        }

        $totalResponses = $template->responses()->count();

        return view('school-admin.surveys.analytics', compact(
            'template', 'questionAnalytics', 'targetAnalytics', 'totalResponses'
        ));
    }

    /* =================== STUDENT/PARENT FILL =================== */

    public function studentFill(): View
    {
        $student = Student::where('user_id', auth()->id())->first();
        abort_unless($student, 404, 'Profil siswa tidak ditemukan.');

        $activeTemplates = SurveyTemplate::where('school_id', $this->schoolId())
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now()->toDateString());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now()->toDateString());
            })
            ->whereIn('survey_type', ['guru', 'fasilitas'])
            ->with('questions')
            ->get();

        return view('student-portal.surveys.list', compact('activeTemplates', 'student'));
    }

    public function studentDoFill(SurveyTemplate $template): View
    {
        $student = Student::where('user_id', auth()->id())->first();
        abort_unless($student, 404);

        $alreadyResponded = SurveyResponse::where('survey_template_id', $template->id)
            ->where('respondent_type', 'student')
            ->where('respondent_id', $student->id)
            ->exists();

        if ($alreadyResponded) {
            abort(403, 'Anda sudah mengisi survei ini.');
        }

        $questions = $template->questions()->orderBy('sort_order')->get();

        $targets = [];
        if ($template->survey_type === 'guru') {
            $sectionIds = $student->classSection()
                ->exists()
                ? [$student->class_section_id]
                : [];

            $query = User::where('users.school_id', $this->schoolId())
                ->whereHas('roles', fn($q) => $q->where('name', 'teacher'));

            if ($student->class_section_id) {
                $userIds = DB::table('class_sections')
                    ->where('id', $student->class_section_id)
                    ->whereNotNull('class_teacher_id')
                    ->pluck('class_teacher_id');

                $allTeacherIds = DB::table('timetable_slots')
                    ->where('class_section_id', $student->class_section_id)
                    ->whereNotNull('teacher_id')
                    ->pluck('teacher_id');

                $merged = $userIds->merge($allTeacherIds)->unique()->filter();
                $query->whereIn('users.id', $merged->toArray());
            }

            $targets = $query->select('users.id', 'users.name')->get();
        } elseif ($template->survey_type === 'fasilitas') {
            $targets = collect([
                ['id' => 0, 'name' => 'Perpustakaan'],
                ['id' => 1, 'name' => 'Kantin'],
                ['id' => 2, 'name' => 'Laboratorium'],
                ['id' => 3, 'name' => 'Transportasi'],
                ['id' => 4, 'name' => 'Asrama'],
                ['id' => 5, 'name' => 'Toilet'],
                ['id' => 6, 'name' => 'Lapangan Olahraga'],
            ]);
        }

        return view('student-portal.surveys.fill', compact('template', 'student', 'questions', 'targets'));
    }

    public function studentSubmit(Request $request, SurveyTemplate $template): RedirectResponse
    {
        $student = Student::where('user_id', auth()->id())->first();
        abort_unless($student, 404);

        $alreadyResponded = SurveyResponse::where('survey_template_id', $template->id)
            ->where('respondent_type', 'student')
            ->where('respondent_id', $student->id)
            ->exists();

        if ($alreadyResponded) {
            return back()->with('error', 'Anda sudah mengisi survei ini.');
        }

        $validated = $request->validate([
            'target_id'      => 'nullable|integer',
            'target_type'    => 'nullable|string',
            'answers'        => 'required|array',
            'answers.*.question_id' => 'required|exists:survey_questions,id',
            'answers.*.value'       => 'required|string',
        ]);

        DB::transaction(function () use ($template, $student, $validated) {
            $response = SurveyResponse::create([
                'school_id'         => $this->schoolId(),
                'survey_template_id' => $template->id,
                'respondent_type'   => 'student',
                'respondent_id'     => $student->id,
                'target_type'       => $validated['target_type'] ?? 'school',
                'target_id'         => $validated['target_id'] ?? null,
                'submitted_at'      => now(),
            ]);

            foreach ($validated['answers'] as $ans) {
                $question = SurveyQuestion::find($ans['question_id']);
                SurveyAnswer::create([
                    'survey_response_id' => $response->id,
                    'survey_question_id' => $ans['question_id'],
                    'answer_text'  => $question->question_type !== 'rating_1_5' ? $ans['value'] : null,
                    'answer_rating' => $question->question_type === 'rating_1_5'
                        ? min(5, max(1, (int) $ans['value']))
                        : null,
                ]);
            }
        });

        return redirect()->route('student.surveys')->with('success', 'Terima kasih! Survei telah terkirim.');
    }

    /* =================== PARENT FILL =================== */

    public function parentFill(): View
    {
        $children = Student::whereHas('parents', fn($q) => $q->where('parent_id', auth()->id()))->get();

        $activeTemplates = SurveyTemplate::where('school_id', $this->schoolId())
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now()->toDateString());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now()->toDateString());
            })
            ->whereIn('survey_type', ['guru', 'kepsek', 'fasilitas'])
            ->with('questions')
            ->get();

        return view('parent-portal.surveys.list', compact('activeTemplates', 'children'));
    }

    public function parentDoFill(SurveyTemplate $template): View
    {
        $parentId = auth()->id();
        $children = Student::whereHas('parents', fn($q) => $q->where('parent_id', $parentId))->get();

        $alreadyResponded = SurveyResponse::where('survey_template_id', $template->id)
            ->where('respondent_type', 'parent')
            ->where('respondent_id', $parentId)
            ->exists();

        if ($alreadyResponded) {
            abort(403, 'Anda sudah mengisi survei ini.');
        }

        $questions = $template->questions()->orderBy('sort_order')->get();

        $targets = [];
        if ($template->survey_type === 'guru') {
            $sectionIds = $children->pluck('class_section_id')->filter();
            $allTeacherIds = DB::table('timetable_slots')
                ->whereIn('class_section_id', $sectionIds)
                ->whereNotNull('teacher_id')
                ->pluck('teacher_id');
            $classTeacherIds = DB::table('class_sections')
                ->whereIn('id', $sectionIds)
                ->whereNotNull('class_teacher_id')
                ->pluck('class_teacher_id');

            $merged = $allTeacherIds->merge($classTeacherIds)->unique()->filter();
            $targets = User::where('school_id', $this->schoolId())
                ->whereIn('id', $merged->toArray())
                ->select('id', 'name')->get();
        } elseif ($template->survey_type === 'fasilitas') {
            $targets = collect([
                ['id' => 0, 'name' => 'Perpustakaan'],
                ['id' => 1, 'name' => 'Kantin'],
                ['id' => 2, 'name' => 'Laboratorium'],
                ['id' => 3, 'name' => 'Transportasi'],
                ['id' => 4, 'name' => 'Asrama'],
                ['id' => 5, 'name' => 'Toilet'],
                ['id' => 6, 'name' => 'Lapangan Olahraga'],
            ]);
        } elseif ($template->survey_type === 'kepsek') {
            $targets = User::where('school_id', $this->schoolId())
                ->whereHas('roles', fn($q) => $q->where('name', 'admin'))
                ->select('id', 'name')->get();
        }

        return view('parent-portal.surveys.fill', compact('template', 'children', 'questions', 'targets'));
    }

    public function parentSubmit(Request $request, SurveyTemplate $template): RedirectResponse
    {
        $parentId = auth()->id();

        $alreadyResponded = SurveyResponse::where('survey_template_id', $template->id)
            ->where('respondent_type', 'parent')
            ->where('respondent_id', $parentId)
            ->exists();

        if ($alreadyResponded) {
            return back()->with('error', 'Anda sudah mengisi survei ini.');
        }

        $validated = $request->validate([
            'target_id'      => 'nullable|integer',
            'target_type'    => 'nullable|string',
            'answers'        => 'required|array',
            'answers.*.question_id' => 'required|exists:survey_questions,id',
            'answers.*.value'       => 'required|string',
        ]);

        DB::transaction(function () use ($template, $parentId, $validated) {
            $response = SurveyResponse::create([
                'school_id'         => $this->schoolId(),
                'survey_template_id' => $template->id,
                'respondent_type'   => 'parent',
                'respondent_id'     => $parentId,
                'target_type'       => $validated['target_type'] ?? 'school',
                'target_id'         => $validated['target_id'] ?? null,
                'submitted_at'      => now(),
            ]);

            foreach ($validated['answers'] as $ans) {
                $question = SurveyQuestion::find($ans['question_id']);
                SurveyAnswer::create([
                    'survey_response_id' => $response->id,
                    'survey_question_id' => $ans['question_id'],
                    'answer_text'  => $question->question_type !== 'rating_1_5' ? $ans['value'] : null,
                    'answer_rating' => $question->question_type === 'rating_1_5'
                        ? min(5, max(1, (int) $ans['value']))
                        : null,
                ]);
            }
        });

        return redirect()->route('portal.surveys')->with('success', 'Terima kasih! Survei telah terkirim.');
    }
}
