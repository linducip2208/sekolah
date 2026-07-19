# Module 14 — Library Management

## Depends On
Module 03 (school setup), Module 02 (auth — librarian role must exist)

## What to Build
Sistem perpustakaan lengkap: katalog buku, manajemen anggota,
peminjaman & pengembalian buku, perhitungan denda, dukungan barcode.
Dikelola utamanya oleh role `librarian`.

---

## Database Schema (MySQL)

```php
// book_categories table
Schema::create('book_categories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->timestamps();
    $table->unique(['school_id', 'name']);
});

// books table
Schema::create('books', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('book_category_id')->constrained();
    $table->string('title');
    $table->string('author')->nullable();
    $table->string('isbn')->nullable();
    $table->string('publisher')->nullable();
    $table->year('publish_year')->nullable();
    $table->string('edition')->nullable();
    $table->unsignedSmallInteger('total_quantity')->default(1);
    $table->unsignedSmallInteger('available_quantity')->default(1);
    $table->string('cover')->nullable();       // S3 path
    $table->string('barcode')->nullable();
    $table->text('description')->nullable();
    $table->string('rack_location')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes();
    $table->index(['school_id', 'title']);
    $table->index(['school_id', 'isbn']);
    $table->index(['school_id', 'barcode']);
});

// book_issues table
Schema::create('book_issues', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('book_id')->constrained();
    $table->foreignId('issued_to')->constrained('users');   // student atau staff
    $table->foreignId('issued_by')->constrained('users');   // librarian
    $table->foreignId('returned_to')->nullable()->constrained('users');
    $table->date('issue_date');
    $table->date('due_date');
    $table->date('return_date')->nullable();
    $table->enum('status', ['issued', 'returned', 'overdue', 'lost'])->default('issued');
    $table->unsignedInteger('fine_amount')->default(0);  // dalam cents (integer!)
    $table->boolean('fine_paid')->default(false);
    $table->string('note')->nullable();
    $table->timestamps();
    $table->index(['school_id', 'issued_to', 'status']);
    $table->index(['school_id', 'book_id', 'status']);
    $table->index(['school_id', 'due_date', 'status']);  // untuk query overdue
});
```

---

## API Endpoints

| Method | URI | Role | Description |
|---|---|---|---|
| GET | `/api/v1/library/books` | all | Cari katalog buku |
| POST | `/api/v1/library/books` | admin, librarian | Tambah buku |
| PUT | `/api/v1/library/books/{id}` | admin, librarian | Update buku |
| DELETE | `/api/v1/library/books/{id}` | admin, librarian | Soft delete |
| POST | `/api/v1/library/books/barcode` | librarian | Lookup by barcode scan |
| POST | `/api/v1/library/issue` | librarian | Pinjamkan buku ke anggota |
| POST | `/api/v1/library/return/{issueId}` | librarian | Kembalikan buku |
| GET | `/api/v1/library/issues` | admin, librarian | Semua peminjaman aktif |
| GET | `/api/v1/library/issues/overdue` | admin, librarian | Buku terlambat dikembalikan |
| GET | `/api/v1/library/member/{userId}` | librarian, student(own), parent(child) | Buku dipinjam anggota |
| POST | `/api/v1/library/fine/{issueId}/pay` | accountant, librarian | Tandai denda sudah dibayar |

---

## LibraryService Implementation

```php
// Modules/Facilities/Services/LibraryService.php
class LibraryService
{
    // Pinjamkan buku
    public function issueBook(int $bookId, int $userId, int $librarianId, int $dueDays = 14): BookIssue
    {
        return DB::transaction(function () use ($bookId, $userId, $librarianId, $dueDays) {
            $book = Book::lockForUpdate()->findOrFail($bookId);

            if ($book->available_quantity < 1) {
                throw new BookNotAvailableException("Tidak ada salinan tersedia: {$book->title}");
            }

            // Cek: anggota punya buku overdue?
            $overdue = BookIssue::where('issued_to', $userId)
                ->where('status', 'overdue')
                ->exists();

            if ($overdue) {
                throw new MemberHasOverdueException("Anggota memiliki buku yang terlambat. Kembalikan dulu.");
            }

            // Cek batas max buku per anggota (dari school settings)
            $school      = app('current_school');
            $maxBooks    = data_get($school->settings, 'library.max_books_per_member', 3);
            $activeBorrows = BookIssue::where('issued_to', $userId)
                ->where('status', 'issued')
                ->count();

            if ($activeBorrows >= $maxBooks) {
                throw new MaxBooksExceededException("Anggota sudah meminjam {$maxBooks} buku (batas maksimal).");
            }

            $issue = BookIssue::create([
                'school_id'  => $book->school_id,
                'book_id'    => $bookId,
                'issued_to'  => $userId,
                'issued_by'  => $librarianId,
                'issue_date' => today(),
                'due_date'   => today()->addDays($dueDays),
                'status'     => 'issued',
            ]);

            $book->decrement('available_quantity');

            return $issue->load('book', 'issuedTo');
        });
    }

    // Kembalikan buku
    public function returnBook(int $issueId, int $librarianId): BookIssue
    {
        return DB::transaction(function () use ($issueId, $librarianId) {
            $issue = BookIssue::lockForUpdate()
                ->where('status', '!=', 'returned')
                ->findOrFail($issueId);

            $fine = $this->calculateFine($issue);

            $issue->update([
                'return_date' => today(),
                'returned_to' => $librarianId,
                'status'      => 'returned',
                'fine_amount' => $fine,
                'fine_paid'   => $fine === 0,
            ]);

            $issue->book->increment('available_quantity');

            return $issue->fresh()->load('book');
        });
    }

    // Denda: ambil dari school settings (default 500 IDR/hari = 50000 cents)
    public function calculateFine(BookIssue $issue): int
    {
        if (today()->lte($issue->due_date)) {
            return 0;
        }

        $school      = app('current_school');
        $finePerDay  = data_get($school->settings, 'library.fine_per_day_cents', 50000);
        $overdueDays = today()->diffInDays($issue->due_date);

        return $overdueDays * $finePerDay;
    }
}
```

---

## Overdue Scheduler

```php
// app/Console/Commands/MarkOverdueBooks.php
// Runs daily: $schedule->command('library:mark-overdue')->dailyAt('07:00');
// Update status = 'overdue' WHERE due_date < today AND status = 'issued'
// Kirim FCM notification ke anggota + parent
```

---

## Barcode Lookup (Flutter Camera)

```dart
// lib/features/library/presentation/pages/barcode_scan_page.dart
// Pakai package: mobile_scanner
// On scan: POST /api/v1/library/books/barcode { "barcode": "978-..." }
// Tampilkan card detail buku dengan tombol [Pinjamkan ke Anggota]
// Cari anggota: search by nama siswa atau ID
```

---

## Settings (per sekolah, di schools.settings JSON)

```json
{
  "library": {
    "max_books_per_member": 3,
    "default_issue_days": 14,
    "fine_per_day_cents": 50000,
    "allow_staff_borrow": true
  }
}
```

---

## Acceptance Criteria

- [ ] Tidak bisa pinjam lebih dari `available_quantity`
- [ ] Tidak bisa pinjam ke anggota yang punya buku overdue
- [ ] `available_quantity` increment saat return, decrement saat issue (always consistent)
- [ ] Denda dihitung benar untuk buku terlambat
- [ ] Scheduler harian tandai overdue dan kirim notifikasi
- [ ] Barcode search mengembalikan buku yang benar
- [ ] Student hanya bisa lihat peminjaman miliknya sendiri
- [ ] Librarian bisa lihat semua peminjaman di sekolahnya
- [ ] Concurrent issue safety: tidak bisa issue melebihi stok (lockForUpdate)

## Tests to Write

```
tests/Feature/Library/
  IssueBookTest.php
  ReturnBookTest.php
  OverdueDetectionTest.php
  FineCalculationTest.php
  BarcodeLookupTest.php
  QuantityConsistencyTest.php   ← concurrent issue safety test
  MaxBooksLimitTest.php
  MemberHasOverdueBlockTest.php
```
