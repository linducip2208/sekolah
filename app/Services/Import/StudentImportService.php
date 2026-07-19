<?php

namespace App\Services\Import;

use App\Models\Academic\ClassSection;
use App\Models\Academic\Student;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class StudentImportService
{
    private array $errors   = [];
    private int   $imported = 0;

    public function import(UploadedFile $file, int $classSectionId): array
    {
        $this->errors   = [];
        $this->imported = 0;

        $classSection = ClassSection::findOrFail($classSectionId);
        $schoolId     = auth()->user()->school_id;

        $handle = fopen($file->getRealPath(), 'r');
        $headers = fgetcsv($handle);

        $row = 1;
        while (($data = fgetcsv($handle)) !== false) {
            $row++;
            $this->processRow($data, $headers, $schoolId, $classSectionId, $row);
        }

        fclose($handle);

        return [
            'imported' => $this->imported,
            'errors'   => $this->errors,
        ];
    }

    private function processRow(array $data, array $headers, int $schoolId, int $classSectionId, int $row): void
    {
        $row_data = array_combine($headers, array_pad($data, count($headers), null));

        $name  = trim($row_data['name'] ?? '');
        $email = trim($row_data['email'] ?? '');

        if (empty($name)) {
            $this->errors[] = "Row {$row}: 'name' is required.";
            return;
        }

        if (empty($email)) {
            $email = Str::slug($name, '.') . '@student.' . $schoolId . '.local';
        }

        if (User::where('email', $email)->exists()) {
            $this->errors[] = "Row {$row}: email '{$email}' already exists.";
            return;
        }

        $user = User::create([
            'name'      => $name,
            'email'     => $email,
            'password'  => bcrypt('Student' . $schoolId . '!'),
            'school_id' => $schoolId,
            'is_active' => true,
        ]);
        $user->assignRole('student');

        Student::create([
            'user_id'          => $user->id,
            'school_id'        => $schoolId,
            'class_section_id' => $classSectionId,
            'admission_no'     => $row_data['admission_no'] ?? null,
            'gender'           => $row_data['gender'] ?? null,
            'date_of_birth'    => $row_data['date_of_birth'] ?? null,
            'guardian_name'    => $row_data['guardian_name'] ?? null,
            'guardian_phone'   => $row_data['guardian_phone'] ?? null,
        ]);

        $this->imported++;
    }

    public function template(): string
    {
        $headers = ['name', 'email', 'admission_no', 'gender', 'date_of_birth', 'guardian_name', 'guardian_phone'];
        $example = ['Budi Santoso', 'budi@mail.com', 'ADM-001', 'male', '2010-05-20', 'Ayah Budi', '08123456789'];

        $lines = [implode(',', $headers), implode(',', $example)];
        return implode("\n", $lines);
    }
}
