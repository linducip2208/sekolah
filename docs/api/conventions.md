# API Conventions — Sikad Pro

## Base URL

```
Production : https://{school}.sikadpro.app/api/v1
Local dev  : http://localhost:8000/api/v1
```

---

## Authentication

Semua endpoint (kecuali login, forgot-password, admission publik) memerlukan Bearer token.

```http
Authorization: Bearer 1|abc123def456...
Content-Type: application/json
Accept: application/json
```

Token didapat dari `POST /api/v1/auth/login`.

---

## Standard Response Format

### Success

```json
{
  "data": { ... }           // single resource
}

{
  "data": [ ... ],          // collection
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 73
  },
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  }
}

{
  "message": "Attendance marked for 3 students",
  "data": { ... }           // action result
}
```

### Error

```json
// 422 Validation Error
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["Email harus diisi.", "Format email tidak valid."],
    "password": ["Password minimal 8 karakter."]
  }
}

// 401 Unauthorized
{
  "message": "Unauthenticated."
}

// 403 Forbidden
{
  "message": "This action is unauthorized."
}

// 404 Not Found
{
  "message": "No query results for model [Student] 999"
}

// 402 Payment Required (subscription expired)
{
  "message": "Subscription expired. Renew at https://sikadpro.app/billing",
  "expired_at": "2025-06-01T00:00:00Z"
}

// 403 License Invalid
{
  "message": "License tidak valid atau tidak aktif.",
  "activate_url": "https://whitelabel.co.id"
}

// 500 Server Error
{
  "message": "Server Error"
}
```

---

## HTTP Status Codes

| Code | Kapan digunakan |
|------|-----------------|
| 200  | GET, PUT, POST berhasil |
| 201  | POST yang membuat resource baru |
| 204  | DELETE berhasil (no body) |
| 400  | Bad request (malformed) |
| 401  | Token tidak ada / tidak valid |
| 402  | Subscription expired |
| 403  | Tidak punya izin (policy denied) / license invalid |
| 404  | Resource tidak ditemukan (SchoolScope menyembunyikan juga) |
| 422  | Validation error |
| 429  | Rate limit exceeded |
| 500  | Server error |

---

## Pagination

Default 15 item per halaman. Semua endpoint list mendukung:

```
?page=2          → halaman ke-2
?per_page=25     → 25 item per halaman (max: 100)
?search=keyword  → pencarian teks
?sort=name       → sort by field
?direction=desc  → asc | desc
```

---

## Date & Time Format

```
Date       : YYYY-MM-DD              (2025-07-14)
DateTime   : YYYY-MM-DDTHH:MM:SS    (2025-07-14T09:00:00)
Timestamps : ISO 8601 dengan timezone
```

Semua timestamp menggunakan timezone sekolah (dari `school.settings.timezone`).

---

## File Upload

Upload file menggunakan `multipart/form-data`:

```http
POST /api/v1/classroom/materials
Content-Type: multipart/form-data

file: [binary]
title: "Materi Bab 1"
class_section_id: 5
subject_id: 3
```

Batas ukuran file: **50MB** per upload.
File disimpan di S3. Response selalu berikan `*_url` (signed URL atau public URL).

---

## Filter & Search Conventions

```
GET /api/v1/students?class_section_id=5&search=budi
GET /api/v1/fee/invoices?status=unpaid&month=2025-07
GET /api/v1/attendance/report?from_date=2025-07-01&to_date=2025-07-31
GET /api/v1/exams?semester_id=3&subject_id=2&status=published
```

---

## API Resource Shape (contoh Student)

```json
{
  "id": 45,
  "admission_no": "ADM-2024-0045",
  "roll_number": "10A-012",
  "name": "Budi Santoso",
  "email": "budi.s@smkn1.sch.id",
  "avatar_url": "https://s3.../avatars/45.jpg",
  "gender": "male",
  "date_of_birth": "2008-03-15",
  "class": {
    "id": 5,
    "name": "Kelas 10",
    "section": "A"
  },
  "guardian": {
    "name": "Santoso",
    "phone": "08123456789",
    "relation": "father"
  },
  "has_transport": true,
  "has_hostel": false,
  "admission_date": "2024-07-15",
  "created_at": "2024-07-15T08:00:00"
}
```

---

## Rate Limiting

```
Global        : 120 req/menit per user
Auth endpoints: 10 req/menit per IP (brute force protection)
File upload   : 20 req/menit per user
```

Response header saat rate limited:
```
X-RateLimit-Limit: 120
X-RateLimit-Remaining: 0
Retry-After: 45
```

---

## Versioning

API saat ini di versi `v1`. Breaking changes akan memakai prefix `v2`.
Non-breaking changes (tambah field baru) tidak butuh versi baru.

---

## Flutter API Client — Endpoint Constants

```dart
// lib/core/api/api_endpoints.dart
class ApiEndpoints {
  // Auth
  static const login           = '/auth/login';
  static const logout          = '/auth/logout';
  static const me              = '/auth/me';
  static const profile         = '/auth/profile';
  static const fcmToken        = '/auth/fcm-token';

  // Academic
  static const classes         = '/classes';
  static const subjects        = '/subjects';
  static const classSections   = '/class-sections';
  static const myClasses       = '/teacher/my-classes';

  // Attendance
  static String attendanceClass(int id) => '/attendance/class/$id';
  static String attendanceStudent(int id) => '/attendance/student/$id';
  static String attendanceSummary(int id) => '/attendance/summary/$id';

  // Timetable
  static String timetableClass(int id) => '/timetable/class/$id';
  static const timetableMyStudent = '/timetable/student/my';
  static const timetableMy       = '/timetable/my';

  // Classroom
  static const lessons         = '/classroom/lessons';
  static const assignments     = '/classroom/assignments';
  static String assignmentSubmit(int id) => '/classroom/assignments/$id/submit';

  // Exams
  static const exams           = '/exams';
  static const examsUpcoming   = '/exams/upcoming';
  static String examStart(int id) => '/exams/$id/start';
  static String examSubmit(int id) => '/exams/$id/submit';

  // Marks
  static String studentMarks(int id) => '/marks/student/$id';
  static String reportCard(int id) => '/report-cards/student/$id';

  // Fees
  static const feeInvoices     = '/fees/invoices';
  static String studentDues(int id) => '/fees/student/$id/dues';

  // Library
  static const libraryBooks    = '/library/books';
  static const libraryIssue    = '/library/issue';
  static String libraryReturn(int id) => '/library/return/$id';
  static String libraryMember(int id) => '/library/member/$id';

  // Chat
  static const chatConversations = '/chat/conversations';
  static String chatMessages(int id) => '/chat/conversations/$id/messages';
  static const chatUnreadCount = '/chat/unread-count';

  // Notices
  static const notices         = '/notices';

  // Notifications
  static const notifications   = '/notifications';
  static const notifUnread     = '/notifications/unread-count';
  static const notifReadAll    = '/notifications/read-all';
}
```
