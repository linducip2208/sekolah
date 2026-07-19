# Module 15 — Hostel Management

## Depends On
Module 04 (academic structure — students must exist)

## What to Build
Manajemen asrama: blok, kamar, alokasi siswa ke kamar, laporan hunian,
dan tagihan biaya asrama terintegrasi dengan modul fee.

---

## Database Schema

```php
// hostel_blocks table
Schema::create('hostel_blocks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->string('name');                    // "Blok A Putra", "Blok B Putri"
    $table->string('gender');                  // male | female | mixed
    $table->string('warden_name')->nullable(); // nama pengasuh
    $table->string('warden_phone')->nullable();
    $table->text('description')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes();
    $table->index(['school_id', 'gender']);
});

// hostel_rooms table
Schema::create('hostel_rooms', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('hostel_block_id')->constrained()->cascadeOnDelete();
    $table->string('room_no');                 // "A-101", "B-204"
    $table->string('type')->default('sharing');// single | sharing | dormitory
    $table->unsignedTinyInteger('capacity');   // jumlah tempat tidur
    $table->unsignedInteger('fee_per_month')->default(0); // integer cents
    $table->enum('status', ['available', 'full', 'maintenance'])->default('available');
    $table->text('description')->nullable();
    $table->timestamps();
    $table->softDeletes();
    $table->unique(['school_id', 'hostel_block_id', 'room_no']);
    $table->index(['school_id', 'hostel_block_id', 'status']);
});

// hostel_allocations table
Schema::create('hostel_allocations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('hostel_room_id')->constrained()->cascadeOnDelete();
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->date('check_in_date');
    $table->date('check_out_date')->nullable();
    $table->enum('status', ['active', 'checked_out', 'transferred'])->default('active');
    $table->text('note')->nullable();
    $table->foreignId('allocated_by')->constrained('users');
    $table->timestamps();
    $table->softDeletes();
    $table->unique(['school_id', 'student_id', 'status'], 'one_active_allocation');
    $table->index(['school_id', 'hostel_room_id', 'status']);
});
```

---

## API Endpoints

| Method | URI                                             | Role              | Deskripsi                          |
|--------|-------------------------------------------------|-------------------|------------------------------------|
| GET    | `/api/v1/hostel/blocks`                         | admin, recept     | List blok asrama                   |
| POST   | `/api/v1/hostel/blocks`                         | admin             | Buat blok                          |
| PUT    | `/api/v1/hostel/blocks/{id}`                    | admin             | Update blok                        |
| GET    | `/api/v1/hostel/blocks/{id}/rooms`              | admin, recept     | List kamar dalam blok              |
| POST   | `/api/v1/hostel/rooms`                          | admin             | Buat kamar                         |
| PUT    | `/api/v1/hostel/rooms/{id}`                     | admin             | Update kamar                       |
| GET    | `/api/v1/hostel/rooms/{id}/occupants`           | admin, recept     | Siswa dalam kamar ini              |
| GET    | `/api/v1/hostel/allocations`                    | admin, recept     | List semua alokasi aktif           |
| POST   | `/api/v1/hostel/allocations`                    | admin, recept     | Alokasikan siswa ke kamar          |
| PUT    | `/api/v1/hostel/allocations/{id}/checkout`      | admin, recept     | Checkout siswa                     |
| GET    | `/api/v1/hostel/student/{studentId}`            | admin, own, parent| Status asrama satu siswa           |
| GET    | `/api/v1/hostel/report/occupancy`               | admin, recept     | Laporan hunian per blok/kamar      |

---

## HostelService Implementation

```php
// Modules/Facilities/Services/HostelService.php
class HostelService
{
    public function allocate(int $roomId, int $studentId, string $checkInDate, User $staff): HostelAllocation
    {
        return DB::transaction(function () use ($roomId, $studentId, $checkInDate, $staff) {
            // Cek: siswa sudah punya alokasi aktif?
            $existingAlloc = HostelAllocation::where('student_id', $studentId)
                ->where('status', 'active')
                ->exists();

            if ($existingAlloc) {
                throw new StudentAlreadyAllocatedException('Siswa sudah memiliki kamar asrama aktif.');
            }

            // Cek: kamar masih ada tempat?
            $room = HostelRoom::lockForUpdate()->findOrFail($roomId);
            $currentOccupants = HostelAllocation::where('hostel_room_id', $roomId)
                ->where('status', 'active')
                ->count();

            if ($currentOccupants >= $room->capacity) {
                throw new RoomFullException("Kamar {$room->room_no} sudah penuh.");
            }

            $allocation = HostelAllocation::create([
                'school_id'      => $staff->school_id,
                'hostel_room_id' => $roomId,
                'student_id'     => $studentId,
                'check_in_date'  => $checkInDate,
                'status'         => 'active',
                'allocated_by'   => $staff->id,
            ]);

            // Update status kamar jika penuh
            if ($currentOccupants + 1 >= $room->capacity) {
                $room->update(['status' => 'full']);
            }

            // Update flag has_hostel di student
            Student::where('id', $studentId)->update(['has_hostel' => true]);

            return $allocation->load('hostelRoom.hostelBlock', 'student.user');
        });
    }

    public function checkout(HostelAllocation $allocation, string $checkOutDate): HostelAllocation
    {
        return DB::transaction(function () use ($allocation, $checkOutDate) {
            $allocation->update([
                'check_out_date' => $checkOutDate,
                'status'         => 'checked_out',
            ]);

            // Kamar kembali available
            $allocation->hostelRoom->update(['status' => 'available']);

            // Update flag student
            Student::where('id', $allocation->student_id)->update(['has_hostel' => false]);

            return $allocation->fresh('hostelRoom', 'student.user');
        });
    }
}
```

---

## Acceptance Criteria

- [ ] Satu siswa hanya bisa punya satu alokasi aktif
- [ ] Tidak bisa alokasikan ke kamar yang sudah penuh
- [ ] Status kamar otomatis berubah ke 'full' saat kapasitas penuh
- [ ] Checkout membebaskan slot kamar
- [ ] Receptionist bisa kelola alokasi, student & parent hanya view

## Tests to Write

```
tests/Feature/Hostel/
  BlockRoomCrudTest.php
  AllocateStudentTest.php
  DuplicateAllocationTest.php
  RoomCapacityTest.php
  CheckoutTest.php
  StudentViewOwnHostelTest.php
```
