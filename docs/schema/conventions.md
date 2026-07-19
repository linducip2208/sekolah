# Database Conventions — MySQL 8

## Database Engine

**Wajib: MySQL 8.0+**. Seluruh project menggunakan MySQL — bukan SQLite.
Alasannya: JSON columns, fulltext index, dan `upsert()` bergantung pada MySQL behavior.

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eschool_saas
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

---

## Naming Rules

- Tables: `snake_case`, plural → `students`, `fee_invoices`, `class_rooms`
- Columns: `snake_case` → `first_name`, `created_at`, `school_id`
- Foreign keys: `{table_singular}_id` → `student_id`, `class_id`
- Pivot tables: alphabetical order → `role_user`, `student_subject`
- Booleans: `is_` atau `has_` prefix → `is_active`, `has_transport`
- Timestamps: selalu `created_at`, `updated_at`, `deleted_at` (soft delete)

---

## Money / Amounts

**Selalu simpan sebagai integer (unit terkecil: sen/paise/cents).**

```php
// Migration
$table->unsignedInteger('amount'); // stored as sen/paise/cents

// Model cast
protected $casts = [
    'amount' => 'integer',
];

// Accessor untuk tampilan
public function getAmountFormattedAttribute(): string
{
    return 'Rp ' . number_format($this->amount / 100, 0, ',', '.');
}

// Contoh: Rp 150.000 disimpan sebagai 15000000
// Contoh: Rp 99.000 disimpan sebagai 9900000
```

---

## Standard Columns on Every Table

```php
$table->id();
$table->foreignId('school_id')->constrained()->cascadeOnDelete(); // KECUALI: schools, roles, plans
$table->timestamps();
$table->softDeletes();
```

---

## JSON Columns (MySQL JSON type)

```php
// OK — config dinamis
$table->json('settings')->nullable();  // {"theme": "blue", "currency": "IDR"}
$table->json('features')->nullable();  // ["attendance", "library"]
$table->json('allowances')->nullable(); // [{id, title, amount}]

// TIDAK OK — harusnya relasi proper
$table->json('subject_ids');  // ← salah, gunakan pivot table
```

---

## Indexes

```php
// Selalu index kombinasi foreign key + school_id
$table->index(['school_id', 'student_id']);
$table->index(['school_id', 'created_at']);  // untuk range queries

// Unique constraints berbasis school
$table->unique(['school_id', 'admission_number']);
$table->unique(['school_id', 'email']);       // email unique per sekolah
$table->unique(['school_id', 'subdomain']);   // untuk tabel schools: cukup unique('subdomain')
```

---

## Character Set

```php
// Semua tabel: utf8mb4 (support emoji dan karakter khusus)
// Di config/database.php:
'charset'   => 'utf8mb4',
'collation' => 'utf8mb4_unicode_ci',
```

---

## Migrations Order Reference

```
0001_create_plans_table
0002_create_schools_table
0003_create_users_table           ← replace default Laravel users migration
0004_create_spatie_permission_tables
0005_create_personal_access_tokens_table
...
00XX_create_{module}_tables       ← satu migration file per modul
```

---

## Seeder Structure

```
DatabaseSeeder
  ├── PlanSeeder              (free, basic, pro plans)
  ├── RolePermissionSeeder    (7 roles + semua permissions dari rbac.md)
  └── DemoSchoolSeeder        (data demo, HANYA di non-production)
```

---

## Migration Best Practices

```php
// 1. Selalu ada down() yang reversible
public function down(): void
{
    Schema::dropIfExists('attendances');
}

// 2. cascadeOnDelete untuk foreign key ke schools
$table->foreignId('school_id')->constrained()->cascadeOnDelete();

// 3. nullOnDelete untuk optional foreign key
$table->foreignId('class_teacher_id')->nullable()->constrained('users')->nullOnDelete();

// 4. Gunakan unsignedInteger untuk amount (tidak perlu signed)
$table->unsignedInteger('amount')->default(0);

// 5. Enum untuk status field yang terbatas nilainya
$table->enum('status', ['issued', 'returned', 'overdue', 'lost'])->default('issued');

// 6. JSON untuk config dinamis
$table->json('settings')->nullable();

// 7. virtualAs untuk kolom computed (MySQL specific)
$table->decimal('percentage', 5, 2)->virtualAs('(obtained_marks / total_marks) * 100');
```

---

## Upsert (MySQL feature — dipakai di Attendance)

```php
// Bulk insert or update on duplicate key
Attendance::upsert(
    $records,                                      // data array
    ['school_id', 'student_id', 'date'],           // unique keys
    ['status', 'note', 'marked_by', 'updated_at']  // update these on conflict
);
```

---

## Soft Deletes

```php
// Semua model WAJIB pakai SoftDeletes
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends SchoolModel
{
    use SoftDeletes;  // sudah include di SchoolModel, tapi eksplisit lebih jelas
}

// Query default: WHERE deleted_at IS NULL (otomatis)
// Termasuk soft deleted:
Student::withTrashed()->find($id);

// Hanya soft deleted:
Student::onlyTrashed()->get();

// Restore:
Student::withTrashed()->find($id)->restore();
```
