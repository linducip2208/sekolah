# Module 02 — Authentication & Roles

## Depends On
Module 01 (multi-tenant foundation)

## What to Build
Full auth flow untuk semua 7 role. Laravel Sanctum untuk API token.
Login, logout, password reset, profile update, avatar upload, FCM token.

---

## API Endpoints

| Method | URI | Role | Description |
|---|---|---|---|
| POST | `/api/v1/auth/login` | public | Email + password login |
| POST | `/api/v1/auth/logout` | all | Revoke current token |
| GET | `/api/v1/auth/me` | all | Current user profile |
| PUT | `/api/v1/auth/profile` | all | Update name/phone/locale |
| POST | `/api/v1/auth/avatar` | all | Upload avatar (multipart) |
| POST | `/api/v1/auth/fcm-token` | all | Register Firebase token |
| POST | `/api/v1/auth/forgot-password` | public | Send reset link |
| POST | `/api/v1/auth/reset-password` | public | Reset with token |

---

## Login Response Contract

```json
{
  "token": "1|abc...",
  "user": {
    "id": 1,
    "name": "Budi Santoso",
    "email": "budi@smkn1.sch.id",
    "phone": "08123456789",
    "avatar_url": "https://...",
    "role": "teacher",
    "school_id": 3,
    "school": {
      "id": 3,
      "name": "SMKN 1 Jakarta",
      "subdomain": "smkn1-jakarta",
      "logo_url": "https://...",
      "timezone": "Asia/Jakarta",
      "locale": "id"
    },
    "permissions": ["attendance.manage", "classroom.*", "marks.*"]
  }
}
```

---

## Files to Create

```
app/
  Http/
    Controllers/Api/AuthController.php
    Requests/Auth/LoginRequest.php
    Requests/Auth/UpdateProfileRequest.php
    Requests/Auth/AvatarUploadRequest.php
    Resources/UserResource.php
    Resources/SchoolResource.php
  Services/AuthService.php
```

---

## AuthController Implementation

```php
// app/Http/Controllers/Api/AuthController.php
class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->email,
            $request->password,
            $request->device_name ?? 'mobile'
        );

        if (!$result) {
            return response()->json(['message' => __('auth.failed')], 401);
        }

        return response()->json($result);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => __('auth.logged_out')]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(
            new UserResource($request->user()->load('school'))
        );
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->validated());
        return response()->json(new UserResource($user->fresh('school')));
    }

    public function updateAvatar(AvatarUploadRequest $request): JsonResponse
    {
        $user = $request->user();
        $path = $request->file('avatar')->store("avatars/{$user->school_id}", 's3');
        $user->update(['avatar' => $path]);
        return response()->json(new UserResource($user->fresh()));
    }

    public function updateFcmToken(Request $request): JsonResponse
    {
        $request->validate(['fcm_token' => 'required|string']);
        $request->user()->update(['fcm_token' => $request->fcm_token]);
        return response()->json(['message' => 'ok']);
    }
}
```

---

## AuthService Implementation

```php
// app/Services/AuthService.php
class AuthService
{
    public function login(string $email, string $password, string $deviceName): ?array
    {
        $user = User::where('email', $email)
            ->where('is_active', true)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        // Revoke old tokens untuk device yang sama
        $user->tokens()->where('name', $deviceName)->delete();

        $token = $user->createToken($deviceName)->plainTextToken;

        return [
            'token' => $token,
            'user'  => new UserResource($user->load('school', 'roles', 'permissions')),
        ];
    }
}
```

---

## LoginRequest Validation

```php
// app/Http/Requests/Auth/LoginRequest.php
class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email'       => ['required', 'email'],
            'password'    => ['required', 'string', 'min:8'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => __('validation.required', ['attribute' => 'email']),
            'password.required' => __('validation.required', ['attribute' => 'password']),
        ];
    }
}
```

---

## UpdateProfileRequest Validation

```php
// app/Http/Requests/Auth/UpdateProfileRequest.php
class UpdateProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'   => ['sometimes', 'string', 'max:255'],
            'phone'  => ['sometimes', 'nullable', 'regex:/^(08|\+628)\d{8,11}$/'],
            'locale' => ['sometimes', 'in:id,en,ar'],
        ];
    }
}
```

---

## Avatar Upload
- Resize ke max **400x400** sebelum upload (gunakan `Intervention/Image`)
- Store di S3 path: `avatars/{school_id}/{user_id}.jpg`
- Return URL via `Storage::url()`

---

## Flutter Auth Flow

```dart
// lib/features/auth/data/auth_repository.dart
class AuthRepository {
  Future<UserModel> login(String email, String password) async {
    final response = await _api.post('/auth/login', {
      'email':       email,
      'password':    password,
      'device_name': await _getDeviceName(),
    });
    final user = UserModel.fromJson(response['user']);
    await AppStorage.saveToken(response['token']);
    await AppStorage.saveUser(user);
    return user;
  }
}

// lib/features/auth/presentation/bloc/auth_bloc.dart
// States: AuthInitial, AuthLoading, AuthAuthenticated, AuthError
// Events: AuthLoginRequested, AuthLogoutRequested, AuthCheckRequested
```

---

## Acceptance Criteria

- [ ] Login dengan kredensial valid → token + user + permissions array
- [ ] Login password salah → 401
- [ ] User inactive tidak bisa login → 401
- [ ] FCM token tersimpan dan tampil di /me
- [ ] Profile update validasi format HP Indonesia (08xx atau +628xx)
- [ ] Avatar upload resize ke max 400x400, tersimpan di S3
- [ ] Password reset email terkirim (test dengan Mailtrap/array driver)
- [ ] Logout menghapus token dari DB

## Tests to Write

```
tests/Feature/Auth/
  LoginTest.php
  LogoutTest.php
  ProfileUpdateTest.php
  AvatarUploadTest.php
  FcmTokenTest.php
  PasswordResetTest.php
  InactiveUserTest.php
```
