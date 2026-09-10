<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * SchoolSetupService — pelacak progres setup sekolah baru (onboarding).
 *
 * Menghitung persentase kelengkapan konfigurasi dasar dari data nyata
 * di database (bukan mockup). Setiap langkah punya CTA ke halaman
 * pengaturan terkait sehingga admin tahu persis apa yang belum diisi.
 */
class SchoolSetupService
{
    protected int $ttl = 300;

    /** @return array{percent:int, done:int, total:int, steps:array<int,array>} */
    public function progress(int $schoolId): array
    {
        return Cache::remember("setup:progress:{$schoolId}", $this->ttl, function () use ($schoolId) {
            $safe = fn (callable $fn, $default = false) => rescue($fn, $default, false);

            $steps = [
                ['key' => 'profile', 'label' => 'Profil Sekolah', 'href' => rescue(fn () => route('admin.branding.show'), '#', false)],
                ['key' => 'year', 'label' => 'Tahun Ajaran Aktif', 'href' => rescue(fn () => route('admin.academic.years.index'), '#', false)],
                ['key' => 'subjects', 'label' => 'Mata Pelajaran', 'href' => rescue(fn () => route('admin.academic.subjects.index'), '#', false)],
                ['key' => 'classes', 'label' => 'Kelas & Rombel', 'href' => rescue(fn () => route('admin.academic.classes.index'), '#', false)],
                ['key' => 'staff', 'label' => 'Guru & Staf', 'href' => rescue(fn () => route('admin.staff.index'), '#', false)],
                ['key' => 'students', 'label' => 'Data Siswa', 'href' => rescue(fn () => route('admin.students.index'), '#', false)],
                ['key' => 'fees', 'label' => 'Struktur SPP', 'href' => rescue(fn () => route('admin.fee.structures.index'), '#', false)],
            ];

            foreach ($steps as &$step) {
                $step['done'] = match ($step['key']) {
                    'profile' => (bool) $safe(fn () => DB::table('schools')->where('id', $schoolId)->whereNotNull('name')->where('name', '!=', '')->value('name')),
                    'year' => (bool) $safe(fn () => DB::table('academic_years')->where('school_id', $schoolId)->where('is_active', true)->exists()),
                    'subjects' => (bool) $safe(fn () => DB::table('subjects')->where('school_id', $schoolId)->exists()),
                    'classes' => (bool) $safe(fn () => DB::table('class_rooms')->where('school_id', $schoolId)->exists()),
                    'staff' => (bool) $safe(fn () => DB::table('staffs')->where('school_id', $schoolId)->exists()),
                    'students' => (bool) $safe(fn () => DB::table('students')->where('school_id', $schoolId)->exists()),
                    'fees' => (bool) $safe(fn () => DB::table('fee_structures')->where('school_id', $schoolId)->exists()),
                    default => false,
                };
            }
            unset($step);

            $done = count(array_filter($steps, fn ($s) => $s['done']));
            $total = count($steps);

            return [
                'percent' => (int) round($done / max($total, 1) * 100),
                'done' => $done,
                'total' => $total,
                'steps' => $steps,
            ];
        });
    }

    public static function flush(int $schoolId): void
    {
        Cache::forget("setup:progress:{$schoolId}");
    }
}
