@extends('layouts.dashboard')

@section('content')
    <div class="space-y-8">
        <div class="flex flex-col gap-4 border-b border-gray-800 pb-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-white">Control Panel</h2>
                <p class="mt-0.5 text-xs text-gray-400">Manage notification recipients and download database backups.</p>
            </div>
            <a href="{{ route('control-panel.download-db') }}" class="inline-flex items-center justify-center rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-gray-950 shadow-lg shadow-amber-500/10 transition-colors hover:bg-amber-400">
                <i class="fas fa-download mr-2"></i>Download DB Backup
            </a>
        </div>

        @if (session('success'))
            <div role="status" class="rounded-lg border border-emerald-800 bg-emerald-950/40 px-4 py-3 text-sm text-emerald-300">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div role="alert" class="rounded-lg border border-red-900 bg-red-950/40 px-4 py-3 text-sm text-red-300">{{ session('error') }}</div>
        @endif

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
                <div class="flex items-center justify-between text-xs text-gray-400 mb-2">
                    <span>Notification Recipients</span>
                    <span class="font-mono text-xs">{{ $recipients->count() }}</span>
                </div>
                <p class="text-xs text-gray-500 mb-4">Emails that receive low-stock and investment-due alerts.</p>

                <form method="POST" action="{{ route('control-panel.recipients.store') }}" class="space-y-3">
                    @csrf
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input type="email" name="email" placeholder="email@example.com" required
                            class="flex-1 rounded-lg border border-gray-700 bg-gray-900 px-3 py-2 text-xs text-gray-200 focus:border-amber-500 focus:outline-none">
                        <button type="submit" class="rounded-lg bg-amber-500 px-3 py-2 text-xs font-bold text-gray-950 hover:bg-amber-400">
                            Add
                        </button>
                    </div>
                </form>

                @if ($recipients->isNotEmpty())
                    <div class="mt-4 space-y-2">
                        @foreach ($recipients as $recipient)
                            <div class="flex items-center justify-between px-3 py-2 rounded-lg border border-gray-800 bg-gray-950/50">
                                <span class="text-sm text-gray-200">{{ $recipient->email }}</span>
                                <form method="POST" action="{{ route('control-panel.recipients.destroy', $recipient) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-400 hover:text-red-300">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-gray-500 italic">No notification recipients configured. Add an email above to start receiving alerts.</p>
                @endif
            </div>

            <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
                <div class="flex items-center justify-between text-xs text-gray-400 mb-2">
                    <span>Database Backup</span>
                    <span class="font-mono text-xs">{{ $dbSizeFormatted }}</span>
                </div>
                <p class="text-xs text-gray-500 mb-4">Download a copy of the SQLite database file.</p>

                <div class="space-y-3">
                    <p class="text-xs text-gray-400">
                        <span class="font-semibold text-gray-200">Database path:</span><br>
                        <span class="font-mono text-[10px] text-gray-500">{{ $dbPath }}</span>
                    </p>
                    @if ($lastDbBackupDownloadedAt)
                        <p class="text-xs text-gray-400">
                            <span class="font-semibold text-gray-200">Last downloaded:</span><br>
                            <span class="font-mono text-[10px] text-gray-500">{{ \Carbon\Carbon::parse($lastDbBackupDownloadedAt)->format('M d, Y h:i A') }}</span>
                        </p>
                    @else
                        <p class="text-xs text-amber-400">
                            <span class="font-semibold">Never downloaded.</span><br>
                            <span class="text-[10px]">A backup reminder will appear on the dashboard every 7 days until you download.</span>
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-800 bg-gray-900 p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center gap-6">
                <div class="flex-shrink-0">
                    <div class="rounded-xl bg-amber-500/10 p-3 text-amber-400">
                        <i class="fas fa-mobile-alt text-2xl"></i>
                    </div>
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        <h3 class="text-lg font-bold text-white">Download Voltcraft App</h3>
                        <span class="rounded-full border border-amber-500/30 bg-amber-500/10 px-2.5 py-0.5 text-[10px] font-semibold text-amber-300">Android APK</span>
                    </div>
                    <p class="text-xs text-gray-400 mb-4">Install the Voltcraft mobile app to receive push notifications for low-stock and investment-due alerts.</p>
                    <div class="flex items-center gap-4 text-xs text-gray-500">
                        <span class="flex items-center gap-1.5">
                            <i class="fas fa-download text-gray-400"></i>
                            <span>{{ $apkSizeFormatted }}</span>
                        </span>
                        <span class="w-px h-4 bg-gray-800"></span>
                        <span class="flex items-center gap-1.5">
                            <i class="fas fa-info-circle text-gray-400"></i>
                            <span>Enable "Install from unknown sources"</span>
                        </span>
                    </div>
                </div>
                <div class="flex-shrink-0">
                    @if ($apkAvailable)
                        <a href="{{ route('control-panel.download-apk') }}" class="inline-flex items-center justify-center w-full sm:w-auto rounded-xl bg-amber-500 px-5 py-3 text-sm font-bold text-gray-950 shadow-lg shadow-amber-500/20 transition-all hover:bg-amber-400 hover:shadow-amber-500/40 hover:-translate-y-1 active:scale-95">
                            <i class="fas fa-download mr-2"></i>Download APK
                        </a>
                    @else
                        <button disabled class="inline-flex items-center justify-center w-full sm:w-auto rounded-xl px-5 py-3 text-sm font-bold text-gray-500 bg-gray-800 border border-gray-700 cursor-not-allowed">
                            <i class="fas fa-download mr-2"></i>APK not available
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-800 bg-gray-900 p-4">
            <div class="flex items-center justify-between text-xs text-gray-400 mb-2">
                <span>Push Notification Devices</span>
                <span class="font-mono text-xs">{{ $devices->count() }}</span>
            </div>
            <p class="text-xs text-gray-500 mb-4">Android devices registered via the Voltcraft mobile app to receive low-stock and investment-due push alerts.</p>

            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full min-w-[700px] border-collapse text-left text-xs">
                    <thead>
                        <tr class="border-b border-gray-800 text-gray-500">
                            <th class="px-4 py-3 font-semibold">Device</th>
                            <th class="px-4 py-3 font-semibold">User</th>
                            <th class="px-4 py-3 font-semibold">Platform</th>
                            <th class="px-4 py-3 font-semibold">Last used</th>
                            <th class="px-4 py-3 font-semibold text-right">FCM token</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800/70">
                        @forelse ($devices as $device)
                            <tr class="hover:bg-gray-800/30">
                                <td class="px-4 py-4">
                                    <p class="font-semibold text-gray-200">{{ $device->device_name ?? 'Android device' }}</p>
                                    <p class="mt-1 text-[10px] text-gray-500">{{ strtolower((string) $device->platform) }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="text-gray-200">{{ $device->user->name ?? $device->user->email }}</p>
                                    <p class="mt-1 text-[10px] text-gray-500">{{ $device->user->email }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="rounded border border-gray-700 bg-gray-800 px-2 py-1 text-[10px] text-gray-300">{{ ucfirst((string) $device->platform) }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    @if ($device->last_used_at)
                                        {{ \Carbon\Carbon::parse($device->last_used_at)->format('M d, Y h:i A') }}
                                    @else
                                        <span class="text-gray-500">Never</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <span class="font-mono text-[10px] text-gray-400 block truncate max-w-[140px]">{{ substr((string) $device->fcm_token, 0, 12) }}…</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-xs text-gray-500">No push notification devices registered yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div id="push-device-registration" class="mt-6 pt-6 border-t border-gray-800 hidden">
                    <p class="text-xs text-gray-500 mb-3">No push notification device registered for your account yet.</p>
                    <button
                        id="register-push-device"
                        class="inline-flex items-center justify-center w-full sm:w-auto rounded-lg bg-amber-500 px-4 py-2 text-xs font-bold text-gray-950 shadow-lg shadow-amber-500/20 hover:bg-amber-400 hover:shadow-amber-500/40 hover:-translate-y-1 active:scale-95 transition-all"
                    >
                        <i class="fas fa-mobile-alt mr-2"></i>Register Device for Push Notifications
                    </button>
                    <p class="mt-2 text-[10px] text-gray-500">This will use your account name as the device name.</p>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        (function () {
            var btn = document.getElementById('register-push-device');
            var registrationPanel = document.getElementById('push-device-registration');
            if (!btn || !registrationPanel) return;

            function showToast(message, type) {
                var toast = document.createElement('div');
                toast.className = 'fixed bottom-5 right-5 z-50 px-4 py-3 rounded-lg text-xs font-semibold ' +
                    (type === 'success' ? 'bg-emerald-900 text-emerald-200 border border-emerald-700' : 'bg-red-900 text-red-200 border border-red-700');
                toast.textContent = message;
                document.body.appendChild(toast);
                setTimeout(function () { toast.remove(); }, 5000);
            }

            function getNativeToken() {
                try {
                    if (window.VoltcraftNative && typeof window.VoltcraftNative.getFcmToken === 'function') {
                        return window.VoltcraftNative.getFcmToken();
                    } else if (window.VoltcraftPushToken) {
                        return window.VoltcraftPushToken;
                    }
                } catch (e) {
                    console.warn('VoltcraftPush: unable to read FCM token from native bridge', e);
                }
                return null;
            }

            function checkDeviceRegistration(token) {
                fetch('{{ route('push-devices.status') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ fcm_token: token }),
                })
                .then(function (response) {
                    return response.ok ? response.json() : null;
                })
                .then(function (data) {
                    if (data && data.registered) {
                        registrationPanel.classList.add('hidden');
                    } else {
                        registrationPanel.classList.remove('hidden');
                    }
                })
                .catch(function () {
                    registrationPanel.classList.remove('hidden');
                });
            }

            var nativeToken = getNativeToken();
            if (nativeToken) {
                checkDeviceRegistration(nativeToken);
            } else if (window.VoltcraftNative &&
                typeof window.VoltcraftNative.requestPushNotifications === 'function') {
                window.VoltcraftNative.requestPushNotifications();
                var attempts = 0;
                var tokenTimer = window.setInterval(function () {
                    attempts += 1;
                    var token = getNativeToken();
                    if (token) {
                        window.clearInterval(tokenTimer);
                        checkDeviceRegistration(token);
                    } else if (attempts >= 20) {
                        window.clearInterval(tokenTimer);
                        registrationPanel.classList.remove('hidden');
                    }
                }, 250);
            }

            btn.addEventListener('click', function () {
                var token = getNativeToken();
                if (!token && window.VoltcraftNative &&
                    typeof window.VoltcraftNative.requestPushNotifications === 'function') {
                    window.VoltcraftNative.requestPushNotifications();
                }

                if (!token) {
                    showToast('Waiting for notification permission and FCM token registration...', 'success');
                    var attempts = 0;
                    var tokenTimer = window.setInterval(function () {
                        attempts += 1;
                        token = getNativeToken();
                        if (token) {
                            window.clearInterval(tokenTimer);
                            registerToken(token);
                        } else if (attempts >= 20) {
                            window.clearInterval(tokenTimer);
                            showToast('Unable to read FCM token. Please enable notifications and try again.', 'error');
                        }
                    }, 250);
                } else {
                    registerToken(token);
                }
            });

            function registerToken(token) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i>Registering...';

                fetch('{{ route('push-devices.register') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        fcm_token: token,
                        device_name: '{{ addslashes(auth()->user()->name) }}',
                    }),
                })
                .then(function (response) {
                    if (!response.ok) {
                        return response.json().then(function (err) {
                            throw new Error(err?.message || 'Registration failed with status ' + response.status);
                        });
                    }
                    return response.json();
                })
                .then(function (data) {
                    showToast(data.message || 'Device registered successfully.', 'success');
                    setTimeout(function () { location.reload(); }, 1500);
                })
                .catch(function (err) {
                    showToast(err.message || 'Registration failed. Please try again.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-mobile-alt mr-2"></i>Register Device for Push Notifications';
                });
            }
        })();
    </script>
    @endpush
@endsection