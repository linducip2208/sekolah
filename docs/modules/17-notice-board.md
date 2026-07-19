# Module 17 — Notice Board & Announcements

## Depends On
Module 02 (auth — all roles must exist), Module 04 (academic structure — for class-targeted notices)

## What to Build
Papan pengumuman digital. Admin/teacher/receptionist buat pengumuman.
Bisa ditarget ke semua, per role, atau per kelas. Lampiran file & gambar.
Semua role bisa membaca notice yang sesuai target mereka.

---

## Database Schema

```php
// notices table
Schema::create('notices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
    $table->string('title');
    $table->longText('description');
    $table->string('attachment')->nullable();       // S3 path file/gambar
    $table->string('attachment_type')->nullable();  // image | pdf | doc
    $table->json('target_roles')->nullable();       // null = semua role
    // ["student","parent"] atau null untuk semua
    $table->json('target_class_sections')->nullable(); // null = semua kelas
    // [5, 8, 12] atau null untuk semua
    $table->datetime('publish_at')->nullable();     // null = langsung publish
    $table->datetime('expire_at')->nullable();      // null = tidak expired
    $table->boolean('is_published')->default(true);
    $table->boolean('send_notification')->default(true); // push FCM?
    $table->timestamps();
    $table->softDeletes();
    $table->index(['school_id', 'is_published', 'publish_at']);
    $table->index(['school_id', 'created_by']);
});

// notice_reads table (tracking siapa yang sudah baca)
Schema::create('notice_reads', function (Blueprint $table) {
    $table->id();
    $table->foreignId('notice_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->timestamp('read_at');
    $table->unique(['notice_id', 'user_id']);
    $table->index('notice_id');
});
```

---

## API Endpoints

| Method | URI                                        | Role                        | Deskripsi                          |
|--------|--------------------------------------------|-----------------------------|-------------------------------------|
| GET    | `/api/v1/notices`                          | all                         | List notice yang relevan (filtered) |
| POST   | `/api/v1/notices`                          | admin, teacher, recept      | Buat notice baru                    |
| GET    | `/api/v1/notices/{id}`                     | all                         | Detail notice                       |
| PUT    | `/api/v1/notices/{id}`                     | admin, teacher, recept      | Edit notice (hanya milik sendiri)   |
| DELETE | `/api/v1/notices/{id}`                     | admin, teacher, recept      | Hapus notice                        |
| POST   | `/api/v1/notices/{id}/read`                | all                         | Tandai sudah dibaca                 |
| GET    | `/api/v1/notices/{id}/read-by`             | admin                       | Siapa saja yang sudah baca          |

---

## NoticeService — Filtered List

```php
// Modules/Communication/Services/NoticeService.php
class NoticeService
{
    public function getForUser(User $user): Builder
    {
        $query = Notice::where('school_id', $user->school_id)
            ->where('is_published', true)
            ->where(fn($q) => $q->whereNull('publish_at')->orWhere('publish_at', '<=', now()))
            ->where(fn($q) => $q->whereNull('expire_at')->orWhere('expire_at', '>=', now()))
            ->latest('publish_at');

        // Filter by target_roles
        $query->where(function ($q) use ($user) {
            $q->whereNull('target_roles')
              ->orWhereJsonContains('target_roles', $user->roles->first()->slug);
        });

        // Filter by target_class_sections (untuk student/parent)
        if ($user->hasRole('student')) {
            $student = $user->student;
            $query->where(function ($q) use ($student) {
                $q->whereNull('target_class_sections')
                  ->orWhereJsonContains('target_class_sections', $student->class_section_id);
            });
        }

        if ($user->hasRole('parent')) {
            $childClassSectionIds = $user->students->pluck('class_section_id')->toArray();
            $query->where(function ($q) use ($childClassSectionIds) {
                $q->whereNull('target_class_sections')
                  ->orWhere(function ($q2) use ($childClassSectionIds) {
                      foreach ($childClassSectionIds as $id) {
                          $q2->orWhereJsonContains('target_class_sections', $id);
                      }
                  });
            });
        }

        return $query;
    }
}
```

---

## Notice Response Contract

```json
GET /api/v1/notices
{
  "data": [
    {
      "id": 45,
      "title": "Libur Nasional Hari Kemerdekaan",
      "description": "Diberitahukan bahwa pada tanggal 17 Agustus...",
      "attachment_url": "https://s3.../notices/attachment.pdf",
      "attachment_type": "pdf",
      "target_roles": null,
      "target_classes": null,
      "created_by": { "name": "Admin SMKN 1", "role": "admin" },
      "publish_at": "2025-08-01T08:00:00",
      "expire_at": "2025-08-18T00:00:00",
      "is_read": false,
      "read_count": 127,
      "created_at": "2025-07-30T10:00:00"
    }
  ],
  "meta": { "total": 15, "unread": 3 }
}
```

---

## Acceptance Criteria

- [ ] Notice yang ditarget ke role/kelas tertentu tidak terlihat oleh role/kelas lain
- [ ] Notice terjadwal hanya muncul setelah `publish_at`
- [ ] Notice expired tidak muncul di list
- [ ] FCM dikirim saat notice dipublish (jika `send_notification = true`)
- [ ] Tracking `read_by` mencatat siapa dan kapan membaca

## Tests to Write

```
tests/Feature/Notice/
  CreateNoticeTest.php
  TargetRoleFilterTest.php
  TargetClassFilterTest.php
  ScheduledNoticeTest.php
  ExpiredNoticeTest.php
  MarkAsReadTest.php
  ReadByTrackingTest.php
```
