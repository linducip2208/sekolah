<?php

namespace App\Http\Controllers\Api\Dapodik;

use App\Http\Controllers\Controller;
use App\Services\Dapodik\DapodikService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DapodikController extends Controller
{
    public function __construct(private DapodikService $service) {}

    public function config(Request $request): JsonResponse
    {
        return response()->json($this->service->getOrCreateConfig($request->user()->school_id));
    }

    public function updateConfig(Request $request): JsonResponse
    {
        $data = $request->validate([
            'npsn'           => 'required|string|max:15',
            'username'       => 'nullable|string|max:200',
            'password'       => 'nullable|string|max:200',
            'endpoint_url'   => 'nullable|url|max:500',
            'field_mappings' => 'nullable|array',
        ]);

        $config = $this->service->getOrCreateConfig($request->user()->school_id);
        $config->npsn           = $data['npsn'];
        $config->endpoint_url   = $data['endpoint_url'] ?? $config->endpoint_url;
        $config->field_mappings = $data['field_mappings'] ?? $config->field_mappings;
        if (!empty($data['username'])) $config->username = $data['username'];
        if (!empty($data['password'])) $config->password = $data['password'];
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
        }, 'dapodik-students-' . date('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }
}
