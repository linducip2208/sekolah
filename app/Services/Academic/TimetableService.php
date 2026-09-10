<?php

namespace App\Services\Academic;

use App\Models\Academic\ClassSection;
use App\Models\Academic\Subject;
use App\Models\Academic\TimetableBreak;
use App\Models\Academic\TimetableSlot;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TimetableService
{
    public function checkConflict(array $data, ?int $excludeId = null): array
    {
        $schoolId = auth()->user()->school_id;
        $this->assertReferences($schoolId, (int) $data['class_section_id'], isset($data['subject_id']) ? (int) $data['subject_id'] : null, (int) $data['teacher_id']);
        $conflicts = [];

        $timeOverlap = function ($q) use ($data) {
            $q->where(function ($q) use ($data) {
                $q->where('start_time', '<', $data['end_time'])
                    ->where('end_time', '>', $data['start_time']);
            });
        };

        $teacherConflict = TimetableSlot::where('school_id', $schoolId)
            ->where('teacher_id', $data['teacher_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->where($timeOverlap)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->with('classSection.classRoom', 'classSection.section', 'subject')
            ->first();

        if ($teacherConflict) {
            $conflicts[] = [
                'type' => 'teacher_conflict',
                'message' => "Guru sudah mengajar {$teacherConflict->subject->name} pada waktu ini.",
            ];
        }

        $classConflict = TimetableSlot::where('school_id', $schoolId)
            ->where('class_section_id', $data['class_section_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->where($timeOverlap)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->first();

        if ($classConflict) {
            $conflicts[] = [
                'type' => 'class_conflict',
                'message' => 'Rombel sudah memiliki jadwal pada waktu ini.',
            ];
        }

        if (! empty($data['room'])) {
            $roomConflict = TimetableSlot::where('school_id', $schoolId)
                ->where('room', $data['room'])
                ->where('day_of_week', $data['day_of_week'])
                ->where($timeOverlap)
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->first();

            if ($roomConflict) {
                $conflicts[] = [
                    'type' => 'room_conflict',
                    'message' => 'Ruangan sudah digunakan pada waktu ini.',
                ];
            }
        }

        return $conflicts;
    }

    public function store(array $data): TimetableSlot
    {
        $this->assertReferences($this->schoolId(), (int) $data['class_section_id'], (int) $data['subject_id'], (int) $data['teacher_id']);
        $conflicts = $this->checkConflict($data);
        if (! empty($conflicts)) {
            abort(422, $conflicts[0]['message']);
        }

        $data['school_id'] = auth()->user()->school_id;

        return TimetableSlot::create($data);
    }

    public function update(TimetableSlot $slot, array $data): TimetableSlot
    {
        abort_unless((int) $slot->school_id === $this->schoolId(), 404);
        $merged = array_merge($slot->toArray(), $data);
        $this->assertReferences($this->schoolId(), (int) $merged['class_section_id'], (int) $merged['subject_id'], (int) $merged['teacher_id']);
        $conflicts = $this->checkConflict(
            $merged,
            $slot->id
        );
        if (! empty($conflicts)) {
            abort(422, $conflicts[0]['message']);
        }

        $slot->update($data);

        return $slot->fresh()->load('subject', 'teacher', 'classSection');
    }

    public function bulkReplace(int $classSectionId, array $slots): int
    {
        $schoolId = $this->schoolId();
        ClassSection::withoutGlobalScopes()->where('school_id', $schoolId)->findOrFail($classSectionId);

        foreach ($slots as $slot) {
            $this->assertReferences($schoolId, $classSectionId, (int) $slot['subject_id'], (int) $slot['teacher_id']);
            abort_unless($slot['start_time'] < $slot['end_time'], 422, 'Jam selesai harus setelah jam mulai.');
        }

        return DB::transaction(function () use ($classSectionId, $slots) {
            TimetableSlot::where('school_id', $this->schoolId())
                ->where('class_section_id', $classSectionId)
                ->delete();

            $schoolId = $this->schoolId();
            $count = 0;
            foreach ($slots as $slot) {
                $data = array_merge($slot, [
                    'school_id' => $schoolId,
                    'class_section_id' => $classSectionId,
                ]);
                $this->assertBatchConflict($data, $schoolId);
                TimetableSlot::create(array_merge($slot, [
                    'school_id' => $schoolId,
                    'class_section_id' => $classSectionId,
                ]));
                $count++;
            }

            return $count;
        });
    }

    public function getWeeklyForClass(int $classSectionId): array
    {
        ClassSection::withoutGlobalScopes()->where('school_id', $this->schoolId())->findOrFail($classSectionId);
        $slots = TimetableSlot::where('class_section_id', $classSectionId)
            ->with('subject', 'teacher', 'classSection.classRoom', 'classSection.section')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week')
            ->map(fn ($group) => $group->map(fn ($s) => $this->formatSlot($s)))
            ->toArray();

        $breaks = TimetableBreak::orderBy('day_of_week')->orderBy('start_time')->get();

        return ['slots' => $slots, 'breaks' => $breaks];
    }

    public function getWeeklyForTeacher(int $teacherId): array
    {
        User::withoutGlobalScopes()->where('school_id', $this->schoolId())->findOrFail($teacherId);
        $slots = TimetableSlot::where('teacher_id', $teacherId)
            ->with('subject', 'classSection.classRoom', 'classSection.section')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week')
            ->map(fn ($group) => $group->map(fn ($s) => $this->formatSlot($s)))
            ->toArray();

        $breaks = TimetableBreak::orderBy('day_of_week')->orderBy('start_time')->get();

        return ['slots' => $slots, 'breaks' => $breaks];
    }

    private function formatSlot(TimetableSlot $slot): array
    {
        return [
            'id' => $slot->id,
            'subject' => $slot->subject->name,
            'subject_code' => $slot->subject->code ?? null,
            'teacher' => $slot->teacher->name,
            'teacher_id' => $slot->teacher_id,
            'start_time' => $slot->start_time,
            'end_time' => $slot->end_time,
            'room' => $slot->room,
            'day_of_week' => $slot->day_of_week,
        ];
    }

    private function schoolId(): int
    {
        return (int) auth()->user()->school_id;
    }

    private function assertReferences(int $schoolId, int $classSectionId, ?int $subjectId, int $teacherId): void
    {
        ClassSection::withoutGlobalScopes()->where('school_id', $schoolId)->findOrFail($classSectionId);
        User::withoutGlobalScopes()->where('school_id', $schoolId)->findOrFail($teacherId);

        if ($subjectId !== null) {
            Subject::withoutGlobalScopes()->where('school_id', $schoolId)->findOrFail($subjectId);
        }
    }

    private function assertBatchConflict(array $data, int $schoolId): void
    {
        $timeOverlaps = static fn (array $other): bool => $data['start_time'] < $other['end_time'] && $data['end_time'] > $other['start_time'];

        $existing = TimetableSlot::where('school_id', $schoolId)
            ->where('day_of_week', $data['day_of_week'])
            ->get(['class_section_id', 'teacher_id', 'room', 'start_time', 'end_time']);

        foreach ($existing as $other) {
            if (! $timeOverlaps($other->toArray())) {
                continue;
            }

            if ((int) $other->class_section_id === (int) $data['class_section_id']) {
                abort(422, 'Rombel memiliki jadwal yang bentrok dalam batch.');
            }
            if ((int) $other->teacher_id === (int) $data['teacher_id']) {
                abort(422, 'Guru memiliki jadwal yang bentrok dalam batch.');
            }
            if (! empty($data['room']) && $other->room === $data['room']) {
                abort(422, 'Ruangan memiliki jadwal yang bentrok dalam batch.');
            }
        }
    }
}
