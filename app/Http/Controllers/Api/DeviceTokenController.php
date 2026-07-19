<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Communication\DeviceToken;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'token'       => 'required|string|max:255',
            'platform'    => 'nullable|string|in:android,ios,web',
            'device_name' => 'nullable|string|max:200',
        ]);

        $user = $request->user();
        $token = DeviceToken::withoutGlobalScopes()->updateOrCreate(
            ['user_id' => $user->id, 'token' => $data['token']],
            [
                'school_id'   => $user->school_id,
                'platform'    => $data['platform'] ?? null,
                'device_name' => $data['device_name'] ?? null,
                'last_used_at' => now(),
            ]
        );

        $user->fcm_token = $data['token'];
        $user->save();

        return response()->json(['ok' => true, 'id' => $token->id]);
    }

    public function unregister(Request $request)
    {
        $data = $request->validate(['token' => 'required|string|max:255']);
        $user = $request->user();
        DeviceToken::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->where('token', $data['token'])
            ->delete();
        if ($user->fcm_token === $data['token']) {
            $user->fcm_token = null;
            $user->save();
        }
        return response()->json(['ok' => true]);
    }
}
