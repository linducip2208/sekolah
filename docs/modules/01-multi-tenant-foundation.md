# Module 01 — Multi-Tenant Foundation

## Depends On
Nothing. This is executed first (after module 00 license).

## What to Build
Laravel project scaffold dengan multi-tenancy infrastructure.
Tidak ada fitur domain — hanya fondasi yang dibutuhkan semua modul lain.

---

## Checklist

### 1. Laravel Installation

```bash
composer create-project laravel/laravel sikadpro
cd sikadpro
composer require nwidart/laravel-modules
composer require spatie/laravel-permission
composer require spatie/laravel-activitylog
php artisan vendor:publish --provider="Nwidart\Modules\LaravelModulesServiceProvider"
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

### 2. Files to Create

**`app/Models/Scopes/SchoolScope.php`**
→ Kode persis dari `docs/schema/multi-tenant.md`

**`app/Models/SchoolModel.php`**
→ Kode persis dari `docs/schema/multi-tenant.md`

**`app/Http/Middleware/EnsureSchoolAccess.php`**
→ Kode persis dari `docs/roles/rbac.md`

**`app/Http/Middleware/ResolveSchool.php`**
→ Kode persis dari `docs/schema/multi-tenant.md`

**`app/Http/Middleware/EnsureValidLicense.php`**
→ Kode persis dari `docs/modules/00-license.md`

**`app/Services/LicenseChecker.php`**
→ Kode persis dari `docs/modules/00-license.md`

### 3. Migrations (MySQL, urutan wajib)

```php
// 0001_create_plans_table
Schema::create('plans', function (Blueprint $table) {
    $table->id();
    $table->string('name');           // 'Free', 'Basic', 'Pro'
    $table->string('slug')->unique(); // 'free', 'basic', 'pro'
    $table->unsignedInteger('price'); // monthly price in cents (integer!)
    $table->unsignedSmallInteger('max_students')->default(0); // 0 = unlimited
    $table->unsignedSmallInteger('max_teachers')->default(0);
    $table->json('features');         // ["attendance","library",...]
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

// 0002_create_schools_table
Schema::create('schools', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('subdomain', 100)->unique();
    $table->string('logo')->nullable();
    $table->text('address')->nullable();
    $table->string('phone', 30)->nullable();
    $table->string('email')->nullable();
    $table->string('timezone', 50)->default('Asia/Jakarta');
    $table->string('locale', 10)->default('id');
    $table->boolean('is_active')->default(true);
    $table->foreignId('plan_id')->nullable()->constrained();
    $table->timestamp('plan_expires_at')->nullable();
    $table->json('settings')->nullable();
    $table->timestamps();
    $table->softDeletes();
});

// 0003_create_users_table (replace default Laravel users migration)
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('phone')->nullable();
    $table->string('avatar')->nullable();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->string('fcm_token')->nullable();  // Firebase push token
    $table->string('locale')->default('id');
    $table->boolean('is_active')->default(true);
    $table->rememberToken();
    $table->timestamps();
    $table->softDeletes();
    $table->index(['school_id', 'email']);
});
```

### 4. config/multitenancy.php

```php
return [
    'subdomain_pattern'    => '{school}.sikadpro.app',
    'super_admin_domain'   => 'admin.sikadpro.app',
    'default_timezone'     => 'Asia/Jakarta',
    'default_locale'       => 'id',
    'grace_period_days'    => 7,
];
```

### 5. Seeders

```php
// database/seeders/PlanSeeder.php
Plan::insert([
    [
        'name'         => 'Free',
        'slug'         => 'free',
        'price'        => 0,
        'max_students' => 50,
        'max_teachers' => 5,
        'features'     => json_encode(['attendance', 'notice']),
        'is_active'    => true,
    ],
    [
        'name'         => 'Basic',
        'slug'         => 'basic',
        'price'        => 9900000,  // Rp 99.000 dalam cents
        'max_students' => 500,
        'max_teachers' => 50,
        'features'     => json_encode(['attendance', 'library', 'fee', 'timetable', 'classroom']),
        'is_active'    => true,
    ],
    [
        'name'         => 'Pro',
        'slug'         => 'pro',
        'price'        => 19900000, // Rp 199.000 dalam cents
        'max_students' => 0,        // unlimited
        'max_teachers' => 0,        // unlimited
        'features'     => json_encode(['*']),
        'is_active'    => true,
    ],
]);

// database/seeders/RolePermissionSeeder.php
// Buat semua 7 roles + permissions dari docs/roles/rbac.md
// Gunakan Spatie permission seeder pattern
```

### 6. Route Registration

```php
// routes/api.php
Route::prefix('v1')->middleware(['api', 'license'])->group(function () {
    // Public — tanpa auth
    Route::post('/auth/login',            [AuthController::class, 'login']);
    Route::post('/auth/forgot-password',  [AuthController::class, 'forgotPassword']);
    Route::post('/auth/reset-password',   [AuthController::class, 'resetPassword']);
    Route::post('/admission/forms',       [AdmissionFormController::class, 'store']); // public PPDB

    // License endpoints (tanpa license middleware)
    Route::post('/license/status',        [LicenseController::class, 'status']);
    Route::post('/license/activate',      [LicenseController::class, 'activate']);

    // Authenticated school routes
    Route::middleware(['auth:sanctum', 'school.access'])->group(function () {
        Route::post('/auth/logout',       [AuthController::class, 'logout']);
        Route::get('/auth/me',            [AuthController::class, 'me']);
        Route::put('/auth/profile',       [AuthController::class, 'updateProfile']);
        Route::post('/auth/avatar',       [AuthController::class, 'updateAvatar']);
        Route::post('/auth/fcm-token',    [AuthController::class, 'updateFcmToken']);
        // Module routes registered by each module's RouteServiceProvider
    });

    // Super Admin routes (bypass school scope)
    Route::prefix('super')
        ->middleware(['auth:sanctum', 'role:super_admin'])
        ->group(function () {
            // Module 21 routes
        });
});
```

---

## Acceptance Criteria

- [ ] `php artisan migrate` runs clean dengan MySQL — tidak ada error
- [ ] `php artisan db:seed` membuat plans dan roles dengan benar
- [ ] Request ke API endpoint tanpa token → 401
- [ ] Request dari user sekolah A ke data sekolah B → 404 (SchoolScope)
- [ ] `SchoolScope` otomatis filter semua query by `school_id`
- [ ] License invalid di production → abort 403
- [ ] PHPUnit: `php artisan test --filter=MultiTenancyTest` passes (MySQL)

## Tests to Write

```
tests/Feature/MultiTenancy/
  SchoolScopeTest.php         ← proves data isolation between schools
  CrossSchoolAccessTest.php   ← proves 404 on cross-school access
  SuperAdminBypassTest.php    ← proves super_admin can see all schools
```
