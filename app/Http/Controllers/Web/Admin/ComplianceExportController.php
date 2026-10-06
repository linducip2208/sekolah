<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Services\Compliance\ComplianceExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ComplianceExportController extends Controller
{
    public function __construct(private ComplianceExportService $exports) {}

    private function csv(string $filename, array $headers, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($rows as $row) {
                fputcsv($out, array_values($row));
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function leger(Request $request): StreamedResponse
    {
        $schoolId = (int) auth()->user()->school_id;
        $data = $this->exports->leger($schoolId, $request->integer('class_section_id') ?: null);

        return $this->csv('leger-e-rapor.csv', $data['headers'], $data['rows']);
    }

    public function dapodik(): StreamedResponse
    {
        $schoolId = (int) auth()->user()->school_id;
        $data = $this->exports->dapodikStudents($schoolId);

        return $this->csv('dapodik-siswa.csv', $data['headers'], $data['rows']);
    }

    public function bos(): StreamedResponse
    {
        $schoolId = (int) auth()->user()->school_id;
        $data = $this->exports->bosRealization($schoolId);

        return $this->csv('lpj-bos.csv', $data['headers'], $data['rows']);
    }
}
