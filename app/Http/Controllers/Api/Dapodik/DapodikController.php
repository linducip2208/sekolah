<?php

namespace App\Http\Controllers\Api\Dapodik;

use App\Http\Controllers\Controller;
use App\Models\Dapodik\DapodikConflict;
use App\Models\Dapodik\DapodikSyncRun;
use App\Services\Dapodik\DapodikService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DapodikController extends Controller
{
    public function __construct(private DapodikService $service) {}

    public function config(Request $request): JsonResponse
    {
        return response()->json($this->service->getOrCreateConnection($request->user()->school_id));
    }

    public function updateConfig(Request $request): JsonResponse
    {
        $data = $request->validate([
            'npsn' => 'required|string|max:15',
            'connection_type' => 'nullable|string|max:30',
            'host' => 'nullable|url|max:500',
            'endpoint_url' => 'nullable|url|max:500',
            'token' => 'nullable|string|max:500',
            'username' => 'nullable|string|max:200',
            'password' => 'nullable|string|max:200',
            'registration_code' => 'nullable|string|max:200',
            'timeout' => 'nullable|integer|min:1|max:300',
            'verify_ssl' => 'nullable|boolean',
            'field_mappings' => 'nullable|array',
        ]);

        $config = $this->service->getOrCreateConnection($request->user()->school_id);
        $config->fill([
            'npsn' => $data['npsn'],
            'connection_type' => $data['connection_type'] ?? $config->connection_type,
            'host' => $data['host'] ?? $data['endpoint_url'] ?? $config->host,
            'timeout' => $data['timeout'] ?? $config->timeout,
            'verify_ssl' => $data['verify_ssl'] ?? $config->verify_ssl,
            'field_mappings' => $data['field_mappings'] ?? $config->field_mappings,
            'status' => 'configured',
        ]);
        foreach (['token', 'username', 'password', 'registration_code'] as $secret) {
            if (array_key_exists($secret, $data) && filled($data[$secret])) {
                $config->setSecret($secret, $data[$secret]);
            }
        }
        $config->save();

        return response()->json($config->fresh());
    }

    public function importStudents(Request $request): JsonResponse
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:10240']);

        $log = $this->service->importStudentsFromCsv(
            $request->user()->school_id,
            $request->user()->id,
            file_get_contents($request->file('file')->getRealPath()),
        );

        return response()->json($log);
    }

    public function exportStudents(Request $request): StreamedResponse
    {
        $csv = $this->service->exportStudentsToCsv($request->user()->school_id);

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, 'dapodik-students-'.date('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    public function testConnection(Request $request): JsonResponse
    {
        return response()->json($this->service->testConnection((int) $request->user()->school_id));
    }

    public function preview(Request $request): JsonResponse
    {
        $data = $request->validate([
            'entity_type' => 'required|in:students,staff,class_sections,subjects',
            'records' => 'nullable|array',
        ]);
        $run = array_key_exists('records', $data)
            ? $this->service->createPreview((int) $request->user()->school_id, (int) $request->user()->id, $data['entity_type'], $data['records'])
            : $this->service->previewFromRemote((int) $request->user()->school_id, (int) $request->user()->id, $data['entity_type']);

        return response()->json($run);
    }

    public function confirm(Request $request, int $runId): JsonResponse
    {
        $run = DapodikSyncRun::withoutGlobalScopes()->where('school_id', $request->user()->school_id)->findOrFail($runId);

        return response()->json($this->service->confirm($run));
    }

    public function runs(Request $request): JsonResponse
    {
        return response()->json(DapodikSyncRun::withoutGlobalScopes()
            ->where('school_id', $request->user()->school_id)->with('items')->latest()->paginate(25));
    }

    public function conflicts(Request $request): JsonResponse
    {
        return response()->json(DapodikConflict::withoutGlobalScopes()
            ->where('school_id', $request->user()->school_id)->where('resolution', 'pending')->latest()->paginate(50));
    }

    public function resolveConflict(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'resolution' => 'required|in:use_local,use_external,ignore',
            'resolution_note' => 'nullable|string|max:1000',
        ]);
        $conflict = DapodikConflict::withoutGlobalScopes()
            ->where('school_id', $request->user()->school_id)->findOrFail($id);
        $conflict->update([
            'resolution' => $data['resolution'],
            'resolution_note' => $data['resolution_note'] ?? null,
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        return response()->json($conflict);
    }
}
