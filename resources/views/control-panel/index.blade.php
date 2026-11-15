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
    </div>
@endsection