<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Voltcraft ERP') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #111827; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #374151; border-radius: 3px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #4B5563; }
    </style>
</head>
<body class="bg-gray-950 text-gray-100 font-sans antialiased custom-scrollbar">
    @include('dashboard.partials.topbar')

    <div class="flex min-h-[calc(100vh-73px)]">
        @include('dashboard.partials.sidebar')

        <main class="flex-1 p-6 lg:p-8 space-y-8 overflow-x-hidden">
            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>
