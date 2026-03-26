<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'EWS SMANSA') }} — @yield('title', 'Dashboard')</title>
    {{-- Tabler Icons Webfont — satu baris, langsung jalan, pakai class "ti ti-nama-icon" --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-stone-100 text-stone-900 antialiased">

    @auth
        <div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-black/50 md:hidden" onclick="closeSidebar()">
        </div>
        @include('partials.sidebar')
    @endauth

    <div class="flex min-h-screen flex-col @auth md:ml-64 @endauth">

        {{-- TOPBAR --}}
        <header
            class="sticky top-0 z-20 flex h-14 items-center justify-between border-b-2 border-stone-200 bg-white px-4 md:px-6">

            <div class="flex items-center gap-3">
                @auth
                    <button onclick="toggleSidebar()"
                        class="flex items-center rounded-md p-1.5 text-green-900 hover:bg-stone-100 md:hidden"
                        aria-label="Buka menu">
                        <i class="ti ti-menu-2 text-xl"></i>
                    </button>
                @endauth
                <h2 class="text-sm font-bold tracking-tight text-green-900 md:text-base">
                    @yield('title', 'Dashboard')
                </h2>

            </div>

            <div id="tanggalwaktu" class="hidden text-xs text-black md:block"></div>

            @auth
                <div class="relative">

                    <button id="user-btn" onclick="toggleUserMenu()"
                        class="flex items-center gap-2 rounded-md border border-stone-200 bg-white px-2.5 py-1.5 text-sm font-medium text-stone-700 hover:bg-stone-50">
                        <span
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-green-800 text-xs font-bold text-white">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </span>
                        <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                        <i class="ti ti-chevron-down text-sm text-stone-400"></i>

                    </button>

                    <div id="user-menu"
                        class="absolute right-0 mt-2 hidden w-44 rounded-lg border border-stone-200 bg-white shadow-lg">
                        <a href="{{ route('profile.edit') }}"
                            class="flex items-center gap-2 rounded-t-lg px-3.5 py-2.5 text-sm text-stone-700 hover:bg-stone-50">
                            <i class="ti ti-user-circle text-base text-stone-400"></i>
                            Profil Saya
                        </a>
                        <div class="border-t border-stone-100"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="flex w-full items-center gap-2 rounded-b-lg px-3.5 py-2.5 text-sm text-red-600 hover:bg-red-50">
                                <i class="ti ti-logout text-base"></i>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            @endauth
        </header>

        <main class="flex-1 p-4 md:p-8">
            @yield('content')
        </main>

    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
            document.getElementById('sidebar-overlay').classList.toggle('hidden');
        }

        function dateTime() {
            const target = document.getElementById('tanggalwaktu');
            if (!target) return;

            const dt = new Date();
            target.textContent = dt.toLocaleString('id-ID', {
                weekday: 'short',
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            });
        }

        function closeSidebar() {
            document.getElementById('sidebar').classList.add('-translate-x-full');
            document.getElementById('sidebar-overlay').classList.add('hidden');
        }

        function toggleUserMenu() {
            document.getElementById('user-menu').classList.toggle('hidden');
        }
        document.addEventListener('click', function(e) {
            const btn = document.getElementById('user-btn');
            const menu = document.getElementById('user-menu');
            if (menu && btn && !btn.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });

        dateTime();
        setInterval(dateTime, 1000);
    </script>

</body>

</html>
