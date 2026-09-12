<?php

namespace App\Services;

use App\Models\Academic\Attendance;
use App\Models\Academic\Mark;
use App\Models\Academic\Student;
use App\Models\Finance\FeeInvoice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportBuilderService
{
    private int $schoolId;

    public function __construct()
    {
        $this->schoolId = auth()->user()->school_id;
    }

    public function buildQuery(array $config): array
    {
        $dataSource = $config['data_source'] ?? 'students';
        $columns    = $config['columns'] ?? [];
        $filters    = $config['filters'] ?? [];
        $grouping   = $config['grouping'] ?? null;
        $chartConfig = $config['chart_config'] ?? null;

        $query = $this->getBaseQuery($dataSource);
        $query = $this->applyColumns($query, $columns, $dataSource);
        $query = $this->applyFilters($query, $filters, $dataSource);

        $chartData = [];
        if ($grouping) {
            $rawResults = $query->get();
            $grouped = $rawResults->groupBy($grouping['field']);
        } else {
            $grouped = $query->get();
        }

        if ($chartConfig) {
            $chartData = $this->buildChartData($query, $chartConfig, $grouping);
        }

        return [
            'query_builder' => $query,
            'grouped'       => $grouped,
            'chart_data'    => $chartData,
            'columns'       => $this->resolveColumnLabels($columns, $dataSource),
        ];
    }

    public function getPreviewData(array $config, int $limit = 50): array
    {
        $dataSource = $config['data_source'] ?? 'students';
        $columns    = $config['columns'] ?? [];
        $filters    = $config['filters'] ?? [];
        $grouping   = $config['grouping'] ?? null;

        $query = $this->getBaseQuery($dataSource);
        $query = $this->applyFilters($query, $filters, $dataSource);

        if ($grouping && !empty($grouping['field'])) {
            $groupField = $grouping['field'];
            $aggFunc = $grouping['aggregate'] ?? 'count';
            $aggTarget = $grouping['aggregate_target'] ?? '*';
            $aggTarget = $aggTarget ?: '*';

            $query = $this->applyGroupedSelect($query, $columns, $dataSource, $groupField, $aggFunc, $aggTarget);
            $results = $query->groupBy($this->fieldReference($groupField, $dataSource))->limit($limit)->get();
        } else {
            $results = $query->select($this->resolveSelects($columns, $dataSource))->limit($limit)->get();
        }

        return [
            'rows'        => $results->toArray(),
            'columns'     => $this->resolveColumnLabels($columns, $dataSource),
            'total_count' => $this->getTotalCount($config),
        ];
    }

    public function exportCsv(array $config): StreamedResponse
    {
        $dataSource = $config['data_source'] ?? 'students';
        $columns    = $config['columns'] ?? [];
        $filters    = $config['filters'] ?? [];
        $grouping   = $config['grouping'] ?? null;
        $labels     = $this->resolveColumnLabels($columns, $dataSource);

        $fileName = 'laporan-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($dataSource, $columns, $filters, $grouping, $labels) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, array_values($labels));

            $query = $this->getBaseQuery($dataSource);
            $query = $this->applyFilters($query, $filters, $dataSource);

            if ($grouping && !empty($grouping['field'])) {
                $groupField = $grouping['field'];
                $aggFunc = $grouping['aggregate'] ?? 'count';
                $aggTarget = $grouping['aggregate_target'] ?? '*';
                $aggTarget = $aggTarget ?: '*';

                $query = $this->applyGroupedSelect($query, $columns, $dataSource, $groupField, $aggFunc, $aggTarget);
                $results = $query->groupBy($this->fieldReference($groupField, $dataSource))->cursor();
            } else {
                $results = $query->select($this->resolveSelects($columns, $dataSource))->cursor();
            }

            foreach ($results as $row) {
                $data = [];
                foreach ($columns as $col) {
                    $field = $col['field'] ?? $col;
                    $data[] = data_get($row, $field, '');
                }
                fputcsv($handle, $data);
            }

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportPdf(array $config): string
    {
        $preview = $this->getPreviewData($config, 500);
        $chartConfig = $config['chart_config'] ?? null;
        $chartData = null;

        if ($chartConfig) {
            $query = $this->getBaseQuery($config['data_source'] ?? 'students');
            $query = $this->applyFilters($query, $config['filters'] ?? [], $config['data_source'] ?? 'students');
            $chartData = $this->buildChartData($query, $chartConfig, $config['grouping'] ?? null);
        }

        $html = view('school-admin.reports.pdf-report-builder', [
            'title'      => $config['name'] ?? 'Laporan Kustom',
            'columns'    => $preview['columns'],
            'rows'       => $preview['rows'],
            'chartData'  => $chartData,
            'chartConfig'=> $chartConfig,
            'schoolName' => auth()->user()->school->name ?? 'Sekolah',
            'generatedAt'=> now()->translatedFormat('d F Y H:i'),
        ])->render();

        $pdf = app('dompdf.wrapper');
        $pdf->loadHTML($html);
        $pdf->setPaper('a4', 'landscape');

        $path = 'reports/laporan-' . now()->format('Ymd-His') . '.pdf';
        \Illuminate\Support\Facades\Storage::disk('local')->put($path, $pdf->output());

        return $path;
    }

    public function getTotalCount(array $config): int
    {
        $dataSource = $config['data_source'] ?? 'students';
        $filters    = $config['filters'] ?? [];

        $query = $this->getBaseQuery($dataSource);
        $query = $this->applyFilters($query, $filters, $dataSource);

        return $query->count();
    }

    private function getBaseQuery(string $dataSource): Builder|QueryBuilder
    {
        $schoolId = $this->schoolId;

        return match ($dataSource) {
            'students' => Student::with('user', 'classSection.classRoom', 'classSection.section')
                ->leftJoin('users as u', 'students.user_id', '=', 'u.id')
                ->leftJoin('class_sections as cs', 'students.class_section_id', '=', 'cs.id')
                ->leftJoin('class_rooms as cr', 'cs.class_room_id', '=', 'cr.id')
                ->leftJoin('sections as sec', 'cs.section_id', '=', 'sec.id')
                ->where('students.school_id', $schoolId)
                ->select('students.*'),

            'marks' => DB::table('marks as m')
                ->join('students as s', 'm.student_id', '=', 's.id')
                ->join('users as u', 's.user_id', '=', 'u.id')
                ->join('subjects as sub', 'm.subject_id', '=', 'sub.id')
                ->leftJoin('exams as e', 'm.exam_id', '=', 'e.id')
                ->leftJoin('class_sections as cs', 's.class_section_id', '=', 'cs.id')
                ->leftJoin('class_rooms as cr', 'cs.class_room_id', '=', 'cr.id')
                ->leftJoin('sections as sec', 'cs.section_id', '=', 'sec.id')
                ->where('m.school_id', $schoolId),

            'attendance' => DB::table('attendances as a')
                ->join('students as s', 'a.student_id', '=', 's.id')
                ->join('users as u', 's.user_id', '=', 'u.id')
                ->leftJoin('class_sections as cs', 'a.class_section_id', '=', 'cs.id')
                ->leftJoin('class_rooms as cr', 'cs.class_room_id', '=', 'cr.id')
                ->leftJoin('sections as sec', 'cs.section_id', '=', 'sec.id')
                ->where('a.school_id', $schoolId),

            'invoices' => DB::table('fee_invoices as fi')
                ->join('students as s', 'fi.student_id', '=', 's.id')
                ->join('users as u', 's.user_id', '=', 'u.id')
                ->leftJoin('fee_payments as fp', 'fp.fee_invoice_id', '=', 'fi.id')
                ->leftJoin('class_sections as cs', 's.class_section_id', '=', 'cs.id')
                ->leftJoin('class_rooms as cr', 'cs.class_room_id', '=', 'cr.id')
                ->leftJoin('sections as sec', 'cs.section_id', '=', 'sec.id')
                ->where('fi.school_id', $schoolId),

            'payments' => DB::table('fee_payments as fp')
                ->join('fee_invoices as fi', 'fp.fee_invoice_id', '=', 'fi.id')
                ->join('students as s', 'fi.student_id', '=', 's.id')
                ->join('users as u', 's.user_id', '=', 'u.id')
                ->leftJoin('class_sections as cs', 's.class_section_id', '=', 'cs.id')
                ->leftJoin('class_rooms as cr', 'cs.class_room_id', '=', 'cr.id')
                ->leftJoin('sections as sec', 'cs.section_id', '=', 'sec.id')
                ->where('fi.school_id', $schoolId),

            'staff' => DB::table('staffs as st')
                ->leftJoin('users as u', 'st.user_id', '=', 'u.id')
                ->where('st.school_id', $schoolId),

            default => Student::where('school_id', $schoolId),
        };
    }

    private function applyColumns(Builder|QueryBuilder $query, array $columns, string $dataSource): Builder|QueryBuilder
    {
        $selects = $this->resolveSelects($columns, $dataSource);
        return $query->select($selects);
    }

    private function resolveSelects(array $columns, string $dataSource): array
    {
        $selects = [];

        foreach ($columns as $col) {
            $field = is_string($col) ? $col : ($col['field'] ?? '');
            $computed = is_string($col) ? false : ($col['computed'] ?? false);

            if ($computed) {
                $selects[] = DB::raw($this->resolveComputedExpression($field, $dataSource) . ' as ' . $field);
            } else {
                $selects[] = DB::raw($this->fieldExpression($field, $dataSource));
            }
        }

        return $selects;
    }

    private function resolveComputedExpression(string $field, string $dataSource): string
    {
        return match ($field) {
            'age'       => $dataSource === 'students'
                ? 'TIMESTAMPDIFF(YEAR, students.date_of_birth, CURDATE())'
                : 'TIMESTAMPDIFF(YEAR, s.date_of_birth, CURDATE())',
            'gpa'       => 'ROUND(AVG(m.obtained_marks / NULLIF(m.total_marks, 0) * 100), 2)',
            'attendance_pct' => 'ROUND(SUM(CASE WHEN a.status IN (\'present\',\'late\') THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0) * 100, 1)',
            'arrears'   => 'SUM(fi.amount - fi.paid_amount)',
            'payment_count' => 'COUNT(fp.id)',
            'total_paid'    => 'SUM(fp.amount)',
            'fee_collection_pct' => 'ROUND(SUM(fi.paid_amount) / NULLIF(SUM(fi.amount), 0) * 100, 1)',
            'student_count' => 'COUNT(s.id)',
            default     => $field,
        };
    }

    private function applyFilters(Builder|QueryBuilder $query, array $filters, string $dataSource): Builder|QueryBuilder
    {
        foreach ($filters as $filter) {
            $field    = $filter['field'] ?? '';
            $operator = $filter['operator'] ?? '=';
            $value    = $filter['value'] ?? null;

            if (empty($value) && $value !== '0' && $value !== 0) {
                continue;
            }

            switch ($field) {
                case 'class_section_id':
                    $query->where(function ($q) use ($dataSource, $value) {
                        if ($dataSource === 'students') {
                            $q->where('class_section_id', $value);
                        } elseif (in_array($dataSource, ['attendance'])) {
                            $q->where('a.class_section_id', $value);
                        }
                    });
                    break;

                case 'gender':
                    if ($dataSource === 'students') {
                        $query->where('gender', $value);
                    } elseif ($dataSource === 'staff') {
                        $query->where('st.gender', $value);
                    }
                    break;

                case 'date_from':
                    $dateField = $this->dateFieldForSource($dataSource);
                    if ($dateField) {
                        $query->where($dateField, '>=', $value);
                    }
                    break;

                case 'date_to':
                    $dateField = $this->dateFieldForSource($dataSource);
                    if ($dateField) {
                        $query->where($dateField, '<=', $value);
                    }
                    break;

                case 'month':
                    $dateField = $this->dateFieldForSource($dataSource);
                    if ($dateField) {
                        $query->whereYear($dateField, substr($value, 0, 4))
                              ->whereMonth($dateField, substr($value, 5, 2));
                    }
                    break;

                case 'status':
                    if ($dataSource === 'invoices') {
                        $query->where('fi.status', $value);
                    } elseif ($dataSource === 'attendance') {
                        $query->where('a.status', $value);
                    }
                    break;

                case 'exam_id':
                    if ($dataSource === 'marks') {
                        $query->where('m.exam_id', $value);
                    }
                    break;

                case 'subject_id':
                    if ($dataSource === 'marks') {
                        $query->where('m.subject_id', $value);
                    }
                    break;

                case 'semester_id':
                    if ($dataSource === 'marks') {
                        $query->where('m.semester_id', $value);
                    }
                    break;

                default:
                    if ($operator === 'between' && is_array($value) && count($value) === 2) {
                        $query->whereBetween($field, $value);
                    } elseif ($operator === 'in' && is_array($value)) {
                        $query->whereIn($field, $value);
                    } elseif ($operator === 'like') {
                        $query->where($field, 'like', '%' . $value . '%');
                    } else {
                        $query->where($field, $operator, $value);
                    }
                    break;
            }
        }

        return $query;
    }

    private function dateFieldForSource(string $dataSource): ?string
    {
        return match ($dataSource) {
            'attendance' => 'a.date',
            'marks'      => 'm.created_at',
            'invoices'   => 'fi.due_date',
            'payments'   => 'fp.payment_date',
            default      => null,
        };
    }

    private function applyGroupedSelect(Builder|QueryBuilder $query, array $columns, string $dataSource, string $groupField, string $aggFunc, string $aggTarget): Builder|QueryBuilder
    {
        $selects = [DB::raw($this->fieldExpression($groupField, $dataSource))];
        $aggLabel = "{$aggFunc}_{$aggTarget}";

        if (! in_array(strtolower($aggFunc), ['count', 'sum', 'avg', 'max', 'min'], true)) {
            throw new \InvalidArgumentException('Fungsi agregasi tidak valid.');
        }

        if ($aggFunc === 'count' && $aggTarget === '*') {
            $selects[] = DB::raw('COUNT(*) as total_count');
        } else {
            $aggregateTarget = $aggTarget === '*' ? '*' : $this->fieldReference($aggTarget, $dataSource);
            $selects[] = DB::raw(strtoupper($aggFunc) . "({$aggregateTarget}) as {$aggLabel}");
        }

        // Saat memakai GROUP BY, kolom detail lain tidak boleh ikut SELECT:
        // MySQL dengan ONLY_FULL_GROUP_BY akan menolaknya. Hasil grup hanya
        // memuat field grup dan nilai agregat.
        return $query->select($selects);
    }

    private function fieldExpression(string $field, string $dataSource): string
    {
        return $this->fieldReference($field, $dataSource) . ' as ' . $field;
    }

    private function fieldReference(string $field, string $dataSource): string
    {
        $fields = [
            'students' => [
                'name' => 'u.name', 'student_name' => 'u.name',
                'admission_no' => 'students.admission_no', 'gender' => 'students.gender',
                'date_of_birth' => 'students.date_of_birth', 'guardian_name' => 'students.guardian_name',
                'guardian_phone' => 'students.guardian_phone', 'class_name' => 'cr.name',
                'section_name' => 'sec.name', 'month' => "DATE_FORMAT(students.created_at, '%Y-%m')",
            ],
            'marks' => [
                'name' => 'u.name', 'student_name' => 'u.name', 'admission_no' => 's.admission_no',
                'gender' => 's.gender', 'date_of_birth' => 's.date_of_birth', 'guardian_name' => 's.guardian_name',
                'guardian_phone' => 's.guardian_phone', 'obtained_marks' => 'm.obtained_marks',
                'total_marks' => 'm.total_marks', 'grade' => 'm.grade',
                'subject_name' => 'sub.name', 'exam_name' => 'e.title', 'date' => 'm.created_at',
                'class_name' => 'cr.name', 'section_name' => 'sec.name',
                'month' => "DATE_FORMAT(m.created_at, '%Y-%m')",
            ],
            'attendance' => [
                'name' => 'u.name', 'student_name' => 'u.name', 'admission_no' => 's.admission_no',
                'gender' => 's.gender', 'date_of_birth' => 's.date_of_birth', 'guardian_name' => 's.guardian_name',
                'guardian_phone' => 's.guardian_phone', 'date' => 'a.date', 'status' => 'a.status',
                'class_name' => 'cr.name', 'section_name' => 'sec.name',
                'month' => "DATE_FORMAT(a.date, '%Y-%m')",
            ],
            'invoices' => [
                'name' => 'u.name', 'student_name' => 'u.name', 'admission_no' => 's.admission_no',
                'gender' => 's.gender', 'date_of_birth' => 's.date_of_birth', 'guardian_name' => 's.guardian_name',
                'guardian_phone' => 's.guardian_phone', 'invoice_no' => 'fi.invoice_no', 'amount' => 'fi.amount',
                'paid_amount' => 'fi.paid_amount', 'status' => 'fi.status', 'date' => 'fi.due_date',
                'month' => "DATE_FORMAT(fi.due_date, '%Y-%m')",
            ],
            'payments' => [
                'name' => 'u.name', 'student_name' => 'u.name', 'admission_no' => 's.admission_no',
                'gender' => 's.gender', 'date_of_birth' => 's.date_of_birth', 'guardian_name' => 's.guardian_name',
                'guardian_phone' => 's.guardian_phone', 'invoice_no' => 'fi.invoice_no', 'amount' => 'fp.amount',
                'paid_amount' => 'fp.amount', 'date' => 'fp.payment_date',
                'month' => "DATE_FORMAT(fp.payment_date, '%Y-%m')",
            ],
            'staff' => [
                'name' => 'u.name', 'phone' => 'u.phone', 'gender' => 'u.gender',
                'subject_name' => 'st.designation', 'class_name' => 'st.department',
                'section_name' => 'st.designation', 'month' => "DATE_FORMAT(st.created_at, '%Y-%m')",
            ],
        ];

        $reference = $fields[$dataSource][$field] ?? null;
        if (! $reference) {
            throw new \InvalidArgumentException("Kolom laporan tidak valid: {$field}");
        }

        return $reference;
    }

    private function buildChartData(Builder|QueryBuilder $query, array $chartConfig, ?array $grouping): array
    {
        $type = $chartConfig['type'] ?? 'bar';
        $labelField = $chartConfig['label_field'] ?? 'name';
        $valueField = $chartConfig['value_field'] ?? 'total';

        $raw = $query->get();

        $labels = $raw->pluck($labelField)->unique()->values()->toArray();
        $values = $raw->pluck($valueField)->toArray();

        $datasets = [
            [
                'label'           => $chartConfig['label'] ?? 'Data',
                'data'            => $values,
                'backgroundColor' => $chartConfig['colors'] ?? [
                    'rgba(37,99,235,0.6)', 'rgba(184,134,11,0.6)',
                    'rgba(16,185,129,0.6)', 'rgba(234,179,8,0.6)',
                    'rgba(220,38,38,0.6)', 'rgba(147,51,234,0.6)',
                ],
                'borderColor' => $chartConfig['border_colors'] ?? [
                    'rgba(37,99,235,1)', 'rgba(184,134,11,1)',
                    'rgba(16,185,129,1)', 'rgba(234,179,8,1)',
                    'rgba(220,38,38,1)', 'rgba(147,51,234,1)',
                ],
                'borderWidth' => 1,
            ],
        ];

        return compact('type', 'labels', 'datasets');
    }

    private function resolveColumnLabels(array $columns, string $dataSource): array
    {
        $labels = [];
        $commonLabels = [
            'name'       => 'Nama',
            'admission_no' => 'No. Induk',
            'gender'     => 'Jenis Kelamin',
            'date_of_birth' => 'Tanggal Lahir',
            'age'        => 'Usia',
            'guardian_name'  => 'Wali',
            'guardian_phone' => 'Telp Wali',
            'student_name'=> 'Nama Siswa',
            'subject_name' => 'Mata Pelajaran',
            'obtained_marks' => 'Nilai',
            'total_marks' => 'Nilai Maks',
            'grade'      => 'Nilai Huruf',
            'exam_name'  => 'Ujian',
            'date'       => 'Tanggal',
            'status'     => 'Status',
            'attendance_pct' => '% Kehadiran',
            'gpa'        => 'IPK / Rata-rata',
            'invoice_no' => 'No. Invoice',
            'amount'     => 'Tagihan',
            'paid_amount'=> 'Terbayar',
            'arrears'    => 'Tunggakan',
            'fee_collection_pct' => '% Koleksi',
            'class_name' => 'Kelas',
            'section_name' => 'Section',
            'student_count' => 'Jumlah Siswa',
            'total_paid' => 'Total Dibayar',
            'total_count'=> 'Total',
        ];

        foreach ($columns as $col) {
            $field = is_string($col) ? $col : ($col['field'] ?? '');
            $label = is_string($col) ? $field : ($col['label'] ?? $commonLabels[$field] ?? $field);
            $labels[$field] = $label;
        }

        return $labels;
    }
}
