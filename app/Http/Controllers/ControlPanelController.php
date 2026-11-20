<?php

namespace App\Http\Controllers;

use App\Models\NotificationDevice;
use App\Models\NotificationRecipient;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class ControlPanelController extends Controller
{
    public function index(): View
    {
        $recipients = NotificationRecipient::query()->orderBy('email')->get();

        $devices = NotificationDevice::query()
            ->with('user')
            ->orderByDesc('last_used_at')
            ->get();

        $userHasDevices = NotificationDevice::query()
            ->where('user_id', Auth::id())
            ->exists();

        $apkPath = storage_path('app/apk/app-debug.apk');
        $apkSize = file_exists($apkPath) ? filesize($apkPath) : 0;
        $apkSizeFormatted = $this->formatBytes($apkSize);
        $apkAvailable = file_exists($apkPath);

        $dbPath = database_path('database.sqlite');
        $dbSize = file_exists($dbPath) ? filesize($dbPath) : 0;
        $dbSizeFormatted = $this->formatBytes($dbSize);

        $lastDownloaded = SystemSetting::get('last_db_backup_downloaded_at');
        $showDbBackupAlert = $lastDownloaded === null
            || now()->diffInDays($lastDownloaded) >= 7;

        return view('control-panel.index', [
            'recipients' => $recipients,
            'devices' => $devices,
            'userHasDevices' => $userHasDevices,
            'apkSizeFormatted' => $apkSizeFormatted,
            'apkAvailable' => $apkAvailable,
            'dbSizeFormatted' => $dbSizeFormatted,
            'dbPath' => $dbPath,
            'lastDbBackupDownloadedAt' => $lastDownloaded,
            'showDbBackupAlert' => $showDbBackupAlert,
        ]);
    }

    public function storeRecipient(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:notification_recipients,email'],
        ]);

        NotificationRecipient::query()->create([
            'email' => $validated['email'],
        ]);

        return redirect()
            ->route('control-panel.index')
            ->with('success', 'Notification email added.');
    }

    public function destroyRecipient(NotificationRecipient $recipient): RedirectResponse
    {
        $recipient->delete();

        return redirect()
            ->route('control-panel.index')
            ->with('success', 'Notification email removed.');
    }

    public function downloadDb()
    {
        $sourcePath = database_path('database.sqlite');

        if (! file_exists($sourcePath)) {
            return back()->with('error', 'Database file not found.');
        }

        $backupDir = storage_path('app/backups');
        File::ensureDirectoryExists($backupDir);

        $backupName = 'voltcraft_backup_'.now()->format('Ymd_His').'.sqlite';
        $backupPath = $backupDir.DIRECTORY_SEPARATOR.$backupName;

        copy($sourcePath, $backupPath);

        SystemSetting::set('last_db_backup_downloaded_at', now());

        return response()->download($backupPath, $backupName, [
            'Content-Type' => 'application/octet-stream',
        ])->deleteFileAfterSend(true);
    }

    public function downloadApk()
    {
        $apkPath = storage_path('app/apk/app-debug.apk');

        if (! file_exists($apkPath)) {
            return back()->with('error', 'Android APK is not available on this server.');
        }

        return response()->download($apkPath, 'voltcraft.apk', [
            'Content-Type' => 'application/vnd.android.package-archive',
        ]);
    }

    /**
     * Format bytes into a human-readable string.
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        $power = min($power, count($units) - 1);

        return number_format($bytes / pow(1024, $power), 2).' '.$units[$power];
    }
}
