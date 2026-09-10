<?php

namespace App\Services\Academic;

use App\Models\Academic\Assignment;
use App\Models\Academic\AssignmentSubmission;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Lesson;
use App\Models\Academic\Student;
use App\Models\Academic\StudyMaterial;
use App\Models\Academic\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ClassroomService
{
    public function createLesson(array $data): Lesson
    {
        $schoolId = $this->schoolId();
        $this->assertAcademicReferences($schoolId, $data['class_section_id'], $data['subject_id'], auth()->id());
        $data['school_id'] = auth()->user()->school_id;
        $data['teacher_id'] = auth()->id();

        return Lesson::create($data);
    }

    public function getLessonsForClass(int $classSectionId, ?int $subjectId = null): Collection
    {
        $schoolId = $this->schoolId();
        ClassSection::withoutGlobalScopes()->where('school_id', $schoolId)->findOrFail($classSectionId);

        if ($subjectId !== null) {
            Subject::withoutGlobalScopes()->where('school_id', $schoolId)->findOrFail($subjectId);
        }

        return Lesson::where('class_section_id', $classSectionId)
            ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId))
            ->with('subject', 'teacher')
            ->latest()
            ->get();
    }

    public function addMaterial(int $lessonId, array $data): StudyMaterial
    {
        $lesson = Lesson::where('school_id', $this->schoolId())->findOrFail($lessonId);

        return $lesson->studyMaterials()->create($data);
    }

    public function createAssignment(array $data): Assignment
    {
        $lesson = Lesson::where('school_id', $this->schoolId())->findOrFail($data['lesson_id']);
        $data['school_id'] = $lesson->school_id;

        return Assignment::create($data);
    }

    public function submitAssignment(int $assignmentId, array $data): AssignmentSubmission
    {
        $assignment = Assignment::where('school_id', $this->schoolId())->findOrFail($assignmentId);
        $student = Student::where('school_id', $this->schoolId())->where('user_id', auth()->id())->firstOrFail();
        $isLate = now()->isAfter($assignment->due_date);

        return AssignmentSubmission::updateOrCreate(
            ['assignment_id' => $assignmentId, 'student_id' => $student->id],
            array_merge($data, ['is_late' => $isLate])
        );
    }

    public function gradeSubmission(AssignmentSubmission $submission, array $data): AssignmentSubmission
    {
        $assignment = Assignment::withoutGlobalScopes()
            ->where('school_id', $this->schoolId())
            ->findOrFail($submission->assignment_id);
        Student::withoutGlobalScopes()
            ->where('school_id', $this->schoolId())
            ->findOrFail($submission->student_id);

        if ($data['marks'] > $assignment->total_marks) {
            abort(422, 'Nilai melebihi total marks yang diizinkan.');
        }

        $submission->update($data);

        return $submission->fresh()->load('student.user', 'assignment');
    }

    private function schoolId(): int
    {
        return (int) auth()->user()->school_id;
    }

    private function assertAcademicReferences(int $schoolId, int $classSectionId, int $subjectId, int $teacherId): void
    {
        ClassSection::withoutGlobalScopes()->where('school_id', $schoolId)->findOrFail($classSectionId);
        Subject::withoutGlobalScopes()->where('school_id', $schoolId)->findOrFail($subjectId);
        User::withoutGlobalScopes()->where('school_id', $schoolId)->findOrFail($teacherId);
    }
}
