<?php

use App\Models\Academic\Student;
use App\Models\Lms\Course;
use App\Models\Lms\CourseEnrollment;
use App\Models\Lms\CourseLesson;
use App\Models\Lms\CourseModule;
use App\Models\School;
use App\Models\User;
use App\Services\Lms\CourseService;

beforeEach(function () {
    $this->service = app(CourseService::class);

    $this->school = School::factory()->create();
    $user = User::factory()->create(['school_id' => $this->school->id]);
    $this->student = Student::create([
        'user_id' => $user->id, 'school_id' => $this->school->id, 'admission_no' => 'NIS-1',
    ]);

    $this->course = Course::create([
        'school_id' => $this->school->id, 'title' => 'Kursus Matematika', 'is_published' => true,
    ]);
    $module = CourseModule::create([
        'school_id' => $this->school->id, 'course_id' => $this->course->id, 'title' => 'Modul 1', 'order' => 1,
    ]);
    $this->lessonA = CourseLesson::create([
        'school_id' => $this->school->id, 'course_module_id' => $module->id, 'title' => 'Pelajaran A', 'order' => 1,
    ]);
    $this->lessonB = CourseLesson::create([
        'school_id' => $this->school->id, 'course_module_id' => $module->id, 'title' => 'Pelajaran B', 'order' => 2,
    ]);
});

it('enrolls a student into a course', function () {
    $enrollment = $this->service->enroll($this->school->id, $this->course->id, $this->student->id);

    expect($enrollment->status)->toBe('enrolled');
    expect($enrollment->progress_pct)->toBe(0);

    $again = $this->service->enroll($this->school->id, $this->course->id, $this->student->id);
    expect(CourseEnrollment::where('course_id', $this->course->id)->count())->toBe(1);
});

it('tracks progress as lessons are completed', function () {
    $enrollment = $this->service->enroll($this->school->id, $this->course->id, $this->student->id);

    $enrollment = $this->service->completeLesson($enrollment, $this->lessonA->id, $this->student->id);
    expect($enrollment->progress_pct)->toBe(50);
    expect($enrollment->status)->toBe('in_progress');

    $enrollment = $this->service->completeLesson($enrollment, $this->lessonB->id, $this->student->id);
    expect($enrollment->progress_pct)->toBe(100);
    expect($enrollment->status)->toBe('completed');
    expect($enrollment->completed_at)->not->toBeNull();
});

it('completing the same lesson twice does not double count', function () {
    $enrollment = $this->service->enroll($this->school->id, $this->course->id, $this->student->id);

    $enrollment = $this->service->completeLesson($enrollment, $this->lessonA->id, $this->student->id);
    $enrollment = $this->service->completeLesson($enrollment, $this->lessonA->id, $this->student->id);

    expect($enrollment->progress_pct)->toBe(50);
});

it('returns progress for a student across courses', function () {
    $this->service->enroll($this->school->id, $this->course->id, $this->student->id);

    $progress = $this->service->progressForStudent($this->school->id, $this->student->id);

    expect($progress)->toHaveCount(1);
    expect($progress[0]['title'])->toBe('Kursus Matematika');
});
