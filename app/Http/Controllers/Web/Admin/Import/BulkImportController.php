<?php

namespace App\Http\Controllers\Web\Admin\Import;

use App\Http\Controllers\Controller;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Staff;
use App\Models\Academic\Student;
use App\Models\User;
use App\Services\Import\CsvImportPreviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulkImportController extends Controller
{
    private function schoolId(): int
    {
        return (int) auth()->user()->school_id;
    }

    public function index(): View
    {
        return view('school-admin.import.index', [
            'classSections' => ClassSection::where('school_id', $this->schoolId())
                ->with(['classRoom', 'section'])->get(),
        ]);
    }

    public function templateStudents(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['admission_no', 'name', 'email', 'phone', 'gender', 'date_of_birth', 'address', 'guardian_name', 'guardian_phone', 'password']);
            fputcsv($out, ['ADM-001', 'Budi Santoso', 'budi@example.com', '08123456789', 'male', '2010-05-15', 'Jl. Mawar 1', 'Pak Santoso', '08129876543', 'Siswa123!']);
            fputcsv($out, ['ADM-002', 'Sari Dewi', 'sari@example.com', '08987654321', 'female', '2010-07-20', 'Jl. Melati 2', 'Bu Dewi', '08123456788', 'Siswa123!']);
            fclose($out);
        }, 'template-siswa.csv', ['Content-Type' => 'text/csv']);
    }

    public function templateStaff(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['employee_id', 'name', 'email', 'phone', 'role', 'department', 'designation', 'joining_date', 'basic_salary_rupiah', 'password']);
            fputcsv($out, ['NIP-001', 'Pak Andi', 'andi@example.com', '08111111111', 'teacher', 'Akademik', 'Guru Matematika', '2020-07-01', '5000000', 'Guru123!']);
            fputcsv($out, ['NIP-002', 'Bu Rina', 'rina@example.com', '08222222222', 'librarian', 'TU', 'Pustakawan', '2021-01-15', '4000000', 'Lib123!']);
            fclose($out);
        }, 'template-staff.csv', ['Content-Type' => 'text/csv']);
    }

    public function importStudents(Request $request, CsvImportPreviewService $previewer): View|RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
            'class_section_id' => 'nullable|exists:class_sections,id',
        ]);

        $classSectionId = $request->integer('class_section_id') ?: null;
        if ($classSectionId !== null) {
            ClassSection::withoutGlobalScopes()->where('school_id', $this->schoolId())->findOrFail($classSectionId);
        }

        try {
            $payload = $previewer->preview($request->file('file'), $this->schoolId(), 'students');
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors($e->getMessage());
        }

        $payload['class_section_id'] = $classSectionId;
        $token = $previewer->store($payload, auth()->id());

        return view('school-admin.import.preview', [
            'token' => $token,
            'kind' => 'students',
            'title' => 'Preview Import Siswa',
            'payload' => $previewer->forDisplay($payload),
        ]);
    }

    public function confirmStudents(Request $request, CsvImportPreviewService $previewer): RedirectResponse
    {
        $request->validate(['token' => 'required|string|size:64']);

        return $this->confirmImport($request->string('token')->toString(), 'students', $previewer);
    }

    public function importStaff(Request $request, CsvImportPreviewService $previewer): View|RedirectResponse
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:5120']);

        try {
            $payload = $previewer->preview($request->file('file'), $this->schoolId(), 'staff');
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors($e->getMessage());
        }

        $token = $previewer->store($payload, auth()->id());

        return view('school-admin.import.preview', [
            'token' => $token,
            'kind' => 'staff',
            'title' => 'Preview Import Staff',
            'payload' => $previewer->forDisplay($payload),
        ]);
    }

    public function confirmStaff(Request $request, CsvImportPreviewService $previewer): RedirectResponse
    {
        $request->validate(['token' => 'required|string|size:64']);

        return $this->confirmImport($request->string('token')->toString(), 'staff', $previewer);
    }

    private function confirmImport(string $token, string $kind, CsvImportPreviewService $previewer): RedirectResponse
    {
        $payload = $previewer->retrieve($token, $this->schoolId(), auth()->id());
        if ($payload === null || ($payload['kind'] ?? null) !== $kind) {
            return redirect()->route('admin.import.index')->withErrors('Preview import sudah kedaluwarsa atau tidak valid.');
        }

        $created = 0;
        $skipped = 0;
        $errors = [];
        $schoolId = $this->schoolId();

        DB::transaction(function () use ($payload, $kind, $schoolId, &$created, &$skipped, &$errors) {
            foreach ($payload['rows'] as $previewRow) {
                if (! $previewRow['valid']) {
                    $skipped++;

                    continue;
                }

                $row = $previewRow['data'];
                $email = strtolower(trim((string) ($row['email'] ?? '')));
                if (User::withoutGlobalScopes()->where('email', $email)->exists()) {
                    $errors[] = "Baris {$previewRow['line']}: email sudah digunakan saat konfirmasi.";
                    $skipped++;

                    continue;
                }
                if ($kind === 'students' && Student::withoutGlobalScopes()
                    ->where('school_id', $schoolId)
                    ->where('admission_no', $row['admission_no'])
                    ->exists()) {
                    $errors[] = "Baris {$previewRow['line']}: nomor pendaftaran sudah digunakan saat konfirmasi.";
                    $skipped++;

                    continue;
                }

                $user = User::create([
                    'name' => trim($row['name']),
                    'email' => $email,
                    'phone' => $row['phone'] ?? null,
                    'password' => Hash::make($row['password']),
                    'school_id' => $schoolId,
                    'is_active' => true,
                ]);
                $user->assignRole($kind === 'students' ? 'student' : $row['role']);

                if ($kind === 'students') {
                    Student::create([
                        'user_id' => $user->id,
                        'school_id' => $schoolId,
                        'class_section_id' => $payload['class_section_id'] ?? null,
                        'admission_no' => $row['admission_no'],
                        'gender' => $row['gender'] ?? 'male',
                        'date_of_birth' => $row['date_of_birth'] ?? null,
                        'address' => $row['address'] ?? null,
                        'guardian_name' => $row['guardian_name'] ?? null,
                        'guardian_phone' => $row['guardian_phone'] ?? null,
                    ]);
                } else {
                    Staff::create([
                        'user_id' => $user->id,
                        'school_id' => $schoolId,
                        'employee_id' => $row['employee_id'] ?? null,
                        'department' => $row['department'] ?? null,
                        'designation' => $row['designation'] ?? null,
                        'joining_date' => $row['joining_date'] ?? null,
                        'basic_salary' => ! empty($row['basic_salary_rupiah']) ? (int) ((float) $row['basic_salary_rupiah'] * 100) : null,
                    ]);
                }

                $created++;
            }
        });

        $previewer->forget($token);
        $message = "$created ".($kind === 'students' ? 'siswa' : 'staff')." di-import. $skipped baris dilewati.";
        if ($errors !== []) {
            $message .= ' '.implode(' ', array_slice($errors, 0, 3));
        }

        return redirect()->route('admin.import.index')->with('success', $message);
    }
}
