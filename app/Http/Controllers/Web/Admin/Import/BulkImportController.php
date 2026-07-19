<?php

namespace App\Http\Controllers\Web\Admin\Import;

use App\Http\Controllers\Controller;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Staff;
use App\Models\Academic\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulkImportController extends Controller
{
    private function schoolId(): int { return auth()->user()->school_id; }

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

    public function importStudents(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
            'class_section_id' => 'nullable|exists:class_sections,id',
        ]);

        $handle = fopen($request->file('file')->getPathname(), 'r');
        $headers = fgetcsv($handle);
        $required = ['admission_no', 'name', 'email', 'gender', 'password'];
        if (count(array_diff($required, $headers)) > 0) {
            fclose($handle);
            return back()->withErrors('CSV harus punya header: '.implode(', ', $required));
        }

        $created = 0; $skipped = 0; $errors = [];
        DB::transaction(function () use ($handle, $headers, $request, &$created, &$skipped, &$errors) {
            while (($row = fgetcsv($handle)) !== false) {
                $row = array_combine($headers, $row);
                if (empty($row['email']) || empty($row['name'])) { $skipped++; continue; }

                if (User::where('email', $row['email'])->exists()) {
                    $errors[] = "Skip {$row['email']} — email sudah ada";
                    $skipped++; continue;
                }

                $user = User::create([
                    'name'      => $row['name'],
                    'email'     => $row['email'],
                    'phone'     => $row['phone'] ?? null,
                    'password'  => Hash::make($row['password'] ?? 'Siswa123!'),
                    'school_id' => $this->schoolId(),
                    'is_active' => true,
                ]);
                $user->assignRole('student');

                Student::create([
                    'user_id'          => $user->id,
                    'school_id'        => $this->schoolId(),
                    'class_section_id' => $request->class_section_id,
                    'admission_no'     => $row['admission_no'] ?? 'ADM-'.$user->id,
                    'gender'           => $row['gender'] ?? 'male',
                    'date_of_birth'    => $row['date_of_birth'] ?? null,
                    'address'          => $row['address'] ?? null,
                    'guardian_name'    => $row['guardian_name'] ?? null,
                    'guardian_phone'   => $row['guardian_phone'] ?? null,
                ]);
                $created++;
            }
        });
        fclose($handle);

        $msg = "$created siswa di-import. $skipped skip.";
        if (!empty($errors)) $msg .= ' ' . implode(' · ', array_slice($errors, 0, 3));
        return back()->with('success', $msg);
    }

    public function importStaff(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:5120']);

        $handle = fopen($request->file('file')->getPathname(), 'r');
        $headers = fgetcsv($handle);
        $required = ['name', 'email', 'role', 'password'];
        if (count(array_diff($required, $headers)) > 0) {
            fclose($handle);
            return back()->withErrors('CSV harus punya header: '.implode(', ', $required));
        }

        $created = 0; $skipped = 0;
        DB::transaction(function () use ($handle, $headers, &$created, &$skipped) {
            $allowedRoles = ['teacher', 'admin', 'accountant', 'librarian', 'counselor', 'nurse', 'receptionist'];
            while (($row = fgetcsv($handle)) !== false) {
                $row = array_combine($headers, $row);
                if (empty($row['email']) || empty($row['name'])) { $skipped++; continue; }
                if (User::where('email', $row['email'])->exists()) { $skipped++; continue; }
                if (!in_array($row['role'] ?? '', $allowedRoles)) { $skipped++; continue; }

                $user = User::create([
                    'name'      => $row['name'],
                    'email'     => $row['email'],
                    'phone'     => $row['phone'] ?? null,
                    'password'  => Hash::make($row['password'] ?? 'Staff123!'),
                    'school_id' => $this->schoolId(),
                    'is_active' => true,
                ]);
                $user->assignRole($row['role']);

                Staff::create([
                    'user_id'      => $user->id,
                    'school_id'    => $this->schoolId(),
                    'employee_id'  => $row['employee_id'] ?? null,
                    'department'   => $row['department'] ?? null,
                    'designation'  => $row['designation'] ?? null,
                    'joining_date' => $row['joining_date'] ?? null,
                    'basic_salary' => !empty($row['basic_salary_rupiah']) ? (int)((float)$row['basic_salary_rupiah'] * 100) : null,
                ]);
                $created++;
            }
        });
        fclose($handle);

        return back()->with('success', "$created staff di-import. $skipped skip.");
    }
}
