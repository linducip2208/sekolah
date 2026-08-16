<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Assignment;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Lesson;
use App\Models\Academic\StudyMaterial;
use App\Models\Academic\Subject;
use App\Models\Curriculum\CurriculumFramework;
use App\Models\Extracurricular\Extracurricular;
use App\Models\QuestionBank\QuestionBankCategory;
use App\Models\QuestionBank\QuestionBankItem;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassroomExtrasController extends Controller
{
    private function schoolId(): int { return auth()->user()->school_id; }
    private function authorizeOwn($model): void { abort_unless($model->school_id === $this->schoolId(), 403); }

    /* ============== ONLINE CLASSROOM (Lessons + Assignments) ============== */

    public function lessons(): View
    {
        return view('school-admin.classroom.lessons', [
            'lessons' => Lesson::where('school_id', $this->schoolId())
                ->with(['classSection.classRoom', 'subject', 'teacher:id,name'])
                ->orderByDesc('created_at')->paginate(20),
            'subjects'      => Subject::where('school_id', $this->schoolId())->get(),
            'classSections' => ClassSection::where('school_id', $this->schoolId())->with(['classRoom', 'section'])->get(),
            'teachers'      => User::where('school_id', $this->schoolId())->whereHas('roles', fn($q) => $q->where('name', 'teacher'))->get(['id', 'name']),
        ]);
    }

    public function storeLesson(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'class_section_id' => 'required|exists:class_sections,id',
            'subject_id'       => 'required|exists:subjects,id',
            'teacher_id'       => 'required|exists:users,id',
            'description'      => 'nullable|string',
        ]);
        $data['school_id'] = $this->schoolId();
        Lesson::create($data);
        return back()->with('success', 'Materi/lesson ditambahkan.');
    }

    public function deleteLesson(Lesson $lesson): RedirectResponse
    {
        $this->authorizeOwn($lesson);
        $lesson->delete();
        return back()->with('success', 'Lesson dihapus.');
    }

    public function assignments(): View
    {
        return view('school-admin.classroom.assignments', [
            'assignments' => Assignment::where('school_id', $this->schoolId())
                ->with('lesson:id,title')->orderByDesc('due_date')->paginate(20),
            'lessons'     => Lesson::where('school_id', $this->schoolId())->orderBy('title')->get(),
        ]);
    }

    public function storeAssignment(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'lesson_id'    => 'required|exists:lessons,id',
            'title'        => 'required|string|max:255',
            'instructions' => 'nullable|string',
            'due_date'     => 'required|date',
            'total_marks'  => 'required|integer|min:1|max:1000',
        ]);
        $data['school_id'] = $this->schoolId();
        Assignment::create($data);
        return back()->with('success', 'Tugas dibuat.');
    }

    public function deleteAssignment(Assignment $assignment): RedirectResponse
    {
        $this->authorizeOwn($assignment);
        $assignment->delete();
        return back()->with('success', 'Tugas dihapus.');
    }

    /* ============== QUESTION BANK ============== */

    public function questionBankCategories(): View
    {
        return view('school-admin.qbank.categories', [
            'categories' => QuestionBankCategory::where('school_id', $this->schoolId())
                ->with('subject')->orderBy('name')->get(),
            'subjects'   => Subject::where('school_id', $this->schoolId())->get(),
        ]);
    }

    public function storeQuestionBankCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'       => 'required|string|max:200',
            'subject_id' => 'required|exists:subjects,id',
        ]);
        $data['school_id'] = $this->schoolId();
        QuestionBankCategory::create($data);
        return back()->with('success', 'Kategori soal ditambahkan.');
    }

    public function deleteQuestionBankCategory(QuestionBankCategory $category): RedirectResponse
    {
        $this->authorizeOwn($category);
        $category->delete();
        return back()->with('success', 'Kategori dihapus.');
    }

    public function questionBankItems(): View
    {
        return view('school-admin.qbank.items', [
            'items'    => QuestionBankItem::where('school_id', $this->schoolId())
                ->with(['category', 'subject'])->orderByDesc('created_at')->paginate(25),
            'categories' => QuestionBankCategory::where('school_id', $this->schoolId())->get(),
            'subjects' => Subject::where('school_id', $this->schoolId())->get(),
        ]);
    }

    public function storeQuestionBankItem(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject_id'                => 'required|exists:subjects,id',
            'question_bank_category_id' => 'required|exists:question_bank_categories,id',
            'question_html'             => 'required|string|max:10000',
            'type'                      => 'required|in:multiple_choice,true_false,short_answer,essay',
            'difficulty'                => 'required|in:easy,medium,hard',
            'cognitive_level'           => 'nullable|in:remembering,understanding,applying,analyzing,evaluating,creating',
            'answer_key'                => 'nullable|string',
            'explanation_html'          => 'nullable|string',
            'tags'                      => 'nullable|string',
            'options_text'              => 'nullable|string',
            'is_published'              => 'nullable|boolean',
        ]);

        $options = $this->parseOptions($data['options_text'] ?? '');
        $answerKey = $data['answer_key'] ?? null;

        if ($data['type'] === 'multiple_choice' && $options) {
            $answerKey = $answerKey ?: implode(',', collect($options)->where('is_correct')->pluck('text')->all());
        }

        $answerKey = $answerKey === null || $answerKey === '' ? '' : $answerKey;

        $data['school_id'] = $this->schoolId();
        $data['author_id'] = auth()->id();
        $data['options']   = $options;
        $data['answer_key'] = $answerKey;
        $data['tags']      = $this->parseTags($data['tags'] ?? '');
        $data['is_published'] = $request->boolean('is_published');

        unset($data['options_text'], $data['tags_text']);

        QuestionBankItem::create($data);
        return back()->with('success', 'Soal ditambahkan ke bank.');
    }

    private function parseOptions(string $raw): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $raw))
            ->map(fn ($line) => trim($line))
            ->filter(fn ($line) => $line !== '')
            ->map(function ($line) {
                $isCorrect = str_starts_with($line, '*');
                return [
                    'text'       => $isCorrect ? ltrim(substr($line, 1)) : $line,
                    'is_correct' => $isCorrect,
                ];
            })
            ->values()
            ->all();
    }

    private function parseTags(string $raw): array
    {
        return collect(preg_split('/,/', $raw))
            ->map(fn ($t) => trim($t))
            ->filter(fn ($t) => $t !== '')
            ->values()
            ->all();
    }

    public function deleteQuestionBankItem(QuestionBankItem $item): RedirectResponse
    {
        $this->authorizeOwn($item);
        $item->delete();
        return back()->with('success', 'Soal dihapus.');
    }

    /* ============== EXTRACURRICULAR ============== */

    public function extracurriculars(): View
    {
        return view('school-admin.extracurricular.list', [
            'extras' => Extracurricular::where('school_id', $this->schoolId())
                ->with('coach:id,name')->orderBy('name')->get(),
            'coaches' => User::where('school_id', $this->schoolId())
                ->whereHas('roles', fn($q) => $q->whereIn('name', ['teacher', 'admin']))
                ->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function storeExtracurricular(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'                 => 'required|string|max:200',
            'description'          => 'nullable|string',
            'coach_id'             => 'nullable|exists:users,id',
            'schedule'             => 'nullable|string|max:200',
            'capacity'             => 'nullable|integer|min:1',
            'fee_per_month_rupiah' => 'nullable|numeric|min:0',
        ]);
        Extracurricular::create([
            'school_id'     => $this->schoolId(),
            'name'          => $data['name'],
            'description'   => $data['description'] ?? null,
            'coach_id'      => $data['coach_id'] ?? null,
            'schedule'      => !empty($data['schedule']) ? ['description' => $data['schedule']] : null,
            'capacity'      => $data['capacity'] ?? null,
            'fee_per_month' => isset($data['fee_per_month_rupiah']) ? (int)($data['fee_per_month_rupiah']*100) : 0,
            'is_active'     => true,
        ]);
        return back()->with('success', 'Ekstrakurikuler ditambahkan.');
    }

    public function deleteExtracurricular(Extracurricular $extra): RedirectResponse
    {
        $this->authorizeOwn($extra);
        $extra->delete();
        return back()->with('success', 'Ekstrakurikuler dihapus.');
    }

    /* ============== CURRICULUM FRAMEWORKS ============== */

    public function curriculumFrameworks(): View
    {
        return view('school-admin.curriculum.frameworks', [
            'frameworks' => CurriculumFramework::where('school_id', $this->schoolId())->orderBy('name')->get(),
        ]);
    }

    public function storeCurriculumFramework(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'type' => 'required|string|max:50',
        ]);
        CurriculumFramework::create([
            'school_id' => $this->schoolId(),
            'name'      => $data['name'],
            'type'      => $data['type'],
            'config'    => [],
            'is_active' => true,
        ]);
        return back()->with('success', 'Framework kurikulum ditambahkan.');
    }

    public function deleteCurriculumFramework(CurriculumFramework $framework): RedirectResponse
    {
        $this->authorizeOwn($framework);
        $framework->delete();
        return back()->with('success', 'Framework dihapus.');
    }
}
