<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * MySQL FULLTEXT-backed global search. Falls back to LIKE if FULLTEXT unavailable.
 */
class SearchService
{
    public function searchSchool(int $schoolId, string $query, int $perEntity = 8): array
    {
        $query = trim($query);
        if (mb_strlen($query) < 2) {
            return $this->empty();
        }

        $bool = $this->buildBooleanQuery($query);

        return [
            'students' => $this->ftSearch('students', ['admission_no', 'address', 'guardian_name'],
                $schoolId, $bool, $perEntity, ['id', 'user_id', 'admission_no', 'guardian_name', 'class_section_id']),

            'users' => $this->ftSearch('users', ['name', 'email'],
                $schoolId, $bool, $perEntity, ['id', 'name', 'email', 'phone']),

            'staff' => $this->ftSearch('staffs', ['employee_id', 'department', 'designation'],
                $schoolId, $bool, $perEntity, ['id', 'user_id', 'employee_id', 'department', 'designation']),

            'invoices' => $this->ftSearch('fee_invoices', ['invoice_no', 'period'],
                $schoolId, $bool, $perEntity, ['id', 'invoice_no', 'period', 'amount', 'status', 'student_id']),

            'notices' => $this->ftSearch('notices', ['title', 'body'],
                $schoolId, $bool, $perEntity, ['id', 'title', 'created_at']),
        ];
    }

    private function ftSearch(string $table, array $cols, int $schoolId, string $bool, int $limit, array $select): array
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable($table)) {
            return [];
        }
        try {
            $colList = '`' . implode('`,`', $cols) . '`';
            $selList = '`' . implode('`,`', $select) . '`';
            $rows = DB::select(
                "SELECT {$selList}, MATCH({$colList}) AGAINST(? IN BOOLEAN MODE) AS relevance
                 FROM `{$table}`
                 WHERE school_id = ?
                   AND MATCH({$colList}) AGAINST(? IN BOOLEAN MODE)
                 ORDER BY relevance DESC
                 LIMIT {$limit}",
                [$bool, $schoolId, $bool]
            );
            return array_map(fn($r) => (array) $r, $rows);
        } catch (\Throwable) {
            // FULLTEXT not present — fall back to LIKE per first column
            $first = $cols[0];
            return DB::table($table)
                ->where('school_id', $schoolId)
                ->where($first, 'like', '%' . str_replace('%', '\\%', $this->cleanQuery($bool)) . '%')
                ->limit($limit)
                ->get($select)
                ->map(fn($r) => (array) $r)
                ->all();
        }
    }

    private function buildBooleanQuery(string $q): string
    {
        // Convert to MySQL boolean mode: each word becomes +word*
        $words = preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY);
        $cleaned = [];
        foreach ($words as $w) {
            $w = preg_replace('/[+\-<>()~*"@]/', '', $w);
            if (mb_strlen($w) >= 2) {
                $cleaned[] = '+' . $w . '*';
            }
        }
        return $cleaned ? implode(' ', $cleaned) : $q;
    }

    private function cleanQuery(string $q): string
    {
        return preg_replace('/[+\-<>()~*"@]/', '', $q);
    }

    private function empty(): array
    {
        return ['students' => [], 'users' => [], 'staff' => [], 'invoices' => [], 'notices' => []];
    }
}
