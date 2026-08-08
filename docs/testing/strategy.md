# Testing Strategy — Sikad Pro

## Overview

```
Setiap endpoint WAJIB punya Feature test.
Target coverage: > 80% untuk business logic (Services).
Tidak ada mocking database — gunakan SQLite in-memory atau MySQL test.
```

---

## Test Types

### 1. Feature Tests (WAJIB — setiap API endpoint)
```php
// tests/Feature/Attendance/BulkMarkTest.php
class BulkMarkTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_mark_attendance_for_own_class(): void
    {
        $school  = School::factory()->create();
        $teacher = User::factory()->teacher()->for($school)->create();
        $class   = ClassSection::factory()->for($school)->create([
            'class_teacher_id' => $teacher->id,
        ]);

        $response = $this->actingAs($teacher)->postJson(
            "/api/v1/attendance/class/{$class->id}",
            [
                'date' => '2025-07-14',
                'attendances' => [
                    ['student_id' => 101, 'status' => 'present'],
                    ['student_id' => 102, 'status' => 'absent'],
                ],
            ]
        );

        $response->assertOk()
                 ->assertJsonPath('summary.present', 1)
                 ->assertJsonPath('summary.absent', 1);

        $this->assertDatabaseHas('attendances', [
            'school_id'        => $school->id,
            'student_id'       => 101,
            'date'             => '2025-07-14',
            'status'           => 'present',
        ]);
    }

    public function test_teacher_cannot_mark_attendance_for_other_class(): void
    {
        $school      = School::factory()->create();
        $teacher     = User::factory()->teacher()->for($school)->create();
        $otherClass  = ClassSection::factory()->for($school)->create(); // teacher bukan wali kelas ini

        $response = $this->actingAs($teacher)->postJson(
            "/api/v1/attendance/class/{$otherClass->id}",
            ['date' => '2025-07-14', 'attendances' => []]
        );

        $response->assertForbidden();
    }
}
```

### 2. Unit Tests (untuk Services dengan logic kompleks)
```php
// tests/Unit/FeeServiceTest.php
class FeeServiceTest extends TestCase
{
    public function test_net_salary_cannot_be_negative(): void
    {
        $service = new PayrollService();
        $netSalary = $service->calculateNet(
            basicSalary: 100_000,   // 1000 sen
            totalAllowances: 50_000,
            totalDeductions: 200_000  // lebih besar dari basic + allowances
        );
        $this->assertSame(0, $netSalary);  // tidak boleh negatif
    }
}
```

### 3. Multi-Tenancy Tests (CRITICAL)
```php
// tests/Feature/MultiTenancy/CrossSchoolIsolationTest.php
class CrossSchoolIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_see_other_school_data(): void
    {
        $schoolA  = School::factory()->create();
        $schoolB  = School::factory()->create();
        $userA    = User::factory()->admin()->for($schoolA)->create();
        $studentB = Student::factory()->for($schoolB)->create();

        $response = $this->actingAs($userA)
            ->getJson("/api/v1/students/{$studentB->id}");

        $response->assertNotFound();  // SchoolScope menyembunyikan data
    }
}
```

### 4. Queue / Job Tests
```php
// tests/Feature/Attendance/AbsenceNotificationTest.php
public function test_fcm_dispatched_for_absent_students(): void
{
    Queue::fake();

    // ... mark attendance dengan status absent

    Queue::assertPushed(NotifyAbsenceJob::class, function ($job) {
        return in_array(102, $job->studentIds);
    });
}
```

---

## Test Database Setup

```php
// phpunit.xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
    <env name="CACHE_DRIVER" value="array"/>
    <env name="SESSION_DRIVER" value="array"/>
    <env name="QUEUE_CONNECTION" value="sync"/>
    <env name="MAIL_MAILER" value="array"/>
    <env name="BROADCAST_DRIVER" value="null"/>
    <env name="LICENSE_CHECK" value="false"/>
</php>
```

---

## Factory Setup (Contoh)

```php
// database/factories/UserFactory.php
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'      => $this->faker->name(),
            'email'     => $this->faker->unique()->safeEmail(),
            'password'  => Hash::make('password'),
            'is_active' => true,
        ];
    }

    public function admin(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole('admin');
        });
    }

    public function teacher(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole('teacher');
        });
    }

    public function student(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole('student');
        });
    }

    public function parent(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole('parent');
        });
    }
}
```

---

## Test Structure per Module

```
tests/
├── Feature/
│   ├── License/
│   │   ├── ValidLicenseTest.php
│   │   ├── OfflineChecksumTest.php
│   │   └── ...
│   ├── MultiTenancy/
│   │   ├── SchoolScopeTest.php
│   │   ├── CrossSchoolAccessTest.php
│   │   └── SuperAdminBypassTest.php
│   ├── Auth/
│   │   ├── LoginTest.php
│   │   ├── LogoutTest.php
│   │   └── PasswordResetTest.php
│   ├── SchoolSetup/
│   ├── AcademicStructure/
│   ├── Attendance/
│   ├── Timetable/
│   ├── Classroom/
│   ├── Exam/
│   ├── Marks/
│   ├── Admission/
│   ├── Fee/
│   ├── Payroll/
│   ├── Subscription/
│   ├── Library/
│   ├── Hostel/
│   ├── Transport/
│   ├── Notice/
│   ├── Chat/
│   ├── Notification/
│   └── SuperAdmin/
└── Unit/
    ├── Services/
    │   ├── FeeServiceTest.php
    │   ├── PayrollServiceTest.php
    │   ├── GradingServiceTest.php
    │   └── ...
    └── Models/
```

---

## Running Tests

```bash
# Semua test
php artisan test

# Satu module saja
php artisan test --filter=AttendanceTest

# Satu test class
php artisan test tests/Feature/Attendance/BulkMarkTest.php

# Parallel (lebih cepat)
php artisan test --parallel

# Dengan coverage
php artisan test --coverage --min=80

# Hanya test yang gagal sebelumnya
php artisan test --dirty
```

---

## Conventions

```php
// Nama test: snake_case deskriptif
public function test_teacher_cannot_access_other_school_students(): void {}
public function test_fee_invoice_status_changes_to_paid_after_full_payment(): void {}

// Gunakan arrange-act-assert pattern
public function test_...: void
{
    // Arrange
    $school = School::factory()->create();
    $user   = User::factory()->admin()->for($school)->create();

    // Act
    $response = $this->actingAs($user)->getJson('/api/v1/...');

    // Assert
    $response->assertOk()->assertJsonStructure(['data' => [...]]);
    $this->assertDatabaseHas('...', [...]);
}

// Http::fake() untuk test eksternal API
Http::fake([
    'whitelabel.co.id/api/license/validate' => Http::response([
        'valid' => true, 'checksum' => 'abc123',
    ], 200),
]);

// Queue::fake() untuk test job dispatch
Queue::fake();
// ... trigger action
Queue::assertPushed(NotifyAbsenceJob::class);

// Mail::fake() untuk test email
Mail::fake();
// ... trigger action
Mail::assertQueued(AdmissionApprovedMail::class);
```
