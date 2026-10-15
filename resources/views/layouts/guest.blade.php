<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Voltcraft ERP') }}</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
    <body class="min-h-screen bg-gray-950 px-4 py-10 font-sans text-gray-100 antialiased">
        <div class="mx-auto w-full max-w-md">
            <a href="{{ route('home') }}" class="mb-8 flex items-center justify-center gap-3">
                <span class="rounded-xl bg-amber-500 p-3 text-xl text-gray-950"><i class="fas fa-charging-station"></i></span>
                <span class="text-2xl font-black tracking-tight text-white">VOLT<span class="text-amber-500">CRAFT</span> ERP</span>
            </a>
            <div class="rounded-2xl border border-gray-800 bg-gray-900 p-6 shadow-xl sm:p-8">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
