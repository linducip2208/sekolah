<?php

namespace App\Services\Academic;

use App\Models\Academic\Exam;
use App\Models\Academic\ExamQuestion;
use App\Models\Academic\ExamResult;
use App\Models\Academic\Mark;
use App\Models\Academic\Semester;
use App\Models\Academic\Student;

class ExamService
{
    public function startExam(int $examId): ExamResult
    {
        $exam    = Exam::findOrFail($examId);
        $student = Student::where('user_id', auth()->id())->firstOrFail();

        if ($exam->start_at && now()->isBefore($exam->start_at)) {
            abort(422, 'Ujian belum dimulai.');
        }

        if ($exam->end_at && now()->isAfter($exam->end_at)) {
            abort(422, 'Waktu ujian sudah habis.');
        }

        $result = ExamResult::firstOrCreate(
            ['exam_id' => $examId, 'student_id' => $student->id],
            ['status' => 'pending', 'started_at' => now()]
        );

        if (in_array($result->status, ['passed', 'failed'])) {
            abort(422, 'Ujian sudah dikerjakan.');
        }

        $questions = $exam->questions()->orderBy('order')->get();
        if ($exam->shuffle_questions) {
            $questions = $questions->shuffle()->values();
        }

        return $result->setRelation('exam', $exam->setRelation('questions', $questions));
    }

    public function submitExam(int $examId, array $answers): ExamResult
    {
        $exam    = Exam::with('questions')->findOrFail($examId);
        $student = Student::where('user_id', auth()->id())->firstOrFail();
        $result  = ExamResult::where('exam_id', $examId)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $result->update([
            'answers'      => $answers,
            'submitted_at' => now(),
        ]);

        $this->autoGrade($result, $exam);

        return $result->fresh()->load('exam');
    }

    public function autoGrade(ExamResult $result, Exam $exam): void
    {
        $answers    = $result->answers ?? [];
        $totalMarks = 0;

        foreach ($exam->questions as $question) {
            $studentAnswer = $answers[$question->id] ?? null;

            if (in_array($question->type, ['mcq', 'true_false']) && $studentAnswer !== null) {
                if ((string) $studentAnswer === (string) $question->correct_answer) {
                    $totalMarks += $question->marks;
                }
            }
        }

        $isPassed = $totalMarks >= $exam->pass_marks;

        $result->update([
            'obtained_marks' => $totalMarks,
            'status'         => $isPassed ? 'passed' : 'failed',
        ]);

        $this->writeMark($result, $exam, $totalMarks);
    }

    /** Sync the auto-graded result into the marks/report-card table (end-to-end). */
    protected function writeMark(ExamResult $result, Exam $exam, int $obtained): void
    {
        $semester = Semester::where('school_id', $exam->school_id)
            ->where('is_active', true)
            ->orderByDesc('id')
            ->first()
            ?? Semester::where('school_id', $exam->school_id)->orderByDesc('id')->first();

        if (!$semester) {
            return;
        }

        $pct   = $exam->total_marks > 0 ? ($obtained / $exam->total_marks) * 100 : 0;
        $grade = app(MarksService::class)->resolveGrade($exam->school_id, $pct);

        Mark::updateOrCreate(
            [
                'school_id'  => $exam->school_id,
                'student_id' => $result->student_id,
                'exam_id'    => $exam->id,
                'subject_id' => $exam->subject_id,
            ],
            [
                'semester_id'    => $semester->id,
                'obtained_marks' => $obtained,
                'total_marks'    => $exam->total_marks,
                'grade'          => $grade ?? match (true) {
                    $pct >= 90 => 'A',
                    $pct >= 80 => 'B',
                    $pct >= 70 => 'C',
                    $pct >= 60 => 'D',
                    default    => 'E',
                },
            ]
        );
    }
}
