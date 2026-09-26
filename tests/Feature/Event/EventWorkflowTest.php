<?php

use App\Models\Event\EventRsvp;
use App\Models\Event\SchoolEvent;
use App\Models\School;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->school = School::factory()->create(['settings' => []]);
    $this->otherSchool = School::factory()->create(['settings' => []]);
    app()->instance('current_school', $this->school);

    $this->admin = User::factory()->create(['school_id' => $this->school->id]);
    $this->admin->assignRole('admin');
    $this->parent = User::factory()->create(['school_id' => $this->school->id]);
    $this->parent->assignRole('parent');
    $this->foreignAdmin = User::factory()->create(['school_id' => $this->otherSchool->id]);
    $this->foreignAdmin->assignRole('admin');

    $this->event = SchoolEvent::create([
        'school_id' => $this->school->id,
        'title' => 'Parent Gathering',
        'slug' => 'parent-gathering',
        'description' => 'A school event.',
        'event_type' => 'parent_meeting',
        'starts_at' => now()->addDays(2),
        'ends_at' => now()->addDays(2)->addHour(),
        'venue' => 'Hall',
        'capacity' => 1,
        'is_published' => true,
    ]);
});

it('allows self-service RSVP but protects event administration', function () {
    Sanctum::actingAs($this->parent);

    $this->postJson("/api/v1/events/{$this->event->id}/rsvp", [
        'status' => 'going',
    ])->assertOk();

    $this->postJson('/api/v1/events', [
        'title' => 'Unauthorized',
        'description' => 'No.',
        'event_type' => 'seminar',
        'starts_at' => now()->addDays(3),
        'ends_at' => now()->addDays(3)->addHour(),
        'venue' => 'Hall',
    ])->assertForbidden();
});

it('keeps event administration and QR check-in school-bound', function () {
    $foreignEvent = SchoolEvent::create([
        'school_id' => $this->otherSchool->id,
        'title' => 'Foreign Event',
        'slug' => 'foreign-event',
        'description' => 'Foreign.',
        'event_type' => 'seminar',
        'starts_at' => now()->addDays(2),
        'ends_at' => now()->addDays(2)->addHour(),
        'venue' => 'Foreign Hall',
    ]);
    $foreignRsvp = EventRsvp::create([
        'school_id' => $this->otherSchool->id,
        'school_event_id' => $foreignEvent->id,
        'user_id' => $this->foreignAdmin->id,
        'status' => 'going',
        'ticket_qr_token' => 'foreign-token',
    ]);

    Sanctum::actingAs($this->admin);

    $this->getJson("/api/v1/events/{$foreignEvent->id}/rsvps")->assertNotFound();
    $this->postJson('/api/v1/events/check-in', [
        'qr_token' => $foreignRsvp->ticket_qr_token,
    ])->assertNotFound();
});

it('does not allow RSVP capacity to be bypassed by a second participant', function () {
    Sanctum::actingAs($this->parent);
    $this->postJson("/api/v1/events/{$this->event->id}/rsvp", ['status' => 'going'])->assertOk();

    $secondParent = User::factory()->create(['school_id' => $this->school->id]);
    $secondParent->assignRole('parent');
    Sanctum::actingAs($secondParent);

    $this->postJson("/api/v1/events/{$this->event->id}/rsvp", ['status' => 'going'])
        ->assertStatus(422)
        ->assertJsonPath('message', 'Kapasitas penuh');
});
