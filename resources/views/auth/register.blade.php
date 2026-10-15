<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-xl font-bold text-white">Create your account</h1>
        <p class="mt-1 text-sm text-gray-400">Register to access Voltcraft ERP.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label for="name" class="mb-1.5 block text-sm font-medium text-gray-300">Name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name"
                class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
            <x-input-error :messages="$errors->get('name')" class="mt-2 text-sm text-red-400" />
        </div>

        <div>
            <label for="email" class="mb-1.5 block text-sm font-medium text-gray-300">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username"
                class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-red-400" />
        </div>

        <div>
            <label for="password" class="mb-1.5 block text-sm font-medium text-gray-300">Password</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-red-400" />
        </div>

        <div>
            <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-gray-300">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                class="w-full rounded-lg border border-gray-700 bg-gray-950 px-3 py-2.5 text-sm text-white focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-sm text-red-400" />
        </div>

        <button type="submit" class="w-full rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-bold text-gray-950 transition hover:bg-amber-400">
            Create account
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-400">
        Already registered?
        <a href="{{ route('login') }}" class="font-medium text-amber-400 hover:text-amber-300">Sign in</a>
    </p>
</x-guest-layout>
