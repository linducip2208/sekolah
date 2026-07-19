<?php

namespace App\Http\Controllers\Api\AI;

use App\Http\Controllers\Controller;
use App\Models\AI\AiFeatureAssignment;
use App\Models\AI\AiModel;
use App\Models\AI\AiProvider;
use App\Models\AI\AiUsageLog;
use App\Services\AI\AiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AiController extends Controller
{
    public function __construct(private AiService $service) {}

    // ==== Provider admin CRUD ====
    public function providers(Request $request): JsonResponse
    {
        return response()->json([
            'data' => AiProvider::where('school_id', $request->user()->school_id)
                ->orderBy('priority')->get()->map(fn ($p) => $this->presentProvider($p)),
        ]);
    }

    public function storeProvider(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'          => 'required|string|max:200',
            'api_format'    => 'required|in:openai_compatible,anthropic_format,gemini_format,image_generic',
            'base_url'      => 'required|url|max:500',
            'api_key'       => 'nullable|string|max:500',
            'extra_headers' => 'nullable|array',
            'extra_config'  => 'nullable|array',
            'is_active'     => 'nullable|boolean',
            'priority'      => 'nullable|integer|min:0|max:1000',
        ]);

        $p = new AiProvider();
        $p->school_id     = $request->user()->school_id;
        $p->name          = $data['name'];
        $p->slug          = Str::slug($data['name']) . '-' . Str::lower(Str::random(4));
        $p->api_format    = $data['api_format'];
        $p->base_url      = $data['base_url'];
        $p->extra_headers = $data['extra_headers'] ?? null;
        $p->extra_config  = $data['extra_config'] ?? null;
        $p->is_active     = (bool) ($data['is_active'] ?? true);
        $p->priority      = (int) ($data['priority'] ?? 0);
        if (!empty($data['api_key'])) $p->api_key = $data['api_key'];
        $p->save();

        return response()->json($this->presentProvider($p), 201);
    }

    public function updateProvider(Request $request, int $id): JsonResponse
    {
        $p = AiProvider::where('school_id', $request->user()->school_id)->findOrFail($id);

        $data = $request->validate([
            'name'          => 'nullable|string|max:200',
            'base_url'      => 'nullable|url|max:500',
            'api_key'       => 'nullable|string|max:500',
            'extra_headers' => 'nullable|array',
            'extra_config'  => 'nullable|array',
            'is_active'     => 'nullable|boolean',
            'priority'      => 'nullable|integer|min:0|max:1000',
        ]);

        foreach (['name','base_url','extra_headers','extra_config','is_active','priority'] as $f) {
            if (array_key_exists($f, $data)) $p->{$f} = $data[$f];
        }
        if (!empty($data['api_key'])) $p->api_key = $data['api_key'];
        $p->save();

        return response()->json($this->presentProvider($p));
    }

    public function destroyProvider(Request $request, int $id): JsonResponse
    {
        AiProvider::where('school_id', $request->user()->school_id)->findOrFail($id)->delete();
        return response()->json(['ok' => true]);
    }

    // ==== Models ====
    public function models(Request $request): JsonResponse
    {
        $models = AiModel::where('school_id', $request->user()->school_id)
            ->with('provider:id,name,api_format')
            ->orderBy('display_name')->get();

        return response()->json(['data' => $models]);
    }

    public function storeModel(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ai_provider_id'      => 'required|integer',
            'model_name'          => 'required|string|max:200',
            'display_name'        => 'required|string|max:200',
            'capability'          => 'nullable|in:chat,completion,embedding,image_gen,image_analysis,speech_to_text,tts',
            'context_window'      => 'nullable|integer|min:128|max:2000000',
            'input_price_per_1k'  => 'nullable|numeric|min:0',
            'output_price_per_1k' => 'nullable|numeric|min:0',
            'is_active'           => 'nullable|boolean',
        ]);
        $data['school_id']  = $request->user()->school_id;
        $data['capability'] = $data['capability'] ?? 'chat';
        return response()->json(AiModel::create($data), 201);
    }

    // ==== Features ====
    public function features(Request $request): JsonResponse
    {
        return response()->json([
            'data' => AiFeatureAssignment::where('school_id', $request->user()->school_id)
                ->with('aiModel:id,model_name,display_name')
                ->get(),
        ]);
    }

    public function assignFeature(Request $request): JsonResponse
    {
        $data = $request->validate([
            'feature_key'    => 'required|string|max:50',
            'ai_model_id'    => 'required|integer',
            'feature_config' => 'nullable|array',
            'is_enabled'     => 'nullable|boolean',
        ]);
        $data['school_id'] = $request->user()->school_id;

        $assignment = AiFeatureAssignment::updateOrCreate(
            ['school_id' => $data['school_id'], 'feature_key' => $data['feature_key']],
            $data,
        );

        return response()->json($assignment, 201);
    }

    // ==== Chat features ====
    public function studyAssistant(Request $request): JsonResponse
    {
        return $this->runFeature($request, 'study_assistant');
    }

    public function lessonPlanGenerator(Request $request): JsonResponse
    {
        return $this->runFeature($request, 'lesson_plan_gen');
    }

    public function essayGrader(Request $request): JsonResponse
    {
        return $this->runFeature($request, 'essay_grader');
    }

    protected function runFeature(Request $request, string $featureKey): JsonResponse
    {
        $data = $request->validate([
            'messages'    => 'required|array|min:1',
            'messages.*.role'    => 'required|in:system,user,assistant',
            'messages.*.content' => 'required|string',
            'temperature' => 'nullable|numeric|between:0,2',
            'max_tokens'  => 'nullable|integer|min:1|max:8192',
        ]);

        try {
            $result = $this->service->chatForFeature(
                $request->user()->school_id,
                $request->user()->id,
                $featureKey,
                $data['messages'],
                array_intersect_key($data, array_flip(['temperature', 'max_tokens'])),
            );
            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function usage(Request $request): JsonResponse
    {
        $usage = AiUsageLog::where('school_id', $request->user()->school_id)
            ->when($request->input('feature_key'), fn ($q, $f) => $q->where('feature_key', $f))
            ->where('created_at', '>=', now()->subDays((int) $request->input('days', 30)))
            ->selectRaw('feature_key, COUNT(*) as request_count, SUM(input_tokens) as input_tokens, SUM(output_tokens) as output_tokens, SUM(estimated_cost) as cost')
            ->groupBy('feature_key')
            ->get();

        return response()->json(['data' => $usage]);
    }

    protected function presentProvider(AiProvider $p): array
    {
        return [
            'id'             => $p->id,
            'name'           => $p->name,
            'slug'           => $p->slug,
            'api_format'     => $p->api_format,
            'base_url'       => $p->base_url,
            'extra_headers'  => $p->extra_headers,
            'extra_config'   => $p->extra_config,
            'is_active'      => $p->is_active,
            'priority'       => $p->priority,
            'masked_api_key' => $p->maskedApiKey(),
            'has_api_key'    => !empty($p->api_key),
            'models_count'   => $p->models()->count(),
        ];
    }
}
