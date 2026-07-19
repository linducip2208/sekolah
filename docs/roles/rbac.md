# RBAC — Role & Permission Matrix

## The 7 Roles

| Role | Scope | Description |
|---|---|---|
| `super_admin` | Platform | Manages all schools, SaaS billing, global config |
| `admin` | School | Full access within their school |
| `teacher` | School | Academic operations, own classes only |
| `student` | School | Read own data, submit assignments/exams |
| `parent` | School | Read their child's data only |
| `receptionist` | School | Admission, hostel, transport front desk |
| `accountant` | School | Fee collection, payroll, financial reports |
| `librarian` | School | Library catalogue, book issue/return |

> Note: `super_admin` is NOT school-scoped. All other roles have `school_id`.

---

## Permission Definitions

Each permission follows the pattern: `module.action`

Actions: `viewAny`, `view`, `create`, `update`, `delete`, `export`

### Academic

| Permission | super_admin | admin | teacher | student | parent | receptionist | accountant | librarian |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| `academic.subjects.*` | ✓ | ✓ | view | — | — | — | — | — |
| `academic.classes.*` | ✓ | ✓ | view | view | view | — | — | — |
| `timetable.*` | ✓ | ✓ | view | view | view | — | — | — |
| `attendance.manage` | ✓ | ✓ | ✓ (own class) | — | — | view | — | — |
| `attendance.view_own` | — | — | — | ✓ | ✓ (child) | — | — | — |
| `classroom.*` | ✓ | ✓ | ✓ (own class) | view+submit | view | — | — | — |
| `exam.manage` | ✓ | ✓ | ✓ (own class) | — | — | — | — | — |
| `exam.take` | — | — | — | ✓ | — | — | — | — |
| `marks.*` | ✓ | ✓ | ✓ (own class) | view | view (child) | — | — | — |

### Finance

| Permission | super_admin | admin | teacher | student | parent | receptionist | accountant | librarian |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| `fee.manage` | ✓ | ✓ | — | view_own | view (child) | — | ✓ | — |
| `fee.collect` | ✓ | ✓ | — | — | — | — | ✓ | — |
| `invoice.view` | ✓ | ✓ | — | view_own | view (child) | — | ✓ | — |
| `payroll.*` | ✓ | ✓ | view_own | — | — | — | ✓ | — |
| `expense.*` | ✓ | ✓ | — | — | — | — | ✓ | — |
| `subscription.*` | ✓ | — | — | — | — | — | — | — |

### Admission

| Permission | super_admin | admin | teacher | student | parent | receptionist | accountant | librarian |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| `admission.manage` | ✓ | ✓ | — | — | — | ✓ | — | — |
| `admission.view` | ✓ | ✓ | — | — | — | ✓ | — | — |

### Facilities

| Permission | super_admin | admin | teacher | student | parent | receptionist | accountant | librarian |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| `library.manage` | ✓ | ✓ | — | — | — | — | — | ✓ |
| `library.issue` | ✓ | ✓ | — | view_own | — | — | — | ✓ |
| `hostel.manage` | ✓ | ✓ | — | view_own | view (child) | ✓ | — | — |
| `transport.manage` | ✓ | ✓ | — | view_own | view (child) | ✓ | — | — |

### Communication

| Permission | super_admin | admin | teacher | student | parent | receptionist | accountant | librarian |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| `notice.manage` | ✓ | ✓ | ✓ | — | — | ✓ | — | — |
| `notice.view` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `chat.*` | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| `notification.send` | ✓ | ✓ | ✓ (own class) | — | — | — | — | — |

---

## Implementation in Laravel

### 1. Roles are stored in DB (not hardcoded enum)

```php
// users table
$table->foreignId('role_id')->constrained();
$table->foreignId('school_id')->nullable()->constrained(); // null for super_admin

// roles table
$table->string('slug'); // 'admin', 'teacher', etc.
$table->string('name');
```

### 2. Permissions via Spatie Laravel-Permission

```bash
composer require spatie/laravel-permission
```

Seed all permissions from this matrix during installation.

### 3. Middleware stack

```php
// routes/api.php
Route::middleware(['auth:sanctum', 'school.access', 'role:admin,teacher'])
    ->group(function () { ... });
```

### 4. Policy example

```php
// app/Policies/AttendancePolicy.php
public function manage(User $user, Attendance $attendance): bool
{
    // Teacher can only manage their own class
    if ($user->hasRole('teacher')) {
        return $attendance->class->teacher_id === $user->id;
    }
    return $user->hasPermissionTo('attendance.manage');
}
```

### 5. The `EnsureSchoolAccess` middleware

```php
// app/Http/Middleware/EnsureSchoolAccess.php
// This middleware MUST be on every school-scoped route
public function handle(Request $request, Closure $next): Response
{
    $user = $request->user();
    if ($user->school_id && $user->school_id !== (int) $request->route('school_id')) {
        abort(403, 'Cross-school access denied.');
    }
    return $next($request);
}
```

---

## Flutter Role Routing

After login, the Flutter app checks `user.role` and navigates to the correct shell:

```dart
switch (user.role) {
  case 'student':  return const StudentShell();
  case 'parent':   return const ParentShell();
  case 'teacher':  return const TeacherShell();
  case 'admin':    return const AdminShell();
  // receptionist, accountant, librarian → StaffShell with filtered nav
  default:         return StaffShell(role: user.role);
}
```
