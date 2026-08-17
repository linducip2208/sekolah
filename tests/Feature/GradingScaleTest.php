<?php

use App\Models\Academic\AcademicYear;
use App\Models\Academic\GradeRule;
use App\Models\Academic\GradeSystem;
use App\Models\Academic\ReportCard;
use App\Models\Academic\Semester;
use App\Models\Academic\Student;
use App\Models\School;
use App\Models\User;
use App\Services\Academic\MarksService;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->school = School::factory()->create();
    $this->admin = User::factory()->create(['school_id' => $this->school->id]);
    Role::firstOrCreate(['name' => 'admin']);
    $this->admin->assignRole('admin');
});

it('manages grading scales and resolves grades from the active system', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.grades.index'))
        ->assertOk()
        ->assertSee('Sistem Penilaian');

    $this->actingAs($this->admin)->post(route('admin.grades.store'), ['name' => 'Kurikulum Merdeka']);

    $system = GradeSystem::where('school_id', $this->school->id)->firstOrFail();

    $this->actingAs($this->admin)->post(route('admin.grades.rules.store', $system), [
        'grade' => 'A', 'min_percent' => 90, 'max_percent' => 100, 'gpa_point' => 4,
    ]);
    $this->actingAs($this->admin)->post(route('admin.grades.rules.store', $system), [
        'grade' => 'B', 'min_percent' => 80, 'max_percent' => 89.99, 'gpa_point' => 3,
    ]);

    expect(GradeRule::where('grade_system_id', $system->id)->count())->toBe(2);

    $service = app(MarksService::class);
    expect($service->resolveGrade($this->school->id, 95))->toBe('A');
    expect($service->resolveGrade($this->school->id, 85))->toBe('B');
});

it('renders the public rapor verification page and QR code', function () {
    $user = User::factory()->create(['school_id' => $this->school->id]);
    $student = Student::create([
        'user_id' => $user->id, 'school_id' => $this->school->id, 'admission_no' => 'V-1',
    ]);
    $year = AcademicYear::create([
        'school_id' => $this->school->id, 'name' => '2025/2026',
        'start_date' => '2025-07-01', 'end_date' => '2026-06-30', 'is_active' => true,
    ]);
    $semester = Semester::create([
        'school_id' => $this->school->id, 'academic_year_id' => $year->id,
        'name' => 'Ganjil', 'start_date' => '2025-07-01', 'end_date' => '2025-12-31', 'is_active' => true,
    ]);
    $card = ReportCard::create([
        'school_id' => $this->school->id, 'student_id' => $student->id, 'semester_id' => $semester->id,
        'total_percentage' => 85, 'overall_grade' => 'A', 'is_published' => true,
        'verification_token' => 'tokentest1234567890',
    ]);

    $this->get(route('raport.verify', 'tokentest1234567890'))
        ->assertOk()
        ->assertSee($user->name);

    $this->get(route('raport.verify.qrcode', 'tokentest1234567890'))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png');
});
