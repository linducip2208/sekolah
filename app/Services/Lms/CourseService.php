<?php

namespace App\Services\Lms;

use App\Models\Lms\Course;
use App\Models\Lms\CourseEnrollment;
use App\Models\Lms\CourseLesson;
use App\Models\Lms\CourseLessonCompletion;
use App\Models\Academic\Student;

class CourseService
{
    public function enroll(int $schoolId, int $courseId, int $studentId): CourseEnrollment
    {
        return CourseEnrollment::firstOrCreate(
            ['school_id' => $schoolId, 'course_id' => $courseId, 'student_id' => $studentId],
            ['status' => 'enrolled', 'progress_pct' => 0]
        );
    }

    public function bulkEnroll(int $schoolId, int $courseId, array $studentIds): int
    {
        $count = 0;
        foreach ($studentIds as $studentId) {
            $this->enroll($schoolId, $courseId, $studentId);
            $count++;
        }
        return $count;
    }

    public function completeLesson(CourseEnrollment $enrollment, int $lessonId, int $studentId): CourseEnrollment
    {
        $lesson = CourseLesson::where('school_id', $enrollment->school_id)
            ->where('id', $lessonId)
            ->firstOrFail();

        CourseLessonCompletion::firstOrCreate(
            [
                'school_id'        => $enrollment->school_id,
                'enrollment_id'    => $enrollment->id,
                'course_lesson_id' => $lessonId,
                'student_id'       => $studentId,
            ],
            ['completed_at' => now()]
        );

        return $this->refreshProgress($enrollment);
    }

    public function refreshProgress(CourseEnrollment $enrollment): CourseEnrollment
    {
        $total = CourseLesson::where('school_id', $enrollment->school_id)
            ->whereHas('module', fn ($q) => $q->where('course_id', $enrollment->course_id))
            ->count();

        $completed = $enrollment->lessonCompletions()->count();

        $pct = $total > 0 ? (int) round($completed / $total * 100) : 0;

        $status = $pct >= 100 ? 'completed' : ($pct > 0 ? 'in_progress' : 'enrolled');

        $enrollment->update([
            'progress_pct' => $pct,
            'status'       => $status,
            'completed_at' => $status === 'completed' ? ($enrollment->completed_at ?? now()) : null,
        ]);

        return $enrollment->fresh();
    }

    public function progressForStudent(int $schoolId, int $studentId): array
    {
        return CourseEnrollment::where('school_id', $schoolId)
            ->where('student_id', $studentId)
            ->with('course')
            ->get()
            ->map(fn ($e) => [
                'course_id'    => $e->course_id,
                'title'        => $e->course?->title,
                'progress_pct' => $e->progress_pct,
                'status'       => $e->status,
            ])
            ->all();
    }
}
