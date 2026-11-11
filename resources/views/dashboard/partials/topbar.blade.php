    <!-- Top Navigation Bar -->
    <header class="bg-gray-900 border-b border-gray-800 sticky top-0 z-50 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <div class="bg-amber-500 text-gray-950 p-2 rounded-xl font-black text-xl shadow-lg shadow-amber-500/10">
                <i class="fas fa-charging-station"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-white">VOLT<span class="text-amber-500">CRAFT</span> ERP</h1>
                <p class="text-xs text-gray-400">Solar Battery Assembly & Management Suite</p>
            </div>
        </div>
        <div class="flex items-center space-x-4">
            <div class="text-right hidden sm:block">
                <p class="text-sm font-medium text-gray-200">{{ auth()->user()->name }}</p>
                <p class="text-xs text-emerald-400 flex items-center justify-end"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block mr-1.5 animate-pulse"></span>System Live</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-lg border border-gray-700 px-3 py-2 text-xs font-medium text-gray-300 transition hover:border-amber-500 hover:text-amber-400">
                    <i class="fas fa-sign-out-alt mr-1"></i> Sign out
                </button>
            </form>
        </div>
    </header>
