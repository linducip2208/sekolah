<?php

use App\Models\Academic\Student;
use App\Models\Lms\Course;
use App\Models\Lms\CourseModule;
use App\Models\Lms\CourseLesson;
use App\Models\Lms\Quiz;
use App\Models\Lms\QuizQuestion;
use App\Models\School;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

function lmsApiFixture(): array
{
    $school = School::factory()->create(['settings' => []]);
    app()->instance('current_school', $school);
    Role::firstOrCreate(['name' => 'student']);

    $course = Course::create([
        'school_id' => $school->id, 'title' => 'IPA Dasar',
        'description' => 'Belajar IPA', 'is_published' => true,
    ]);
    $module = CourseModule::create([
        'school_id' => $school->id, 'course_id' => $course->id, 'title' => 'Modul 1', 'order' => 1,
    ]);
    $lesson = CourseLesson::create([
        'school_id' => $school->id, 'course_module_id' => $module->id,
        'title' => 'Pelajaran 1', 'order' => 1,
    ]);

    $studentUser = User::factory()->create(['school_id' => $school->id]);
    $studentUser->assignRole('student');
    $student = Student::create(['user_id' => $studentUser->id, 'school_id' => $school->id]);

    return [$school, $course, $module, $lesson, $studentUser, $student];
}

test('student can list published courses via API', function () {
    [, , , , $studentUser] = lmsApiFixture();
    Sanctum::actingAs($studentUser);

    $this->getJson('/api/v1/lms/courses')->assertOk()->assertJsonPath('data.0.title', 'IPA Dasar');
});

test('student can enroll and track progress via API', function () {
    [, $course, , $lesson, $studentUser, $student] = lmsApiFixture();
    Sanctum::actingAs($studentUser);

    $enroll = $this->postJson('/api/v1/lms/enroll', ['course_id' => $course->id])->assertCreated();
    $enrollmentId = $enroll->json('id');

    $this->postJson('/api/v1/lms/complete-lesson', [
        'enrollment_id' => $enrollmentId, 'lesson_id' => $lesson->id,
    ])->assertOk()->assertJsonPath('progress_pct', 100);

    $this->getJson('/api/v1/lms/progress')->assertOk()->assertJsonPath('0.status', 'completed');
});

test('cross-school enrollment is rejected', function () {
    [$schoolA] = lmsApiFixture();
    $schoolB = School::factory()->create(['settings' => []]);
    $foreignCourse = Course::create([
        'school_id' => $schoolB->id, 'title' => 'Milik B', 'is_published' => true,
    ]);

    $studentUser = User::factory()->create(['school_id' => $schoolA->id]);
    $studentUser->assignRole('student');
    Student::create(['user_id' => $studentUser->id, 'school_id' => $schoolA->id]);

    Sanctum::actingAs($studentUser);
    $this->postJson('/api/v1/lms/enroll', ['course_id' => $foreignCourse->id])->assertNotFound();
});

test('student can submit quiz and staff can issue certificate via API', function () {
    [$school, $course, $module, $lesson, $studentUser, $student] = lmsApiFixture();

    $quiz = Quiz::create([
        'school_id' => $school->id, 'course_id' => $course->id,
        'title' => 'Kuis 1', 'pass_score' => 50, 'is_published' => true,
    ]);
    $qq = QuizQuestion::create([
        'school_id' => $school->id, 'quiz_id' => $quiz->id,
        'question' => '1+1?', 'type' => 'mcq',
        'options' => [['text' => '2']], 'correct_answer' => '2', 'order' => 1,
    ]);

    Sanctum::actingAs($studentUser);
    $enrollmentId = $this->postJson('/api/v1/lms/enroll', ['course_id' => $course->id])->json('id');

    $this->postJson('/api/v1/lms/quiz/submit', [
        'quiz_id' => $quiz->id, 'answers' => [$qq->id => '2'],
    ])->assertOk()->assertJsonPath('passed', true);

    $this->postJson('/api/v1/lms/complete-lesson', [
        'enrollment_id' => $enrollmentId, 'lesson_id' => $lesson->id,
    ])->assertOk();

    $teacher = User::factory()->create(['school_id' => $school->id]);
    $teacher->assignRole('teacher');
    Sanctum::actingAs($teacher);
    $cert = $this->postJson("/api/v1/lms/enrollments/{$enrollmentId}/certificate")->assertCreated();
    $certNo = $cert->json('certificate_no');
    expect($certNo)->toStartWith('CRT-');

    // Public verification (no auth).
    auth()->forgetGuards();
    $this->getJson("/api/v1/public/certificates/{$certNo}")->assertOk()->assertJsonPath('course', 'IPA Dasar');
});
