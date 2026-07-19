<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AvatarUploadRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->email,
            $request->password,
            $request->device_name ?? 'mobile',
            $request->input('two_factor_code'),
            $request->input('recovery_code'),
        );

        if (!$result) {
            return response()->json(['message' => __('auth.failed')], 401);
        }

        if (!empty($result['two_factor_required'])) {
            return response()->json($result, 202);
        }

        return response()->json($result);
    }

    public function verifyTwoFactor(Request $request): JsonResponse
    {
        $request->validate([
            'challenge_id'     => 'required|string',
            'two_factor_code'  => 'nullable|string|size:6',
            'recovery_code'    => 'nullable|string',
            'device_name'      => 'nullable|string|max:200',
        ]);

        $result = $this->authService->verifyTwoFactor(
            $request->challenge_id,
            $request->input('two_factor_code'),
            $request->input('recovery_code'),
            $request->input('device_name', 'mobile'),
        );

        if (!$result) {
            return response()->json(['message' => 'Invalid challenge or 2FA code.'], 401);
        }
        return response()->json($result);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(
            new UserResource($request->user()->load('school', 'roles', 'permissions'))
        );
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->validated());
        return response()->json(new UserResource($user->fresh()->load('school')));
    }

    public function updateAvatar(AvatarUploadRequest $request): JsonResponse
    {
        $user = $request->user();

        $image = \Intervention\Image\Laravel\Facades\Image::read($request->file('avatar'));
        $image->scaleDown(400, 400);

        $path = "avatars/{$user->school_id}/{$user->id}.jpg";
        \Illuminate\Support\Facades\Storage::put($path, $image->toJpeg());

        $user->update(['avatar' => $path]);

        return response()->json(new UserResource($user->fresh()));
    }

    public function updateFcmToken(Request $request): JsonResponse
    {
        $request->validate(['fcm_token' => 'required|string']);
        $request->user()->update(['fcm_token' => $request->fcm_token]);
        return response()->json(['message' => 'ok']);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = $this->authService->sendPasswordResetLink($request->email);

        return response()->json([
            'message' => $status === Password::RESET_LINK_SENT
                ? 'Reset link sent to your email.'
                : 'Unable to send reset link.',
        ], $status === Password::RESET_LINK_SENT ? 200 : 422);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Kata sandi sekarang salah.'], 422);
        }

        $user->update(['password' => $request->password]);

        return response()->json(['message' => 'Kata sandi berhasil diubah.']);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = $this->authService->resetPassword($request->only(
            'token', 'email', 'password', 'password_confirmation'
        ));

        return response()->json([
            'message' => $status === Password::PASSWORD_RESET
                ? 'Password reset successfully.'
                : 'Invalid token or email.',
        ], $status === Password::PASSWORD_RESET ? 200 : 422);
    }
}
