<?php

namespace App\Services;

use App\Models\Communication\Letter;
use App\Models\Communication\LetterTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Str;

class LetterService
{
    public function renderTemplate(LetterTemplate $template, array $variables): string
    {
        $content = $template->content;

        foreach ($variables as $key => $value) {
            $content = str_replace(
                ['{' . $key . '}', '{' . strtoupper($key) . '}'],
                [$value, $value],
                $content
            );
        }

        return $content;
    }

    public function generateLetterNumber(string $code, string $schoolId): string
    {
        $now = Carbon::now();
        $romanMonth = $this->romanMonth($now->month);
        $year = $now->year;

        $count = Letter::where('school_id', $schoolId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $now->month)
            ->count() + 1;

        $paddedCount = str_pad($count, 3, '0', STR_PAD_LEFT);

        return "{$paddedCount}/{$code}/{$romanMonth}/{$year}";
    }

    private function romanMonth(int $month): string
    {
        return match ($month) {
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
            default => 'I',
        };
    }

    public function generatePdf(Letter $letter): string
    {
        $branding = app(\App\Services\Branding\BrandingService::class)->getForSchool($letter->school_id);

        $pdf = Pdf::loadView('pdf.letter', [
            'letter'   => $letter,
            'branding' => $branding,
        ]);

        $pdf->setPaper('a4');
        $pdf->setOptions([
            'defaultFont' => 'serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ]);

        return $pdf->output();
    }

    public function getDefaultVariablesForStudent(\App\Models\Academic\Student $student): array
    {
        $classSection = $student->classSection;

        return [
            'nama'       => $student->user->name ?? '',
            'nis'        => $student->admission_no ?? '',
            'kelas'      => $classSection ? ($classSection->classRoom?->name . ' ' . $classSection->section?->name) : '',
            'alamat'     => $student->address ?? '',
            'nama_wali'  => $student->guardian_name ?? '',
            'gender'     => $student->gender === 'male' ? 'Laki-laki' : ($student->gender === 'female' ? 'Perempuan' : ''),
            'tempat_lahir' => '',
            'tanggal_lahir' => $student->date_of_birth ? $student->date_of_birth->format('d F Y') : '',
            'sekolah'    => config('app.name', 'Sekolah'),
            'tanggal'    => Carbon::now()->format('d F Y'),
        ];
    }

    public function getDefaultVariablesForStaff(\App\Models\Academic\Staff $staff): array
    {
        return [
            'nama'       => $staff->user->name ?? '',
            'nip'        => $staff->employee_id ?? '',
            'jabatan'    => $staff->designation ?? '',
            'departemen' => $staff->department ?? '',
            'alamat'     => '',
            'sekolah'    => config('app.name', 'Sekolah'),
            'tanggal'    => Carbon::now()->format('d F Y'),
        ];
    }

    public function getAvailableVariables(): array
    {
        return [
            '{nama}'           => 'Nama penerima surat',
            '{nis}'            => 'Nomor Induk Siswa',
            '{nip}'            => 'Nomor Induk Pegawai',
            '{kelas}'          => 'Kelas / Rombel',
            '{alamat}'         => 'Alamat penerima',
            '{nama_wali}'      => 'Nama orang tua / wali',
            '{gender}'         => 'Jenis kelamin',
            '{tempat_lahir}'   => 'Tempat lahir',
            '{tanggal_lahir}'  => 'Tanggal lahir',
            '{jabatan}'        => 'Jabatan / Posisi',
            '{departemen}'     => 'Departemen',
            '{sekolah}'        => 'Nama sekolah',
            '{tanggal}'        => 'Tanggal pembuatan surat',
            '{nomor_surat}'    => 'Nomor surat otomatis',
            '{perihal}'        => 'Perihal / Subject surat',
        ];
    }
}
