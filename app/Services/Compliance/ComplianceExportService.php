<?php

namespace App\Services\Compliance;

use App\Models\Academic\Student;
use App\Models\Finance\BudgetItem;
use App\Models\Finance\BudgetTransaction;
use Illuminate\Support\Facades\DB;

/**
 * ComplianceExportService — exporter format tender sekolah negeri.
 * - Leger e-Rapor: rekap nilai per siswa per mapel (CSV siap konversi Excel Dapodik/e-Rapor)
 * - Dapodik siswa extended: NISN/NIS, nama, JK, TTL, alamat, wali
 * - BOS/LPJ: realisasi anggaran per kategori (CSV BKU sederhana)
 */
class ComplianceExportService
{
    /** @return array{headers:array<int,string>, rows:array<int,array<int,mixed>>} */
    public function leger(int $schoolId, ?int $classSectionId = null): array
    {
        $headers = ['nis', 'nama', 'rombel', 'mapel', 'nilai_akhir', 'predikat', 'deskripsi'];
        $rows = DB::table('marks as m')
            ->join('students as s', 's.id', '=', 'm.student_id')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->leftJoin('subjects as sub', 'sub.id', '=', 'm.subject_id')
            ->leftJoin('class_sections as cs', 'cs.id', '=', 's.class_section_id')
            ->leftJoin('class_rooms as cr', 'cr.id', '=', 'cs.class_room_id')
            ->where('m.school_id', $schoolId)
            ->when($classSectionId, fn ($q) => $q->where('s.class_section_id', $classSectionId))
            ->orderBy('u.name')->orderBy('sub.name')
            ->get(['s.admission_no as nis', 'u.name as nama', 'cr.name as rombel', 'sub.name as mapel', 'm.percentage as nilai', 'm.grade as predikat'])
            ->map(fn ($r) => [$r->nis, $r->nama, $r->rombel, $r->mapel, $r->nilai, $r->predikat, ''])
            ->toArray();

        return ['headers' => $headers, 'rows' => $rows];
    }

    /** @return array{headers:array<int,string>, rows:array<int,array<int,mixed>>} */
    public function dapodikStudents(int $schoolId): array
    {
        $headers = ['nisn', 'nis', 'nama', 'jk', 'tanggal_lahir', 'alamat', 'nama_wali', 'no_hp_wali', 'rombel'];
        $rows = Student::where('school_id', $schoolId)
            ->with(['user:id,name', 'classSection.classRoom:id,name'])
            ->orderBy('id')->limit(5000)->get()
            ->map(fn ($s) => [
                $s->dapodik_id, $s->admission_no, $s->user?->name, $s->gender,
                $s->date_of_birth, $s->address, $s->guardian_name, $s->guardian_phone,
                $s->classSection?->classRoom?->name,
            ])->toArray();

        return ['headers' => $headers, 'rows' => $rows];
    }

    /** @return array{headers:array<int,string>, rows:array<int,array<int,mixed>>} */
    public function bosRealization(int $schoolId): array
    {
        $headers = ['kategori', 'rencana_rp', 'realisasi_rp', 'sisa_rp', 'sumber_dana'];
        $rows = BudgetItem::where('school_id', $schoolId)
            ->with('category:id,name')
            ->orderBy('id')->limit(2000)->get()
            ->map(function ($item) {
                $real = BudgetTransaction::where('school_id', $item->school_id)
                    ->where('budget_item_id', $item->id)->sum('amount');
                $plan = (int) $item->planned_amount;

                return [$item->category?->name ?? '-', $plan / 100, $real / 100, ($plan - $real) / 100, 'BOS Reguler'];
            })->toArray();

        return ['headers' => $headers, 'rows' => $rows];
    }
}
