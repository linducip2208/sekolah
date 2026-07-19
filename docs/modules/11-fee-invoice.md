# Module 11 — Fee & Invoice Management

## Depends On
Module 10 (admission — students must exist), Module 03 (school setup — academic_year, semester)

## What to Build
Manajemen SPP dan tagihan sekolah. Kategori biaya, struktur iuran per kelas,
generate invoice otomatis, pembayaran, laporan keuangan, dan export.

---

## Database Schema

```php
// fee_categories table (jenis biaya)
Schema::create('fee_categories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->string('name');                    // "SPP Bulanan", "Biaya Seragam", "Biaya Ujian"
    $table->text('description')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes();
    $table->unique(['school_id', 'name']);
});

// fee_structures table (besaran biaya per kelas per periode)
Schema::create('fee_structures', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('fee_category_id')->constrained()->cascadeOnDelete();
    $table->foreignId('class_room_id')->nullable()->constrained(); // null = berlaku semua kelas
    $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
    $table->string('name');                    // "SPP Kelas 10 2024/2025"
    $table->unsignedInteger('amount');         // integer cents
    $table->string('frequency');              // 'monthly' | 'yearly' | 'one_time' | 'semester'
    $table->date('due_date')->nullable();      // untuk one-time
    $table->unsignedInteger('late_fee')->default(0);         // denda terlambat
    $table->unsignedInteger('late_fee_per_day')->default(0); // denda harian
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes();
    $table->index(['school_id', 'academic_year_id', 'class_room_id']);
});

// fee_discounts table (diskon per siswa atau per kategori)
Schema::create('fee_discounts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->string('name');                    // "Beasiswa Prestasi", "Diskon Kakak-Adik"
    $table->enum('type', ['percentage', 'fixed'])->default('fixed');
    $table->unsignedInteger('value');          // persen atau nominal
    $table->timestamps();
    $table->softDeletes();
});

// fee_invoices table
Schema::create('fee_invoices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->foreignId('fee_structure_id')->constrained();
    $table->string('invoice_no')->nullable();  // INV-2025-001
    $table->date('invoice_date');
    $table->date('due_date');
    $table->unsignedInteger('amount');         // total sebelum diskon
    $table->unsignedInteger('discount_amount')->default(0);
    $table->unsignedInteger('fine_amount')->default(0);
    $table->unsignedInteger('net_amount');     // amount - discount + fine
    $table->enum('status', ['unpaid', 'partial', 'paid', 'waived'])->default('unpaid');
    $table->string('month')->nullable();       // "2025-07" untuk monthly
    $table->string('note')->nullable();
    $table->timestamps();
    $table->softDeletes();
    $table->unique(['school_id', 'student_id', 'fee_structure_id', 'month']);
    $table->index(['school_id', 'student_id', 'status']);
    $table->index(['school_id', 'due_date', 'status']);
});

// fee_payments table
Schema::create('fee_payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('fee_invoice_id')->constrained()->cascadeOnDelete();
    $table->foreignId('collected_by')->constrained('users');
    $table->unsignedInteger('amount');         // amount dibayarkan kali ini
    $table->date('payment_date');
    $table->string('payment_mode');            // cash | bank_transfer | online | cheque
    $table->string('transaction_id')->nullable();
    $table->string('receipt_no')->nullable();
    $table->text('note')->nullable();
    $table->timestamps();
    $table->index(['school_id', 'fee_invoice_id']);
    $table->index(['school_id', 'payment_date']);
});
```

---

## API Endpoints

| Method | URI                                                    | Role             | Deskripsi                             |
|--------|--------------------------------------------------------|------------------|---------------------------------------|
| GET    | `/api/v1/fees/categories`                              | admin, account   | List kategori biaya                   |
| POST   | `/api/v1/fees/categories`                              | admin            | Buat kategori biaya                   |
| GET    | `/api/v1/fees/structures`                              | admin, account   | List struktur biaya                   |
| POST   | `/api/v1/fees/structures`                              | admin            | Buat struktur biaya                   |
| PUT    | `/api/v1/fees/structures/{id}`                         | admin            | Update struktur biaya                 |
| GET    | `/api/v1/fees/invoices`                                | admin, account   | List semua invoice (filter banyak)    |
| POST   | `/api/v1/fees/invoices`                                | admin, account   | Buat invoice manual                   |
| POST   | `/api/v1/fees/invoices/generate`                       | admin            | Generate invoice bulk (bulanan)       |
| GET    | `/api/v1/fees/invoices/{id}`                           | admin,account,own| Detail invoice                        |
| GET    | `/api/v1/fees/student/{studentId}/dues`                | all (own/child)  | Semua tagihan siswa                   |
| POST   | `/api/v1/fees/invoices/{id}/pay`                       | admin, account   | Catat pembayaran                      |
| POST   | `/api/v1/fees/invoices/{id}/waive`                     | admin            | Waive / hapuskan tagihan              |
| GET    | `/api/v1/fees/invoices/{id}/receipt`                   | all (own/child)  | Download receipt PDF                  |
| GET    | `/api/v1/fees/report/collection`                       | admin, account   | Laporan koleksi per periode           |
| GET    | `/api/v1/fees/report/defaulters`                       | admin, account   | Daftar siswa menunggak                |

---

## FeeService Implementation

```php
// Modules/Finance/Services/FeeService.php
class FeeService
{
    // Generate invoice bulanan untuk semua siswa aktif
    public function generateMonthlyInvoices(int $schoolId, string $month): array
    {
        $structures = FeeStructure::where('school_id', $schoolId)
            ->where('frequency', 'monthly')
            ->where('is_active', true)
            ->with('classRoom')
            ->get();

        $created = 0;
        $skipped = 0;

        foreach ($structures as $structure) {
            $query = Student::where('school_id', $schoolId);

            if ($structure->class_room_id) {
                $query->whereHas('classSection', fn($q) =>
                    $q->where('class_room_id', $structure->class_room_id)
                );
            }

            $students = $query->get();

            foreach ($students as $student) {
                $exists = FeeInvoice::where([
                    'school_id'        => $schoolId,
                    'student_id'       => $student->id,
                    'fee_structure_id' => $structure->id,
                    'month'            => $month,
                ])->exists();

                if ($exists) { $skipped++; continue; }

                FeeInvoice::create([
                    'school_id'        => $schoolId,
                    'student_id'       => $student->id,
                    'fee_structure_id' => $structure->id,
                    'invoice_no'       => $this->generateInvoiceNo($schoolId),
                    'invoice_date'     => today(),
                    'due_date'         => today()->endOfMonth(),
                    'amount'           => $structure->amount,
                    'discount_amount'  => 0,
                    'fine_amount'      => 0,
                    'net_amount'       => $structure->amount,
                    'month'            => $month,
                    'status'           => 'unpaid',
                ]);
                $created++;
            }
        }

        return ['created' => $created, 'skipped' => $skipped];
    }

    public function recordPayment(FeeInvoice $invoice, array $data, User $collector): FeePayment
    {
        return DB::transaction(function () use ($invoice, $data, $collector) {
            $payment = FeePayment::create([
                'school_id'       => $invoice->school_id,
                'fee_invoice_id'  => $invoice->id,
                'collected_by'    => $collector->id,
                'amount'          => $data['amount'],
                'payment_date'    => $data['payment_date'] ?? today(),
                'payment_mode'    => $data['payment_mode'],
                'transaction_id'  => $data['transaction_id'] ?? null,
                'receipt_no'      => $this->generateReceiptNo($invoice->school_id),
            ]);

            $totalPaid = FeePayment::where('fee_invoice_id', $invoice->id)->sum('amount');

            $status = match(true) {
                $totalPaid >= $invoice->net_amount => 'paid',
                $totalPaid > 0                    => 'partial',
                default                           => 'unpaid',
            };

            $invoice->update(['status' => $status]);

            // Notify student/parent
            NotifyPaymentReceivedJob::dispatch($payment, $invoice->student);

            return $payment;
        });
    }
}
```

---

## Acceptance Criteria

- [ ] Generate invoice bulk tidak membuat duplikat untuk bulan yang sama
- [ ] Status invoice update otomatis setelah pembayaran (unpaid → partial → paid)
- [ ] Tagihan siswa yang menunggak tampil di laporan defaulters
- [ ] Invoice dan receipt bisa di-download sebagai PDF
- [ ] Parent dan siswa hanya melihat tagihan mereka sendiri
- [ ] Laporan koleksi bisa difilter per periode, per kelas, per kategori

## Tests to Write

```
tests/Feature/Fee/
  FeeStructureTest.php
  GenerateMonthlyInvoicesTest.php
  RecordPaymentTest.php
  InvoiceStatusTransitionTest.php
  DefaultersReportTest.php
  StudentViewOwnDuesTest.php
  ParentViewChildDuesTest.php
```
