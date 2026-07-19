<?php

namespace App\Http\Controllers\Api\LiveClass;

use App\Http\Controllers\Controller;
use App\Models\LiveClass\LiveClassAttendance;
use App\Models\LiveClass\LiveClassSession;
use App\Models\LiveClass\VideoProvider;
use App\Services\LiveClass\LiveClassService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LiveClassController extends Controller
{
    public function __construct(private LiveClassService $service) {}

    public function providers(Request $request): JsonResponse
    {
        return response()->json([
            'data' => VideoProvider::where('school_id', $request->user()->school_id)->get()
                ->map(fn ($p) => [
                    'id'         => $p->id,
                    'name'       => $p->name,
                    'slug'       => $p->slug,
                    'api_format' => $p->api_format,
                    'base_url'   => $p->base_url,
                    'is_active'  => $p->is_active,
                ]),
        ]);
    }

    public function storeProvider(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'           => 'required|string|max:200',
            'api_format'     => 'required|in:oauth_meeting_api,rest_room_api,self_hosted_jitsi,self_hosted_bbb,manual_link',
            'base_url'       => 'nullable|url|max:500',
            'client_id'      => 'nullable|string|max:500',
            'client_secret'  => 'nullable|string|max:500',
            'access_token'   => 'nullable|string|max:1000',
            'extra_config'   => 'nullable|array',
            'is_active'      => 'nullable|boolean',
        ]);

        $p = new VideoProvider();
        $p->school_id    = $request->user()->school_id;
        $p->name         = $data['name'];
        $p->slug         = Str::slug($data['name']) . '-' . Str::lower(Str::random(4));
        $p->api_format   = $data['api_format'];
        $p->base_url     = $data['base_url'] ?? null;
        $p->extra_config = $data['extra_config'] ?? null;
        $p->is_active    = (bool) ($data['is_active'] ?? true);
        if (!empty($data['client_id'])) $p->client_id = $data['client_id'];
        if (!empty($data['client_secret'])) $p->client_secret = $data['client_secret'];
        if (!empty($data['access_token'])) $p->access_token = $data['access_token'];
        $p->save();

        return response()->json($p, 201);
    }

    public function sessions(Request $request): JsonResponse
    {
        $sessions = LiveClassSession::where('school_id', $request->user()->school_id)
            ->when($request->input('class_section_id'), fn ($q, $cid) => $q->where('class_section_id', $cid))
            ->when($request->input('teacher_id'), fn ($q, $tid) => $q->where('teacher_id', $tid))
            ->orderByDesc('scheduled_start')
            ->paginate(50);

        return response()->json($sessions);
    }

    public function schedule(Request $request): JsonResponse
    {
        $data = $request->validate([
            'class_section_id'  => 'required|integer',
            'subject_id'        => 'required|integer',
            'video_provider_id' => 'nullable|integer',
            'topic'             => 'required|string|max:200',
            'scheduled_start'   => 'required|date',
            'duration_minutes'  => 'required|integer|min:5|max:480',
        ]);

        $session = $this->service->schedule(
            $request->user()->school_id,
            $request->user()->id,
            $data,
        );

        return response()->json($session, 201);
    }

    public function start(Request $request, int $id): JsonResponse
    {
        $s = LiveClassSession::where('school_id', $request->user()->school_id)->findOrFail($id);
        return response()->json($this->service->start($s));
    }

    public function end(Request $request, int $id): JsonResponse
    {
        $s = LiveClassSession::where('school_id', $request->user()->school_id)->findOrFail($id);
        return response()->json($this->service->end($s, $request->input('recording_url')));
    }

    public function join(Request $request, int $id): JsonResponse
    {
        $s = LiveClassSession::where('school_id', $request->user()->school_id)->findOrFail($id);

        $student = $request->user()->student;
        if ($student) {
            LiveClassAttendance::firstOrCreate(
                ['live_class_session_id' => $s->id, 'student_id' => $student->id],
                ['school_id' => $request->user()->school_id, 'joined_at' => now()],
            );
        }

        return response()->json([
            'join_url' => $s->join_url,
            'passcode' => $s->passcode,
            'session'  => $s,
        ]);
    }

    public function recordLeave(Request $request, int $id): JsonResponse
    {
        $student = $request->user()->student;
        if (!$student) return response()->json(['ok' => true]);

        $att = LiveClassAttendance::where('live_class_session_id', $id)
            ->where('student_id', $student->id)
            ->first();

        if ($att && $att->joined_at && !$att->left_at) {
            $minutes = now()->diffInMinutes($att->joined_at);
            $att->update(['left_at' => now(), 'total_minutes' => $minutes]);
        }

        return response()->json(['ok' => true]);
    }
}
