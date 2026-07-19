<?php

namespace App\Services\Academic;

use App\Models\Academic\Assignment;
use App\Models\Academic\AssignmentSubmission;
use App\Models\Academic\Lesson;
use App\Models\Academic\Student;
use App\Models\Academic\StudyMaterial;

class ClassroomService
{
    public function createLesson(array $data): Lesson
    {
        $data['school_id'] = auth()->user()->school_id;
        $data['teacher_id'] = auth()->id();
        return Lesson::create($data);
    }

    public function getLessonsForClass(int $classSectionId, ?int $subjectId = null): \Illuminate\Database\Eloquent\Collection
    {
        return Lesson::where('class_section_id', $classSectionId)
            ->when($subjectId, fn($q) => $q->where('subject_id', $subjectId))
            ->with('subject', 'teacher')
            ->latest()
            ->get();
    }

    public function addMaterial(int $lessonId, array $data): StudyMaterial
    {
        $lesson = Lesson::findOrFail($lessonId);
        return $lesson->studyMaterials()->create($data);
    }

    public function createAssignment(array $data): Assignment
    {
        $lesson = Lesson::findOrFail($data['lesson_id']);
        $data['school_id'] = $lesson->school_id;
        return Assignment::create($data);
    }

    public function submitAssignment(int $assignmentId, array $data): AssignmentSubmission
    {
        $assignment = Assignment::findOrFail($assignmentId);
        $student    = Student::where('user_id', auth()->id())->firstOrFail();
        $isLate     = now()->isAfter($assignment->due_date);

        return AssignmentSubmission::updateOrCreate(
            ['assignment_id' => $assignmentId, 'student_id' => $student->id],
            array_merge($data, ['is_late' => $isLate])
        );
    }

    public function gradeSubmission(AssignmentSubmission $submission, array $data): AssignmentSubmission
    {
        $assignment = $submission->assignment;

        if ($data['marks'] > $assignment->total_marks) {
            abort(422, 'Nilai melebihi total marks yang diizinkan.');
        }

        $submission->update($data);
        return $submission->fresh()->load('student.user', 'assignment');
    }
}
