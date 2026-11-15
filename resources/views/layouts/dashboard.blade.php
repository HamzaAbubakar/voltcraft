<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>{{ config('app.name', 'Voltcraft ERP') }}</title>
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #111827; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #374151; border-radius: 3px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #4B5563; }
        :root { --dashboard-safe-top: max(env(safe-area-inset-top, 0px), var(--inset-top, 0px)); }
        .dashboard-topbar { padding-top: calc(1rem + var(--dashboard-safe-top)); }
        .dashboard-sidebar { top: calc(73px + var(--dashboard-safe-top)); bottom: 0; }
        .dashboard-sidebar-overlay { top: calc(73px + var(--dashboard-safe-top)); }
    </style>
</head>
<body class="bg-gray-950 text-gray-100 font-sans antialiased custom-scrollbar">
    @include('dashboard.partials.topbar')

    <button type="button" data-sidebar-overlay aria-label="Close navigation menu" class="dashboard-sidebar-overlay fixed inset-x-0 bottom-0 z-30 hidden bg-gray-950/75 lg:hidden"></button>

    <div class="flex min-h-[calc(100vh-73px)]">
        @include('dashboard.partials.sidebar')

        <main class="flex-1 p-6 lg:p-8 space-y-8 overflow-x-hidden">
            @yield('content')
        </main>
    </div>
    @stack('scripts')
    <script>
        (() => {
            const sidebar = document.getElementById('dashboard-sidebar');
            const toggle = document.querySelector('[data-sidebar-toggle]');
            const overlay = document.querySelector('[data-sidebar-overlay]');
            const desktopViewport = window.matchMedia('(min-width: 1024px)');
            let isOpen = false;

            const updateSidebar = () => {
                const isDesktop = desktopViewport.matches;
                sidebar.classList.toggle('-translate-x-full', !isDesktop && !isOpen);
                sidebar.setAttribute('aria-hidden', String(!isDesktop && !isOpen));
                sidebar.inert = !isDesktop && !isOpen;
                toggle.setAttribute('aria-expanded', String(isDesktop || isOpen));
                toggle.setAttribute('aria-label', isOpen ? 'Close navigation menu' : 'Open navigation menu');
                overlay.classList.toggle('hidden', isDesktop || !isOpen);
            };

            const closeSidebar = () => {
                isOpen = false;
                updateSidebar();
            };

            toggle.addEventListener('click', () => {
                isOpen = !isOpen;
                updateSidebar();
            });
            overlay.addEventListener('click', closeSidebar);
            sidebar.addEventListener('click', (event) => {
                if (!desktopViewport.matches && event.target.closest('a')) {
                    closeSidebar();
                }
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && isOpen) {
                    closeSidebar();
                    toggle.focus();
                }
            });
            desktopViewport.addEventListener('change', () => {
                isOpen = false;
                updateSidebar();
            });

            updateSidebar();
        })();
    </script>
</body>
</html>
