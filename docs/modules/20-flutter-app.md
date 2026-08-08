# Module 20 — Flutter Mobile App

## Depends On
Semua modul API (01–19) harus complete sebelum Flutter dieksekusi penuh.

## What to Build
Aplikasi Flutter untuk iOS dan Android. 6 shell berbeda per role.
Fitur: dashboard, attendance, timetable, classroom, exam, marks, fees, library,
hostel, transport, notice, chat, profile, notifikasi FCM.

---

## Arsitektur Flutter

```
Pattern: Feature-First + Clean Architecture
State Management: flutter_bloc (BLoC pattern)
Navigation: GoRouter (declarative routing)
HTTP: Dio + dio_interceptors
Auth Storage: flutter_secure_storage
UI: Material 3 + custom theme
Push: firebase_messaging
Real-time: pusher_channels_flutter (WebSocket)
Image: cached_network_image
PDF: flutter_pdfview
Scanner: mobile_scanner (barcode)
Charts: fl_chart
File Pick: file_picker
```

---

## pubspec.yaml Dependencies

```yaml
dependencies:
  flutter:
    sdk: flutter
  # State Management
  flutter_bloc: ^8.1.3
  equatable: ^2.0.5
  # Navigation
  go_router: ^13.0.0
  # HTTP
  dio: ^5.4.0
  pretty_dio_logger: ^1.3.1
  # Storage
  flutter_secure_storage: ^9.0.0
  shared_preferences: ^2.2.2
  # Firebase
  firebase_core: ^2.27.0
  firebase_messaging: ^14.7.9
  # Real-time Chat
  pusher_channels_flutter: ^2.0.2
  # UI
  cached_network_image: ^3.3.1
  shimmer: ^3.0.0
  fl_chart: ^0.66.2
  # Files
  file_picker: ^8.0.3
  flutter_pdfview: ^1.3.2
  image_picker: ^1.0.7
  # Scanner
  mobile_scanner: ^3.5.5
  # Utilities
  intl: ^0.19.0
  timeago: ^3.6.1
  url_launcher: ^6.2.5
```

---

## Folder Structure

```
lib/
├── main.dart
├── firebase_options.dart
├── app/
│   ├── app.dart                        ← MaterialApp + GoRouter setup
│   ├── router/
│   │   ├── app_router.dart
│   │   └── routes.dart
│   └── theme/
│       ├── app_colors.dart
│       ├── app_theme.dart
│       └── app_typography.dart
├── core/
│   ├── api/
│   │   ├── api_client.dart             ← Dio singleton
│   │   ├── api_endpoints.dart          ← semua URL constants
│   │   └── interceptors/
│   │       ├── auth_interceptor.dart   ← inject Bearer token
│   │       ├── error_interceptor.dart  ← handle 401, 403, 422
│   │       └── logging_interceptor.dart
│   ├── error/
│   │   ├── app_exception.dart
│   │   └── error_handler.dart
│   ├── storage/
│   │   └── app_storage.dart            ← secure storage wrapper
│   ├── notifications/
│   │   ├── fcm_service.dart
│   │   └── notification_handler.dart   ← deeplink routing
│   ├── websocket/
│   │   └── pusher_service.dart
│   └── utils/
│       ├── currency_formatter.dart
│       ├── date_formatter.dart
│       └── validators.dart
├── features/
│   ├── auth/
│   │   ├── data/
│   │   │   ├── models/
│   │   │   │   ├── user_model.dart
│   │   │   │   └── school_model.dart
│   │   │   └── auth_repository.dart
│   │   ├── domain/
│   │   │   └── auth_use_case.dart
│   │   └── presentation/
│   │       ├── bloc/
│   │       │   ├── auth_bloc.dart
│   │       │   ├── auth_event.dart
│   │       │   └── auth_state.dart
│   │       └── pages/
│   │           ├── login_page.dart
│   │           └── forgot_password_page.dart
│   ├── dashboard/
│   │   ├── data/dashboard_repository.dart
│   │   └── presentation/
│   │       ├── bloc/dashboard_bloc.dart
│   │       └── pages/
│   │           ├── student_dashboard.dart
│   │           ├── teacher_dashboard.dart
│   │           ├── parent_dashboard.dart
│   │           └── admin_dashboard.dart
│   ├── attendance/
│   ├── timetable/
│   ├── classroom/
│   ├── exam/
│   ├── marks/
│   ├── fees/
│   ├── library/
│   ├── hostel/
│   ├── transport/
│   ├── notice/
│   ├── chat/
│   ├── notifications/
│   └── profile/
└── shells/
    ├── student_shell.dart
    ├── parent_shell.dart
    ├── teacher_shell.dart
    ├── admin_shell.dart
    └── staff_shell.dart
```

---

## Role-Based Navigation (GoRouter)

```dart
// app/router/app_router.dart
final appRouter = GoRouter(
  initialLocation: '/login',
  redirect: (context, state) {
    final authState = context.read<AuthBloc>().state;
    if (authState is AuthAuthenticated) {
      if (state.matchedLocation == '/login') {
        return _homeRouteForRole(authState.user.role);
      }
    }
    if (authState is! AuthAuthenticated && state.matchedLocation != '/login') {
      return '/login';
    }
    return null;
  },
  routes: [
    GoRoute(path: '/login', builder: (_, __) => const LoginPage()),
    ShellRoute(
      builder: (context, state, child) => _shellForRole(context, child),
      routes: [
        // Student routes
        GoRoute(path: '/student/dashboard', builder: (_, __) => const StudentDashboard()),
        GoRoute(path: '/student/timetable', builder: (_, __) => const TimetablePage()),
        GoRoute(path: '/student/attendance', builder: (_, __) => const StudentAttendancePage()),
        // ... semua route per role
      ],
    ),
  ],
);

Widget _shellForRole(BuildContext context, Widget child) {
  final role = context.read<AuthBloc>().state.user?.role;
  return switch (role) {
    'student'     => StudentShell(child: child),
    'parent'      => ParentShell(child: child),
    'teacher'     => TeacherShell(child: child),
    'admin'       => AdminShell(child: child),
    _             => StaffShell(child: child, role: role ?? ''),
  };
}
```

---

## Student Shell Navigation

```dart
// shells/student_shell.dart
class StudentShell extends StatelessWidget {
  static const _tabs = [
    NavigationItem(icon: Icons.home_outlined, activeIcon: Icons.home, label: 'Home', route: '/student/dashboard'),
    NavigationItem(icon: Icons.schedule_outlined, activeIcon: Icons.schedule, label: 'Jadwal', route: '/student/timetable'),
    NavigationItem(icon: Icons.book_outlined, activeIcon: Icons.book, label: 'Kelas', route: '/student/classroom'),
    NavigationItem(icon: Icons.chat_outlined, activeIcon: Icons.chat, label: 'Chat', route: '/student/chat'),
    NavigationItem(icon: Icons.person_outlined, activeIcon: Icons.person, label: 'Profil', route: '/student/profile'),
  ];
}
```

---

## Teacher Shell Navigation

```dart
// shells/teacher_shell.dart
// Tabs: Beranda | Absensi | Kelas | Ujian | Chat | Profil
```

---

## Parent Shell Navigation

```dart
// shells/parent_shell.dart
// Tabs: Beranda | Nilai | Kehadiran | Tagihan | Chat | Profil
// Child switcher: jika parent punya > 1 anak
```

---

## Dashboard Widgets

### Student Dashboard
```
┌──────────────────────────────────┐
│  Selamat datang, Budi 👋         │
│  Kelas 10A • 2024/2025           │
├──────────────────────────────────┤
│  [Tugas Mendatang]   [Ujian]     │
│  3 tugas belum       Besok 09:00 │
│  dikumpulkan         Matematika  │
├──────────────────────────────────┤
│  Jadwal Hari Ini                 │
│  07:00 Matematika — Pak Budi     │
│  08:30 Bahasa Indo — Bu Sari     │
│  ...                             │
├──────────────────────────────────┤
│  Kehadiran Bulan Ini: 95%        │
│  [████████████░░] 19/20 hari     │
└──────────────────────────────────┘
```

### Teacher Dashboard
```
┌──────────────────────────────────┐
│  Halo, Pak Budi 👋               │
├──────────────────────────────────┤
│  [Kelas Hari Ini: 3]             │
│  07:00 Kelas 10A — Matematika    │
│  09:00 Kelas 10B — Matematika    │
│  13:00 Kelas 11A — Fisika        │
├──────────────────────────────────┤
│  Belum Absen:  Kelas 10B (09:00) │
│  [Tandai Absen Sekarang →]       │
├──────────────────────────────────┤
│  Submission Menunggu Dinilai: 12 │
└──────────────────────────────────┘
```

---

## FCM Notification Handler (Flutter)

```dart
// core/notifications/notification_handler.dart
class NotificationHandler {
  static void handleMessage(RemoteMessage message) {
    final type        = message.data['type'];
    final router      = AppRouter.instance;

    switch (type) {
      case 'attendance':
        router.push('/student/attendance/${message.data['student_id']}');
      case 'fee':
        router.push('/fees/invoice/${message.data['invoice_id']}');
      case 'assignment':
        router.push('/classroom/assignment/${message.data['assignment_id']}');
      case 'exam':
        router.push('/exam/${message.data['exam_id']}');
      case 'chat':
        router.push('/chat/${message.data['conversation_id']}');
      case 'notice':
        router.push('/notice/${message.data['notice_id']}');
    }
  }
}
```

---

## Multi-Language Support

```dart
// Mendukung: id (Bahasa Indonesia), en (English), ar (Arabic RTL)
// Implementasi: intl package + ARB files
//
// lib/l10n/
//   app_id.arb   ← Bahasa Indonesia
//   app_en.arb   ← English
//   app_ar.arb   ← Arabic (RTL)
//
// MaterialApp(
//   locale: Locale(user.locale),
//   supportedLocales: [Locale('id'), Locale('en'), Locale('ar')],
//   localizationsDelegates: AppLocalizations.localizationsDelegates,
// )
```

---

## Acceptance Criteria

- [ ] Login mengidentifikasi role dan route ke shell yang benar
- [ ] FCM bekerja di foreground dan background
- [ ] Deep link dari notifikasi membuka halaman yang benar
- [ ] Chat real-time via Pusher (message tanpa refresh)
- [ ] Dashboard menampilkan data relevan per role
- [ ] Support multi-bahasa (id/en minimal)
- [ ] Barcode scanner untuk librarian (module 14)
- [ ] Offline fallback: pesan error yang jelas saat tidak ada koneksi

## Flutter Build Commands

```bash
# Dev
flutter run --dart-define=API_BASE_URL=http://localhost:8000/api/v1

# Production
flutter build apk --release --dart-define=API_BASE_URL=https://api.sikadpro.app/api/v1
flutter build ipa --release --dart-define=API_BASE_URL=https://api.sikadpro.app/api/v1
```
