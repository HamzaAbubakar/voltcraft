<?php

namespace App\Http\Controllers;

use App\Models\NotificationDevice;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PushDeviceController extends Controller
{
    /**
     * Register (or upsert) an FCM device token for the authenticated user.
     *
     * Called by the NativePHP Android WebView or the control panel after it
     * receives its FCM token.
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => ['required', 'string', 'min:20', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        /** @var User $user */
        $user = Auth::user();
        $fcmToken = $request->string('fcm_token')->toString();

        NotificationDevice::updateOrCreate(
            ['fcm_token' => $fcmToken],
            [
                'user_id' => $user->getKey(),
                'device_name' => $request->string('device_name') ?? 'Android device',
                'platform' => 'android',
                'last_used_at' => now(),
            ]
        );

        Log::info('Push device registered', [
            'user_id' => $user->getKey(),
            'token_prefix' => substr($fcmToken, 0, 8),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Push notifications enabled for this device.',
        ]);
    }

    public function status(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => ['required', 'string', 'min:20', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $registered = NotificationDevice::query()
            ->where('user_id', Auth::id())
            ->where('fcm_token', $request->string('fcm_token')->toString())
            ->exists();

        return response()->json([
            'success' => true,
            'registered' => $registered,
        ]);
    }
}
