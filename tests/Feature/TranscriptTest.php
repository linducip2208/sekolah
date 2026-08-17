<?php

use App\Models\Academic\AcademicYear;
use App\Models\Academic\ReportCard;
use App\Models\Academic\Semester;
use App\Models\Academic\Student;
use App\Models\School;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->school = School::factory()->create();
    $this->admin = User::factory()->create(['school_id' => $this->school->id]);
    Role::firstOrCreate(['name' => 'admin']);
    $this->admin->assignRole('admin');

    $user = User::factory()->create(['school_id' => $this->school->id]);
    $this->student = Student::create([
        'user_id' => $user->id, 'school_id' => $this->school->id, 'admission_no' => 'TR-1',
    ]);

    $year = AcademicYear::create([
        'school_id' => $this->school->id, 'name' => '2025/2026',
        'start_date' => '2025-07-01', 'end_date' => '2026-06-30', 'is_active' => true,
    ]);

    $s1 = Semester::create([
        'school_id' => $this->school->id, 'academic_year_id' => $year->id,
        'name' => 'Ganjil', 'start_date' => '2025-07-01', 'end_date' => '2025-12-31', 'is_active' => false,
    ]);
    $s2 = Semester::create([
        'school_id' => $this->school->id, 'academic_year_id' => $year->id,
        'name' => 'Genap', 'start_date' => '2026-01-01', 'end_date' => '2026-06-30', 'is_active' => true,
    ]);

    ReportCard::create([
        'school_id' => $this->school->id, 'student_id' => $this->student->id, 'semester_id' => $s1->id,
        'total_percentage' => 80, 'overall_grade' => 'B', 'gpa' => 3.0, 'status' => 'locked',
    ]);
    ReportCard::create([
        'school_id' => $this->school->id, 'student_id' => $this->student->id, 'semester_id' => $s2->id,
        'total_percentage' => 90, 'overall_grade' => 'A', 'gpa' => 4.0, 'status' => 'approved',
    ]);
});

it('shows a student transcript across semesters with cumulative GPA', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.grades.transcript', ['student_id' => $this->student->id]))
        ->assertOk()
        ->assertSee('Ganjil')
        ->assertSee('Genap')
        ->assertSee('3.5'); // cumulative GPA (3.0 + 4.0) / 2
});
