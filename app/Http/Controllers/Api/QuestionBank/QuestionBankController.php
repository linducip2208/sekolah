<?php

namespace App\Http\Controllers\Api\QuestionBank;

use App\Http\Controllers\Controller;
use App\Models\QuestionBank\QuestionBankCategory;
use App\Models\QuestionBank\QuestionBankItem;
use App\Services\QuestionBank\QuestionBankService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestionBankController extends Controller
{
    public function __construct(private QuestionBankService $service) {}

    public function categories(Request $request): JsonResponse
    {
        return response()->json([
            'data' => QuestionBankCategory::where('school_id', $request->user()->school_id)
                ->when($request->input('subject_id'), fn ($q, $sid) => $q->where('subject_id', $sid))
                ->orderBy('name')->get(),
        ]);
    }

    public function items(Request $request): JsonResponse
    {
        $items = QuestionBankItem::where('school_id', $request->user()->school_id)
            ->when($request->input('subject_id'), fn ($q, $sid) => $q->where('subject_id', $sid))
            ->when($request->input('category_id'), fn ($q, $cid) => $q->where('question_bank_category_id', $cid))
            ->when($request->input('difficulty'), fn ($q, $d) => $q->where('difficulty', $d))
            ->orderByDesc('id')
            ->paginate(50);

        return response()->json($items);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'subject_id'                => 'required|integer',
            'question_bank_category_id' => 'nullable|integer',
            'question_html'             => 'required|string',
            'type'                      => 'required|in:mcq,multi_select,true_false,essay,fill_blank,matching,numeric',
            'options'                   => 'nullable|array',
            'answer_key'                => 'required|array',
            'explanation_html'          => 'nullable|string',
            'difficulty'                => 'required|in:easy,medium,hard',
            'cognitive_level'           => 'required|in:c1,c2,c3,c4,c5,c6',
            'tags'                      => 'nullable|array',
            'is_published'              => 'nullable|boolean',
        ]);
        $data['school_id'] = $request->user()->school_id;
        $data['author_id'] = $request->user()->id;
        return response()->json(QuestionBankItem::create($data), 201);
    }

    public function generateExam(Request $request): JsonResponse
    {
        $data = $request->validate([
            'subject_id'      => 'required|integer',
            'category_id'     => 'nullable|integer',
            'distribution'    => 'required|array',
            'distribution.easy'   => 'nullable|integer|min:0',
            'distribution.medium' => 'nullable|integer|min:0',
            'distribution.hard'   => 'nullable|integer|min:0',
        ]);

        $items = $this->service->generateExamQuestions(
            $request->user()->school_id,
            $data['subject_id'],
            $data['distribution'],
            $data['category_id'] ?? null,
        );

        return response()->json(['data' => $items]);
    }
}
