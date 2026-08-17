<?php

namespace App\Services\Academic;

use App\Models\Academic\TeachingJournal;

class TeachingJournalService
{
    public function create(array $data, int $userId): TeachingJournal
    {
        return TeachingJournal::create(array_merge($data, [
            'school_id'  => $data['school_id'],
            'teacher_id' => $userId,
        ]));
    }

    public function update(TeachingJournal $journal, array $data): TeachingJournal
    {
        $journal->update($data);
        return $journal->fresh();
    }

    public function listForTeacher(int $schoolId, int $teacherId, ?string $from = null, ?string $to = null)
    {
        return TeachingJournal::where('school_id', $schoolId)
            ->where('teacher_id', $teacherId)
            ->when($from, fn ($q) => $q->whereDate('journal_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('journal_date', '<=', $to))
            ->with(['classSection.classRoom', 'classSection.section', 'subject'])
            ->orderByDesc('journal_date')
            ->paginate(20);
    }

    public function listForSchool(int $schoolId, ?int $teacherId = null)
    {
        return TeachingJournal::where('school_id', $schoolId)
            ->when($teacherId, fn ($q) => $q->where('teacher_id', $teacherId))
            ->with(['teacher:id,name', 'classSection.classRoom', 'classSection.section', 'subject'])
            ->orderByDesc('journal_date')
            ->paginate(30);
    }
}
