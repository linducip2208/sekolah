<?php

use App\Models\RoomBooking\BookableRoom;
use App\Models\RoomBooking\RoomBooking;
use App\Models\School;
use App\Models\User;
use App\Services\RoomBooking\RoomBookingService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

beforeEach(function () {
    $this->service = app(RoomBookingService::class);
    $this->school = School::factory()->create();
    $this->user = User::factory()->create(['school_id' => $this->school->id]);
    $this->room = BookableRoom::create([
        'school_id' => $this->school->id,
        'name' => 'Ruang Rapat',
        'room_type' => 'meeting',
        'capacity' => 12,
        'is_active' => true,
    ]);
    $this->actingAs($this->user);
});

it('creates a booking with the authenticated school ownership', function () {
    $booking = $this->service->createBooking([
        'bookable_room_id' => $this->room->id,
        'user_id' => $this->user->id,
        'title' => 'Rapat wali kelas',
        'date' => '2026-12-01',
        'start_time' => '09:00',
        'end_time' => '10:00',
    ]);

    expect($booking->school_id)->toBe($this->school->id)
        ->and($booking->status)->toBe('approved');
});

it('rejects a room from another school', function () {
    $foreignSchool = School::factory()->create();
    $foreignRoom = BookableRoom::create([
        'school_id' => $foreignSchool->id,
        'name' => 'Ruang Foreign',
        'room_type' => 'meeting',
        'is_active' => true,
    ]);

    $this->expectException(ModelNotFoundException::class);
    $this->service->createBooking([
        'bookable_room_id' => $foreignRoom->id,
        'user_id' => $this->user->id,
        'title' => 'Tidak boleh',
        'date' => '2026-12-01',
        'start_time' => '09:00',
        'end_time' => '10:00',
    ]);
});

it('does not allow approving a booking from another school', function () {
    $foreignSchool = School::factory()->create();
    $foreignUser = User::factory()->create(['school_id' => $foreignSchool->id]);
    $foreignRoom = BookableRoom::create([
        'school_id' => $foreignSchool->id,
        'name' => 'Ruang Foreign',
        'room_type' => 'meeting',
        'is_active' => true,
    ]);
    $foreignBooking = RoomBooking::create([
        'school_id' => $foreignSchool->id,
        'bookable_room_id' => $foreignRoom->id,
        'user_id' => $foreignUser->id,
        'title' => 'Booking foreign',
        'date' => '2026-12-01',
        'start_time' => '09:00',
        'end_time' => '10:00',
        'status' => 'pending',
    ]);

    $this->expectException(ModelNotFoundException::class);
    $this->service->approve($foreignBooking->id, $this->user->id);
});
