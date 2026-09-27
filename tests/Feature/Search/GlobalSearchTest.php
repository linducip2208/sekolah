<?php

use App\Models\Academic\Student;
use App\Models\School;
use App\Models\User;

test('global search finds student by name without N+1', function () {
    $school = School::factory()->create(['settings' => []]);
    app()->instance('current_school', $school);

    $admin = User::factory()->create(['school_id' => $school->id, 'is_active' => true]);
    $admin->assignRole('admin');

    $studentUser = User::factory()->create([
        'school_id' => $school->id, 'name' => 'Zahra Searchable Unik',
    ]);
    $student = Student::create([
        'user_id' => $studentUser->id, 'school_id' => $school->id, 'admission_no' => 'NIS-SEARCH-1',
    ]);

    \Illuminate\Support\Facades\DB::enableQueryLog();
    $response = $this->actingAs($admin)->getJson(route('admin.search', ['q' => 'Zahra Searchable']));
    $queries = \Illuminate\Support\Facades\DB::getQueryLog();

    $response->assertOk();
    $titles = collect($response->json('results'))->pluck('title')->all();
    expect($titles)->toContain('Zahra Searchable Unik');

    // Bounded queries: no per-row users lookup.
    $userLookups = collect($queries)->filter(fn ($q) =>
        str_contains($q['query'], 'from `users`') && str_contains($q['query'], 'limit 1')
    )->count();
    expect($userLookups)->toBe(0);
});
