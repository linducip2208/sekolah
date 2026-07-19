# Module 19 — Notifications System

## Depends On
Module 02 (auth — fcm_token on users), semua modul sebelumnya (notifikasi dari event mereka)

## What to Build
Sistem notifikasi terpusat: in-app inbox, Firebase FCM push, email SMTP.
Semua modul mengirim notifikasi via NotificationService. User punya inbox in-app.

---

## Database Schema

```php
// notifications table (in-app inbox)
Schema::create('notifications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();  // penerima
    $table->foreignId('sent_by')->nullable()->constrained('users');  // pengirim
    $table->string('title');
    $table->text('body');
    $table->string('type');                     // attendance|fee|assignment|exam|notice|chat|system
    $table->json('data')->nullable();           // payload tambahan (deeplink info)
    $table->boolean('is_read')->default(false);
    $table->timestamp('read_at')->nullable();
    $table->timestamps();
    $table->index(['school_id', 'user_id', 'is_read']);
    $table->index(['school_id', 'user_id', 'created_at']);
});
```

---

## API Endpoints

| Method | URI                                         | Role | Deskripsi                           |
|--------|---------------------------------------------|------|-------------------------------------|
| GET    | `/api/v1/notifications`                     | all  | List notifikasi user yg login       |
| GET    | `/api/v1/notifications/unread-count`        | all  | Jumlah notifikasi belum dibaca      |
| PUT    | `/api/v1/notifications/{id}/read`           | all  | Tandai satu notifikasi dibaca       |
| PUT    | `/api/v1/notifications/read-all`            | all  | Tandai semua dibaca                 |
| DELETE | `/api/v1/notifications/{id}`                | all  | Hapus satu notifikasi               |

---

## NotificationService

```php
// app/Services/NotificationService.php
class NotificationService
{
    public function send(
        int $userId,
        string $title,
        string $body,
        string $type,
        array $data = [],
        bool $pushFcm = true,
        bool $sendEmail = false,
    ): void {
        $user = User::find($userId);
        if (!$user) return;

        // 1. Simpan ke in-app inbox
        Notification::create([
            'school_id' => $user->school_id,
            'user_id'   => $userId,
            'title'     => $title,
            'body'      => $body,
            'type'      => $type,
            'data'      => $data,
        ]);

        // 2. FCM push (jika ada token)
        if ($pushFcm && $user->fcm_token) {
            SendFcmJob::dispatch($user->fcm_token, $title, $body, $data);
        }

        // 3. Email (jika diminta)
        if ($sendEmail && $user->email) {
            Mail::to($user->email)->queue(new GenericNotificationMail($title, $body));
        }
    }

    public function sendToRole(int $schoolId, string $roleSlug, string $title, string $body, string $type, array $data = []): void
    {
        User::where('school_id', $schoolId)
            ->whereHas('roles', fn($q) => $q->where('slug', $roleSlug))
            ->where('is_active', true)
            ->select('id')
            ->chunkById(100, function ($users) use ($title, $body, $type, $data) {
                foreach ($users as $user) {
                    $this->send($user->id, $title, $body, $type, $data);
                }
            });
    }

    public function sendToClassSection(int $classSectionId, string $targetRole, string $title, string $body, string $type, array $data = []): void
    {
        if ($targetRole === 'student') {
            $userIds = Student::where('class_section_id', $classSectionId)
                ->pluck('user_id');
        } elseif ($targetRole === 'parent') {
            $studentIds = Student::where('class_section_id', $classSectionId)->pluck('id');
            $userIds = DB::table('parent_student')
                ->whereIn('student_id', $studentIds)
                ->pluck('parent_id');
        } else {
            return;
        }

        foreach ($userIds as $userId) {
            $this->send($userId, $title, $body, $type, $data);
        }
    }
}
```

---

## Firebase FCM Job

```php
// app/Jobs/SendFcmJob.php
class SendFcmJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $fcmToken,
        private string $title,
        private string $body,
        private array  $data = [],
    ) {}

    public function handle(): void
    {
        $url     = 'https://fcm.googleapis.com/fcm/send';
        $headers = [
            'Authorization' => 'key=' . config('services.firebase.server_key'),
            'Content-Type'  => 'application/json',
        ];

        Http::withHeaders($headers)->post($url, [
            'to'           => $this->fcmToken,
            'notification' => [
                'title' => $this->title,
                'body'  => $this->body,
                'sound' => 'default',
            ],
            'data'         => $this->data,
        ]);
    }
}
```

---

## Notification Types & Deeplink Data

| Type          | Data Payload                                | Deeplink Flutter             |
|---------------|---------------------------------------------|------------------------------|
| `attendance`  | `{student_id, date, status}`                | `/attendance/student/{id}`   |
| `fee`         | `{invoice_id, amount}`                      | `/fees/invoice/{id}`         |
| `assignment`  | `{assignment_id, subject}`                  | `/classroom/assignment/{id}` |
| `exam`        | `{exam_id, title, start_time}`              | `/exam/{id}`                 |
| `marks`       | `{report_card_id}`                          | `/marks/report-card/{id}`    |
| `notice`      | `{notice_id}`                               | `/notice/{id}`               |
| `chat`        | `{conversation_id, sender_name}`            | `/chat/{conversation_id}`    |
| `library`     | `{issue_id, due_date}`                      | `/library/issue/{id}`        |
| `system`      | `{}`                                        | `/dashboard`                 |

---

## Acceptance Criteria

- [ ] Setiap event (absen, nilai, tugas, bayaran) memicu notifikasi in-app
- [ ] FCM dikirim via queue (non-blocking)
- [ ] Unread count akurat dan update setelah mark-as-read
- [ ] User hanya bisa lihat notifikasi miliknya sendiri (SchoolScope + user_id filter)
- [ ] Notification chunk-by (100) agar tidak OOM saat kirim massal

## Tests to Write

```
tests/Feature/Notification/
  InAppInboxTest.php
  MarkAsReadTest.php
  UnreadCountTest.php
  FcmJobTest.php         (uses Http::fake())
  BroadcastToClassTest.php
  CrossUserIsolationTest.php
```
