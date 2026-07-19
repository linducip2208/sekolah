# Module 16 — Transport Management

## Depends On
Module 04 (academic structure — students must exist)

## What to Build
Manajemen transportasi sekolah: rute, kendaraan, penugasan supir,
alokasi siswa ke rute, dan laporan. Terintegrasi dengan fee untuk biaya transport.

---

## Database Schema

```php
// transport_routes table
Schema::create('transport_routes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->string('title');                   // "Rute 1 — Bekasi Barat"
    $table->string('start_point');
    $table->string('end_point');
    $table->time('pickup_time');               // jam jemput pagi
    $table->time('drop_time');                 // jam antar sore
    $table->unsignedInteger('fee_per_month')->default(0); // integer cents
    $table->unsignedSmallInteger('distance_km')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes();
    $table->index(['school_id', 'is_active']);
});

// transport_route_stops table (halte/titik pemberhentian)
Schema::create('transport_route_stops', function (Blueprint $table) {
    $table->id();
    $table->foreignId('transport_route_id')->constrained()->cascadeOnDelete();
    $table->string('stop_name');
    $table->time('pickup_time');
    $table->unsignedTinyInteger('order');
    $table->timestamps();
});

// vehicles table
Schema::create('vehicles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->string('name');                    // "Bus Sekolah 1"
    $table->string('registration_no');         // plat nomor
    $table->string('type');                    // bus | minibus | van
    $table->unsignedTinyInteger('capacity');   // jumlah kursi
    $table->string('driver_name')->nullable();
    $table->string('driver_phone')->nullable();
    $table->string('driver_license')->nullable();
    $table->date('insurance_expiry')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes();
    $table->index(['school_id', 'is_active']);
    $table->unique(['school_id', 'registration_no']);
});

// route_vehicles pivot (satu rute bisa punya beberapa kendaraan)
Schema::create('route_vehicles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('transport_route_id')->constrained()->cascadeOnDelete();
    $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
    $table->timestamps();
    $table->unique(['transport_route_id', 'vehicle_id']);
});

// student_transports table (alokasi siswa ke rute)
Schema::create('student_transports', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->foreignId('transport_route_id')->constrained()->cascadeOnDelete();
    $table->foreignId('transport_route_stop_id')->nullable()->constrained();
    $table->date('start_date');
    $table->date('end_date')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes();
    $table->unique(['school_id', 'student_id', 'is_active'], 'one_active_transport');
    $table->index(['school_id', 'transport_route_id', 'is_active']);
});
```

---

## API Endpoints

| Method | URI                                                 | Role              | Deskripsi                         |
|--------|-----------------------------------------------------|-------------------|-----------------------------------|
| GET    | `/api/v1/transport/routes`                          | admin, recept, all| List rute aktif                   |
| POST   | `/api/v1/transport/routes`                          | admin             | Buat rute                         |
| PUT    | `/api/v1/transport/routes/{id}`                     | admin             | Update rute                       |
| GET    | `/api/v1/transport/routes/{id}/students`            | admin, recept     | Siswa di rute ini                 |
| GET    | `/api/v1/transport/vehicles`                        | admin, recept     | List kendaraan                    |
| POST   | `/api/v1/transport/vehicles`                        | admin             | Tambah kendaraan                  |
| PUT    | `/api/v1/transport/vehicles/{id}`                   | admin             | Update kendaraan                  |
| POST   | `/api/v1/transport/allocations`                     | admin, recept     | Alokasikan siswa ke rute          |
| PUT    | `/api/v1/transport/allocations/{id}/deactivate`     | admin, recept     | Hentikan transport siswa          |
| GET    | `/api/v1/transport/student/{studentId}`             | admin, own, parent| Info transport satu siswa         |
| GET    | `/api/v1/transport/report/passengers`               | admin, recept     | Laporan penumpang per rute        |

---

## Files to Create

```
Modules/Facilities/
  Http/Controllers/
    TransportRouteController.php
    VehicleController.php
    StudentTransportController.php
  Models/
    TransportRoute.php
    TransportRouteStop.php
    Vehicle.php
    StudentTransport.php
  Services/TransportService.php
  Repositories/TransportRepository.php
  Policies/TransportPolicy.php
```

---

## Acceptance Criteria

- [ ] Satu siswa hanya punya satu alokasi transport aktif
- [ ] Rute menampilkan daftar halte dengan waktu jemput
- [ ] Laporan menampilkan total penumpang per rute
- [ ] Parent dan student dapat melihat info rute & halte mereka
- [ ] Kendaraan bisa di-assign ke lebih dari satu rute

## Tests to Write

```
tests/Feature/Transport/
  RouteCrudTest.php
  VehicleCrudTest.php
  AllocateStudentTest.php
  DuplicateAllocationTest.php
  StudentViewOwnRouteTest.php
```
