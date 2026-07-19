# Multi-Tenancy Architecture

## Strategy: Shared Database, School-Scoped Rows

Every table that belongs to a school has a `school_id` foreign key.
The `SchoolScope` global scope is applied on every school-owned model.
This is the **single most important rule** in the entire codebase.

---

## SchoolScope Global Scope

```php
// app/Models/Scopes/SchoolScope.php
namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class SchoolScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->check() && auth()->user()->school_id) {
            $builder->where($model->getTable() . '.school_id', auth()->user()->school_id);
        }
    }
}
```

## Base School Model

```php
// app/Models/SchoolModel.php  ← ALL school-owned models extend this
namespace App\Models;

use App\Models\Scopes\SchoolScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class SchoolModel extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::addGlobalScope(new SchoolScope());

        static::creating(function (Model $model) {
            if (auth()->check() && !$model->school_id) {
                $model->school_id = auth()->user()->school_id;
            }
        });
    }
}
```

## Usage

```php
// CORRECT — school_id auto-injected by SchoolScope
$students = Student::all();

// CORRECT — super_admin bypass with withoutGlobalScope
$students = Student::withoutGlobalScope(SchoolScope::class)
    ->where('school_id', $targetSchoolId)
    ->get();

// WRONG — never do this
$students = Student::where('school_id', $request->school_id)->get();
// ↑ This bypasses the scope contract and is a security risk
```

---

## School Subdomain Routing

Each school gets a subdomain: `smkn1.eschool.app`

```php
// routes/web.php
Route::domain('{school}.eschool.app')->group(function () {
    Route::middleware(['school.resolve'])->group(function () {
        // all school web routes
    });
});
```

```php
// app/Http/Middleware/ResolveSchool.php
public function handle(Request $request, Closure $next): Response
{
    $school = School::where('subdomain', $request->route('school'))
        ->where('is_active', true)
        ->firstOrFail();

    app()->instance('current_school', $school);
    config(['app.school_id' => $school->id]);

    return $next($request);
}
```

---

## Schools Table (MySQL)

```sql
CREATE TABLE schools (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(255) NOT NULL,
    subdomain       VARCHAR(100) NOT NULL UNIQUE,
    logo            VARCHAR(500),
    address         TEXT,
    phone           VARCHAR(30),
    email           VARCHAR(255),
    timezone        VARCHAR(50) DEFAULT 'Asia/Jakarta',
    locale          VARCHAR(10) DEFAULT 'id',
    is_active       TINYINT(1) DEFAULT 1,
    plan_id         BIGINT UNSIGNED,
    plan_expires_at TIMESTAMP NULL,
    settings        JSON,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,
    deleted_at      TIMESTAMP NULL,
    CONSTRAINT fk_schools_plan FOREIGN KEY (plan_id) REFERENCES plans(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Super Admin vs School Admin

| | Super Admin | School Admin |
|---|---|---|
| `school_id` | NULL | required |
| Can see all schools | ✓ | ✗ |
| Panel URL | `admin.eschool.app` | `{school}.eschool.app/admin` |
| SchoolScope applied | ✗ (bypassed) | ✓ |

---

## Tenant Resolution Flow (API)

```
Mobile App → POST /api/v1/auth/login
  1. User login dengan email + password
  2. AuthService cari user berdasarkan email (no SchoolScope — public endpoint)
  3. User model punya school_id
  4. Return token + user.school_id + school info
  5. Semua request berikutnya: token → user → school_id → SchoolScope aktif otomatis
```

---

## Multi-Tenant Settings (per school)

Pengaturan per sekolah disimpan di `schools.settings` (JSON column MySQL):

```json
{
  "currency": "IDR",
  "currency_symbol": "Rp",
  "date_format": "d/m/Y",
  "time_format": "H:i",
  "working_days": ["Mon", "Tue", "Wed", "Thu", "Fri"],
  "school_start_time": "07:00",
  "school_end_time": "15:00",
  "attendance_type": "daily",
  "grading_system": "percentage",
  "library": {
    "max_books_per_member": 3,
    "default_issue_days": 14,
    "fine_per_day_cents": 500,
    "allow_staff_borrow": true
  }
}
```

Query JSON column di MySQL:
```php
// Ambil satu field
School::where('id', $id)->value('settings->currency'); // "IDR"

// Filter berdasarkan JSON field
School::whereJsonContains('settings->working_days', 'Sat')->get();
```
