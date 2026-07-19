<?php

namespace App\Services\Finance;

use App\Models\Academic\AcademicYear;
use App\Models\Academic\Student;
use App\Models\Finance\AdmissionEnquiry;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdmissionService
{
    public function enroll(AdmissionEnquiry $enquiry, int $classSectionId): Student
    {
        return DB::transaction(function () use ($enquiry, $classSectionId) {
            $password    = Str::random(10);
            $studentUser = User::create([
                'school_id' => $enquiry->school_id,
                'name'      => $enquiry->student_name,
                'email'     => 'student.' . $enquiry->id . '@' . $enquiry->school_id . '.' . config('multitenancy.base_domain'),
                'password'  => Hash::make($password),
                'is_active' => true,
            ]);
            $studentUser->assignRole('student');

            $activeYear = AcademicYear::where('school_id', $enquiry->school_id)
                ->where('is_active', true)
                ->first();

            $student = Student::create([
                'school_id'        => $enquiry->school_id,
                'user_id'          => $studentUser->id,
                'class_section_id' => $classSectionId,
                'admission_date'   => today(),
                'guardian_name'    => $enquiry->father_name ?? $enquiry->mother_name,
                'guardian_phone'   => $enquiry->phone,
            ]);

            $enquiry->update(['status' => 'enrolled', 'converted_student_id' => $student->id]);

            return $student->load('user', 'classSection');
        });
    }
}
