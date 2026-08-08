# Sikad Pro — Flutter Mobile App

Aplikasi mobile multi-role (Student, Parent, Teacher, Admin, Staff, Librarian) untuk Sikad Pro.

## Quick Start

```bash
# 1. Install Flutter dependencies
flutter pub get

# 2. Generate platform folders (jika belum ada)
flutter create . --org id.coid.whitelabel.sikadpro --platforms=android,ios

# 3. Setup Firebase (sekali per environment)
dart pub global activate flutterfire_cli
flutterfire configure --project=sikadpro-saas

# 4. Run di emulator
flutter run \
  --dart-define=API_BASE_URL=http://10.0.2.2:8000/api/v1 \
  --dart-define=PUSHER_KEY=local \
  --dart-define=PUSHER_HOST=10.0.2.2 \
  --dart-define=PUSHER_PORT=6001
```

## Build Production

```bash
flutter build apk --release \
  --dart-define=API_BASE_URL=https://api.sikadpro.app/api/v1 \
  --dart-define=PUSHER_KEY=PROD_KEY \
  --dart-define=PUSHER_HOST=ws.sikadpro.app

flutter build ipa --release \
  --dart-define=API_BASE_URL=https://api.sikadpro.app/api/v1
```

## Arsitektur

- **Pattern:** Feature-First + Clean Architecture (data / domain / presentation)
- **State:** flutter_bloc 9.x
- **Routing:** go_router 16.x dengan role-based redirect
- **HTTP:** Dio + auth/error interceptors
- **Storage:** flutter_secure_storage (token), SharedPreferences (settings)
- **Push:** Firebase Messaging + flutter_local_notifications (foreground)
- **Real-time:** Pusher Channels (chat, broadcast)
- **i18n:** Bahasa Indonesia (default), English, Arabic (RTL)

## Folder Structure

```
lib/
  main.dart
  app/                 ← App root, router, theme
  core/                ← API client, storage, FCM, Pusher, utils
  features/            ← Feature modules (auth, dashboard, attendance, ...)
  shells/              ← Role-based bottom nav shells (5 roles)
  l10n/                ← Translations (id, en, ar)
```

## Backend API

Backend Laravel 11 dengan endpoint `/api/v1/*`. Lihat `../docs/api/` untuk reference lengkap.

Auth menggunakan Laravel Sanctum (Bearer token).
