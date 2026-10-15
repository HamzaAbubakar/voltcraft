<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-xl font-bold text-white">Sign in</h1>
        <p class="mt-1 text-sm text-gray-400">Access the solar battery operations dashboard.</p>
    </div>

    <x-auth-session-status class="mb-4 text-sm text-emerald-400" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="mb-1.5 block text-sm font-medium text-gray-300">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-red-400" />
        </div>

        <div>
            <label for="password" class="mb-1.5 block text-sm font-medium text-gray-300">Password</label>
            <input id="password" name="password" type="password" required autocomplete="current-password"
                class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white placeholder-gray-500 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-red-400" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-gray-400">
                <input id="remember_me" type="checkbox" class="rounded border-gray-700 bg-gray-950 text-amber-500 focus:ring-amber-500" name="remember">
                <span>Remember me</span>
            </label>
            @if (Route::has('password.request'))
                <a class="text-sm text-amber-400 hover:text-amber-300" href="{{ route('password.request') }}">
                    Forgot password?
                </a>
            @endif
        </div>

        <button type="submit" class="w-full rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-bold text-gray-950 transition hover:bg-amber-400">
            Sign in
        </button>
    </form>

</x-guest-layout>
