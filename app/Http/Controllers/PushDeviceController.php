<?php

namespace App\Http\Controllers;

use App\Models\NotificationDevice;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PushDeviceController extends Controller
{
    /**
     * Register (or upsert) an FCM device token for the authenticated user.
     *
     * Called by the NativePHP Android WebView after it receives its FCM token.
     */
    public function register(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => ['required', 'string', 'min:20', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        /** @var User $user */
        $user = Auth::user();

        NotificationDevice::updateOrCreate(
            ['fcm_token' => $request->string('fcm_token')],
            [
                'user_id' => $user->getKey(),
                'device_name' => $request->string('device_name') ?? 'Android device',
                'platform' => 'android',
                'last_used_at' => now(),
            ]
        );

        Log::info('Push device registered', [
            'user_id' => $user->getKey(),
            'token_prefix' => substr($request->string('fcm_token'), 0, 8),
        ]);

        return back()->with('success', 'Push notifications enabled for this device.');
    }
}
