# Claude Code — Execution Guide

## How to Work With This Project

This folder IS the project spec. Claude Code reads the relevant `.md` file
before writing any code. This ensures consistency, no hallucinated APIs,
and correct implementation every time.

---

## Commands to Use

### Execute a specific module
```
Execute module 01 — multi-tenant foundation
```
Claude Code will:
1. Read `CLAUDE.md`
2. Read `docs/modules/01-multi-tenant-foundation.md`
3. Read any referenced schema docs
4. Write ALL files listed in the module's "Files to Create" section
5. Run migrations
6. Write the tests listed in "Tests to Write"
7. Run `php artisan test` and fix until green

### Execute multiple modules in sequence
```
Execute modules 01 through 05 in order
```

### Execute all remaining modules
```
Execute modules 00 through 21 in order
```

### Fix a specific module
```
Module 05 attendance has a bug: available_quantity goes negative on concurrent requests. Fix it.
```

### Add a feature to existing module
```
Add export-to-CSV feature to module 05 attendance report endpoint
```

### Review before executing
```
What will you build for module 14 — library? List the files and endpoints only, don't write code yet.
```

---

## Module Execution Order (STRICT)

Do NOT skip ahead. Each module depends on previous ones.

```
Phase 0 — License (WAJIB sebelum apapun)
  00 → License protection (whitelabel.co.id)

Phase 1 — Foundation (must be first)
  01 → 02

Phase 2 — Academic (must be before Phase 3+)
  03 → 04 → 05 → 06 → 07 → 08 → 09

Phase 3 — Finance (can parallel with Phase 4)
  10 → 11 → 12 → 13

Phase 4 — Facilities (can parallel with Phase 3)
  14 → 15 → 16

Phase 5 — Communication (after Phase 2)
  17 → 18 → 19

Phase 6 — Mobile Flutter (after all API modules done)
  20

Phase 7 — Super Admin SaaS (after all school modules done)
  21
```

---

## What Claude Code Will Do For Each Module

For every module execution, Claude Code will produce:

1. **Migration files** — exact schema from the module doc
2. **Model** — extends `SchoolModel`, includes casts, relations, scopes
3. **Repository** — all DB queries, no logic
4. **Service** — business logic, calls repository
5. **Controller** — thin, calls service, returns resources
6. **Form Requests** — validation with messages
7. **API Resource** — clean JSON response shape
8. **Policy** — role-based access per the RBAC matrix
9. **Routes** — registered in module's route file
10. **Feature Tests** — every acceptance criterion has a test

---

## What Claude Code Will NOT Do

- Will not write business logic in controllers
- Will not use raw `DB::table()` in controllers (use repositories)
- Will not skip the `school_id` scope on any model
- Will not hardcode permission strings inline (use Policy)
- Will not leave untested endpoints
- Will not store amounts as floats
- Will not ask for confirmation — always proceeds automatically

---

## Quick Reference

| Need to know | Read |
|---|---|
| Full system architecture | `docs/ARCHITECTURE.md` |
| Which roles can do what | `docs/roles/rbac.md` |
| How multi-tenancy works | `docs/schema/multi-tenant.md` |
| DB naming rules | `docs/schema/conventions.md` |
| License API | `LICENSE_API.md` |
| Docker deployment | `docs/deployment/docker.md` |
| Flutter setup | `docs/flutter/setup.md` |
| Testing strategy | `docs/testing/strategy.md` |
| Module 00 — License | `docs/modules/00-license.md` |
| Module 01 — Foundation | `docs/modules/01-multi-tenant-foundation.md` |
| Module 02 — Auth | `docs/modules/02-auth-roles.md` |
| Module 03 — School Setup | `docs/modules/03-school-setup.md` |
| Module 04 — Academic Structure | `docs/modules/04-academic-structure.md` |
| Module 05 — Attendance | `docs/modules/05-attendance.md` |
| Module 06 — Timetable | `docs/modules/06-timetable.md` |
| Module 07 — Online Classroom | `docs/modules/07-online-classroom.md` |
| Module 08 — Exam Engine | `docs/modules/08-exam-engine.md` |
| Module 09 — Marks & Grades | `docs/modules/09-marks-grades.md` |
| Module 10 — Admission | `docs/modules/10-admission.md` |
| Module 11 — Fee & Invoice | `docs/modules/11-fee-invoice.md` |
| Module 12 — Payroll | `docs/modules/12-payroll.md` |
| Module 13 — Subscription | `docs/modules/13-subscription.md` |
| Module 14 — Library | `docs/modules/14-library.md` |
| Module 15 — Hostel | `docs/modules/15-hostel.md` |
| Module 16 — Transport | `docs/modules/16-transport.md` |
| Module 17 — Notice Board | `docs/modules/17-notice-board.md` |
| Module 18 — Chat | `docs/modules/18-chat.md` |
| Module 19 — Notifications | `docs/modules/19-notifications.md` |
| Module 20 — Flutter App | `docs/modules/20-flutter-app.md` |
| Module 21 — SaaS Panel | `docs/modules/21-saas-panel.md` |
