<?php

namespace App\Http\Controllers\Web\Admin\Ai;

use App\Http\Controllers\Controller;
use App\Models\AI\AiModel;
use App\Models\AI\AiProvider;
use App\Models\Academic\Subject;
use App\Models\Academic\Student;
use App\Models\QuestionBank\QuestionBankItem;
use App\Services\AI\AiModulAjarGenerator;
use App\Services\AI\AiRubricGenerator;
use App\Services\AI\AiWorksheetGenerator;
use App\Services\AI\AiQuestionVariationGenerator;
use App\Services\AI\AiRemedialGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiTeacherAssistantController extends Controller
{
    public function __construct(
        protected AiModulAjarGenerator $modulAjar,
        protected AiRubricGenerator $rubric,
        protected AiWorksheetGenerator $worksheet,
        protected AiQuestionVariationGenerator $variation,
        protected AiRemedialGenerator $remedial,
    ) {}

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(): View
    {
        $schoolId = $this->schoolId();

        return view('school-admin.ai.teacher-assistant', [
            'subjects'  => Subject::where('school_id', $schoolId)->orderBy('name')->get(),
            'students'  => Student::where('school_id', $schoolId)->with('user:id,name')->orderBy('admission_no')->get(),
            'providers' => AiProvider::where('school_id', $schoolId)->where('is_active', true)->orderBy('priority')->get(),
            'aiModels'  => AiModel::where('school_id', $schoolId)->where('is_active', true)->with('provider')->orderBy('priority')->get(),
        ]);
    }

    public function generateModulAjar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'subject_name'    => 'required|string|max:255',
            'topic'           => 'required|string|max:255',
            'grade_level'     => 'required|string|max:50',
            'hours'           => 'required|integer|min:1|max:20',
            'ai_provider_id'  => 'nullable|exists:ai_providers,id',
            'ai_model_id'     => 'nullable|exists:ai_models,id',
        ]);

        try {
            $result = $this->modulAjar->generate(
                $this->schoolId(), auth()->id(),
                $data['subject_name'], $data['topic'],
                $data['grade_level'], (int) $data['hours'],
                $data['ai_provider_id'] ?? null, $data['ai_model_id'] ?? null,
            );
            return response()->json(['success' => true] + $result);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function generateRubric(Request $request): JsonResponse
    {
        $data = $request->validate([
            'assignment_title' => 'required|string|max:255',
            'criteria'         => 'required|array|min:1',
            'criteria.*'       => 'required|string|max:200',
            'max_score'        => 'required|integer|min:1|max:100',
            'ai_provider_id'   => 'nullable|exists:ai_providers,id',
            'ai_model_id'      => 'nullable|exists:ai_models,id',
        ]);

        try {
            $result = $this->rubric->generate(
                $this->schoolId(), auth()->id(),
                $data['assignment_title'], $data['criteria'], (int) $data['max_score'],
                $data['ai_provider_id'] ?? null, $data['ai_model_id'] ?? null,
            );
            return response()->json(['success' => true] + $result);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function generateWorksheet(Request $request): JsonResponse
    {
        $data = $request->validate([
            'subject_name'    => 'required|string|max:255',
            'topic'           => 'required|string|max:255',
            'grade_level'     => 'required|string|max:50',
            'question_count'  => 'required|integer|min:1|max:50',
            'subject_id'      => 'nullable|exists:subjects,id',
            'category_id'     => 'nullable|exists:question_bank_categories,id',
            'ai_provider_id'  => 'nullable|exists:ai_providers,id',
            'ai_model_id'     => 'nullable|exists:ai_models,id',
        ]);

        try {
            $result = $this->worksheet->generate(
                $this->schoolId(), auth()->id(),
                $data['subject_name'], $data['topic'],
                $data['grade_level'], (int) $data['question_count'],
                $data['ai_provider_id'] ?? null, $data['ai_model_id'] ?? null,
            );
            return response()->json(['success' => true] + $result);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function saveWorksheet(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'subject_name'    => 'required|string|max:255',
            'topic'           => 'required|string|max:255',
            'grade_level'     => 'required|string|max:50',
            'question_count'  => 'required|integer|min:1|max:50',
            'subject_id'      => 'nullable|exists:subjects,id',
            'category_id'     => 'nullable|exists:question_bank_categories,id',
            'ai_provider_id'  => 'nullable|exists:ai_providers,id',
            'ai_model_id'     => 'nullable|exists:ai_models,id',
        ]);

        try {
            $result = $this->worksheet->generateAndSave(
                $this->schoolId(), auth()->id(),
                [
                    'subject_name'  => $data['subject_name'],
                    'topic'         => $data['topic'],
                    'grade_level'   => $data['grade_level'],
                    'question_count'=> $data['question_count'],
                    'subject_id'    => $data['subject_id'] ?? null,
                    'category_id'   => $data['category_id'] ?? null,
                ],
                $data['ai_provider_id'] ?? null, $data['ai_model_id'] ?? null,
            );
            return back()->with('success', count($result['items']) . ' soal berhasil disimpan ke Bank Soal.');
        } catch (\Throwable $e) {
            return back()->withErrors('Gagal generate worksheet: ' . $e->getMessage());
        }
    }

    public function generateVariation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'question_id'     => 'required|exists:question_bank_items,id',
            'variation_count' => 'required|integer|min:1|max:10',
            'ai_provider_id'  => 'nullable|exists:ai_providers,id',
            'ai_model_id'     => 'nullable|exists:ai_models,id',
        ]);

        try {
            $result = $this->variation->generate(
                $this->schoolId(), auth()->id(),
                (int) $data['question_id'], (int) $data['variation_count'],
                $data['ai_provider_id'] ?? null, $data['ai_model_id'] ?? null,
            );
            return response()->json(['success' => true] + $result);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function saveVariation(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'question_id'     => 'required|exists:question_bank_items,id',
            'variation_count' => 'required|integer|min:1|max:10',
            'ai_provider_id'  => 'nullable|exists:ai_providers,id',
            'ai_model_id'     => 'nullable|exists:ai_models,id',
        ]);

        try {
            $result = $this->variation->generateAndSave(
                $this->schoolId(), auth()->id(),
                (int) $data['question_id'], (int) $data['variation_count'],
                $data['ai_provider_id'] ?? null, $data['ai_model_id'] ?? null,
            );
            return back()->with('success', count($result['items']) . ' variasi soal berhasil disimpan.');
        } catch (\Throwable $e) {
            return back()->withErrors('Gagal generate variasi: ' . $e->getMessage());
        }
    }

    public function generateRemedial(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_id'      => 'required|exists:students,id',
            'subject_name'    => 'required|string|max:255',
            'weak_topics'     => 'required|array|min:1',
            'weak_topics.*'   => 'required|string|max:200',
            'ai_provider_id'  => 'nullable|exists:ai_providers,id',
            'ai_model_id'     => 'nullable|exists:ai_models,id',
        ]);

        try {
            $result = $this->remedial->generate(
                $this->schoolId(), auth()->id(),
                (int) $data['student_id'], $data['subject_name'],
                $data['weak_topics'],
                $data['ai_provider_id'] ?? null, $data['ai_model_id'] ?? null,
            );
            return response()->json(['success' => true] + $result);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
