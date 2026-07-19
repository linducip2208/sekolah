# Module 23 — Bus Tracking + ID Gate (Smart School Safety)

## Depends On
Module 16 (Transport), Module 04 (Academic Structure), Module 19 (Notifications)

## What to Build
1. **Bus Tracking GPS realtime** — parent track posisi bus anak di Flutter app
2. **ID Gate** — siswa tap kartu/QR di gerbang masuk-keluar, parent dapat notif otomatis

## Database Schema

```php
Schema::create('vehicle_locations', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
    $t->decimal('lat', 10, 7); $t->decimal('lng', 10, 7);
    $t->decimal('speed_kmh', 5, 2)->nullable();
    $t->decimal('heading_deg', 5, 2)->nullable();
    $t->timestamp('recorded_at');
    $t->index(['vehicle_id', 'recorded_at']);
});

Schema::create('vehicle_trips', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('vehicle_id')->constrained();
    $t->foreignId('transport_route_id')->constrained();
    $t->enum('direction', ['pickup', 'drop']);
    $t->timestamp('started_at'); $t->timestamp('ended_at')->nullable();
    $t->json('stops_completed')->nullable();   // [{stop_id, arrived_at, students_onboard:[id]}]
    $t->string('status', 20)->default('active');
    $t->timestamps();
});

Schema::create('id_gate_devices', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->string('name'); $t->string('location');
    $t->string('device_token_encrypted');
    $t->enum('type', ['entry', 'exit', 'both']);
    $t->boolean('is_active')->default(true);
    $t->timestamps();
});

Schema::create('id_gate_events', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('id_gate_device_id')->constrained();
    $t->foreignId('user_id')->constrained();
    $t->enum('direction', ['in', 'out']);
    $t->timestamp('scanned_at');
    $t->index(['school_id', 'user_id', 'scanned_at']);
});

Schema::create('student_id_cards', function (Blueprint $t) {
    $t->id();
    $t->foreignId('school_id')->constrained()->cascadeOnDelete();
    $t->foreignId('student_id')->unique()->constrained();
    $t->string('card_uid', 50)->unique();        // RFID/NFC UID atau QR code
    $t->string('qr_token', 100)->unique();       // rotating QR token
    $t->boolean('is_active')->default(true);
    $t->timestamp('issued_at');
    $t->timestamps();
});
```

## API Endpoints

| Method | URI | Role |
|---|---|---|
| POST | `/api/v1/devices/gps-ping` | device token | Vehicle GPS device push lokasi |
| GET | `/api/v1/parent/children/{studentId}/bus-location` | parent | Realtime location anak |
| GET | `/api/v1/transport/trips/{id}/track` | parent, admin | Detail trip |
| POST | `/api/v1/devices/gate-scan` | device token | Tap gerbang |
| GET | `/api/v1/parent/children/{studentId}/gate-events` | parent | Riwayat tap gerbang |
| GET | `/api/v1/admin/gate-devices` | admin |
| POST | `/api/v1/admin/students/{id}/id-card` | admin | Issue/regenerate ID card |

## Realtime Broadcasting

- `private-school.{schoolId}.bus.{vehicleId}` — push lokasi GPS
- `private-parent.{userId}` — push event tap gerbang anak

## Acceptance Criteria
- [ ] Device GPS push tiap 10 detik → frontend update live di map
- [ ] Tap gerbang masuk → notif "Anak masuk sekolah jam 07:15"
- [ ] Tap gerbang keluar → notif "Anak keluar sekolah jam 14:00"
- [ ] Parent buka app → lihat posisi bus anak di Google Maps tile
- [ ] Bus dekat halte (radius 200m) → notif "Bus 5 menit lagi"
- [ ] QR token rotating tiap login (anti-clone)
