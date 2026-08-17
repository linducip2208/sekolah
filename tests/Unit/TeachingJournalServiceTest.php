<?php

use App\Models\Academic\TeachingJournal;
use App\Models\School;
use App\Models\User;
use App\Services\Academic\TeachingJournalService;

beforeEach(function () {
    $this->service = app(TeachingJournalService::class);
    $this->school = School::factory()->create();
    $this->teacher = User::factory()->create(['school_id' => $this->school->id]);
});

it('creates a teaching journal', function () {
    $journal = $this->service->create([
        'school_id' => $this->school->id,
        'journal_date' => '2026-08-17',
        'material' => 'Persamaan Linear',
        'learning_activity' => 'Diskusi kelompok',
        'competency_ids' => [],
    ], $this->teacher->id);

    expect($journal->teacher_id)->toBe($this->teacher->id);
    expect($journal->material)->toBe('Persamaan Linear');
    $this->assertDatabaseHas('teaching_journals', ['school_id' => $this->school->id, 'teacher_id' => $this->teacher->id]);
});

it('lists journals for a teacher', function () {
    $this->service->create([
        'school_id' => $this->school->id, 'journal_date' => '2026-08-17', 'material' => 'A', 'competency_ids' => [],
    ], $this->teacher->id);

    $journals = $this->service->listForTeacher($this->school->id, $this->teacher->id);

    expect($journals->total())->toBe(1);
});

it('updates a journal reflection', function () {
    $journal = $this->service->create([
        'school_id' => $this->school->id, 'journal_date' => '2026-08-17', 'material' => 'A', 'competency_ids' => [],
    ], $this->teacher->id);

    $updated = $this->service->update($journal, ['reflection' => 'Siswa antusias.']);

    expect($updated->reflection)->toBe('Siswa antusias.');
});
