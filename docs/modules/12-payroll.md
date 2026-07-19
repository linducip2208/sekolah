# Module 12 — Payroll Management

## Depends On
Module 04 (academic structure — staffs must exist), Module 03 (school setup)

## What to Build
Manajemen gaji guru dan staff. Struktur gaji (basic + tunjangan + potongan),
generate slip gaji bulanan, laporan payroll, dan export.

---

## Database Schema

```php
// payroll_allowances table (jenis tunjangan)
Schema::create('payroll_allowances', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->string('title');                    // "Tunjangan Transport", "Uang Makan"
    $table->enum('type', ['fixed', 'percentage'])->default('fixed');
    $table->unsignedInteger('amount')->default(0);     // fixed amount atau %
    $table->timestamps();
    $table->softDeletes();
});

// payroll_deductions table (jenis potongan)
Schema::create('payroll_deductions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->string('title');                    // "BPJS Kesehatan", "Pajak PPh21", "Pinjaman"
    $table->enum('type', ['fixed', 'percentage'])->default('fixed');
    $table->unsignedInteger('amount')->default(0);
    $table->timestamps();
    $table->softDeletes();
});

// staff_payroll_structures table (struktur gaji per staff)
Schema::create('staff_payroll_structures', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
    $table->unsignedInteger('basic_salary');    // integer cents
    $table->json('allowances')->nullable();     // [{id, title, amount}, ...]
    $table->json('deductions')->nullable();     // [{id, title, amount}, ...]
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->softDeletes();
    $table->unique(['school_id', 'staff_id']);
});

// payroll_slips table (slip gaji bulanan)
Schema::create('payroll_slips', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
    $table->string('month');                    // "2025-07"
    $table->unsignedInteger('basic_salary');
    $table->json('allowances')->nullable();     // snapshot saat generate
    $table->json('deductions')->nullable();     // snapshot saat generate
    $table->unsignedInteger('total_allowances')->default(0);
    $table->unsignedInteger('total_deductions')->default(0);
    $table->unsignedInteger('net_salary');      // basic + allowances - deductions
    $table->unsignedTinyInteger('working_days')->default(26);
    $table->unsignedTinyInteger('present_days')->default(26);
    $table->unsignedInteger('attendance_deduction')->default(0);
    $table->enum('status', ['draft', 'paid', 'cancelled'])->default('draft');
    $table->date('paid_date')->nullable();
    $table->string('payment_mode')->nullable(); // bank_transfer | cash | cheque
    $table->text('note')->nullable();
    $table->foreignId('generated_by')->constrained('users');
    $table->timestamps();
    $table->softDeletes();
    $table->unique(['school_id', 'staff_id', 'month']);
    $table->index(['school_id', 'month', 'status']);
});
```

---

## API Endpoints

| Method | URI                                                  | Role             | Deskripsi                          |
|--------|------------------------------------------------------|------------------|------------------------------------|
| GET    | `/api/v1/payroll/allowances`                         | admin, account   | List jenis tunjangan               |
| POST   | `/api/v1/payroll/allowances`                         | admin            | Buat tunjangan                     |
| GET    | `/api/v1/payroll/deductions`                         | admin, account   | List jenis potongan                |
| POST   | `/api/v1/payroll/deductions`                         | admin            | Buat potongan                      |
| GET    | `/api/v1/payroll/structures`                         | admin, account   | List struktur gaji semua staff     |
| POST   | `/api/v1/payroll/structures`                         | admin            | Set struktur gaji staff            |
| PUT    | `/api/v1/payroll/structures/{id}`                    | admin            | Update struktur gaji               |
| GET    | `/api/v1/payroll/structures/staff/{staffId}`         | admin, own       | Struktur gaji satu staff           |
| GET    | `/api/v1/payroll/slips`                              | admin, account   | List slip gaji (filter bulan)      |
| POST   | `/api/v1/payroll/slips/generate`                     | admin, account   | Generate slip gaji bulanan (bulk)  |
| GET    | `/api/v1/payroll/slips/{id}`                         | admin, account, own | Detail slip gaji                |
| GET    | `/api/v1/payroll/slips/staff/{staffId}`              | admin, own       | Riwayat slip gaji satu staff       |
| POST   | `/api/v1/payroll/slips/{id}/mark-paid`               | admin, account   | Tandai sudah dibayar               |
| GET    | `/api/v1/payroll/slips/{id}/download`                | admin, own       | Download slip PDF                  |
| GET    | `/api/v1/payroll/report`                             | admin, account   | Laporan payroll per bulan          |

---

## PayrollService Implementation

```php
// Modules/Finance/Services/PayrollService.php
class PayrollService
{
    public function generateMonthlySlips(int $schoolId, string $month): array
    {
        $staffs = Staff::where('school_id', $schoolId)
            ->with('payrollStructure')
            ->get();

        $created = 0;
        $skipped = 0;

        foreach ($staffs as $staff) {
            if (!$staff->payrollStructure) { $skipped++; continue; }

            $exists = PayrollSlip::where([
                'school_id' => $schoolId,
                'staff_id'  => $staff->id,
                'month'     => $month,
            ])->exists();

            if ($exists) { $skipped++; continue; }

            $structure = $staff->payrollStructure;

            $totalAllowances = collect($structure->allowances ?? [])->sum(function ($a) use ($structure) {
                return $a['type'] === 'percentage'
                    ? intval($structure->basic_salary * ($a['amount'] / 100))
                    : $a['amount'];
            });

            $totalDeductions = collect($structure->deductions ?? [])->sum(function ($d) use ($structure) {
                return $d['type'] === 'percentage'
                    ? intval($structure->basic_salary * ($d['amount'] / 100))
                    : $d['amount'];
            });

            $netSalary = $structure->basic_salary + $totalAllowances - $totalDeductions;

            PayrollSlip::create([
                'school_id'        => $schoolId,
                'staff_id'         => $staff->id,
                'month'            => $month,
                'basic_salary'     => $structure->basic_salary,
                'allowances'       => $structure->allowances,
                'deductions'       => $structure->deductions,
                'total_allowances' => $totalAllowances,
                'total_deductions' => $totalDeductions,
                'net_salary'       => max(0, $netSalary),
                'status'           => 'draft',
                'generated_by'     => auth()->id(),
            ]);

            $created++;
        }

        return ['created' => $created, 'skipped' => $skipped];
    }
}
```

---

## Acceptance Criteria

- [ ] Generate slip tidak membuat duplikat untuk bulan yang sama
- [ ] Snapshot allowances/deductions disimpan di slip (tidak berubah kalau struktur diubah)
- [ ] Net salary = basic + allowances - deductions (tidak boleh negatif)
- [ ] Staff hanya bisa melihat slip gajinya sendiri
- [ ] Admin dan accountant bisa mark-paid dan download slip PDF

## Tests to Write

```
tests/Feature/Payroll/
  PayrollStructureTest.php
  GenerateSlipsTest.php
  DuplicateSlipTest.php
  NetSalaryCalculationTest.php
  StaffViewOwnSlipTest.php
  MarkPaidTest.php
```
