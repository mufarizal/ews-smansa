<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="theme-color" content="#22c55e">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="EWS">
    <link rel="icon" href="{{ asset('img/logo.png') }}" type="image/png">
    <title>Login | EWS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
</head>

<body class="min-h-screen bg-lime-50 text-slate-900 antialiased">
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -left-24 top-0 h-80 w-80 rounded-full bg-lime-300/40 blur-3xl"></div>
        <div class="absolute -right-20 bottom-0 h-96 w-96 rounded-full bg-red-300/30 blur-3xl"></div>
    </div>

    <main class="mx-auto flex min-h-screen w-full max-w-6xl items-center px-4 py-10 sm:px-6 lg:px-8">
        <section
            class="grid w-full overflow-hidden rounded-3xl border border-lime-200 bg-white shadow-xl shadow-lime-900/10 md:grid-cols-2">
            <aside class="hidden bg-lime-700 p-10 text-white md:flex md:flex-col md:justify-between">
                <div>
                    <img src="{{ asset('img/logo.png') }}" alt="Logo EWS" class="h-16 w-16 rounded-lg  p-1">
                    <h1 class="mt-6 text-3xl font-bold leading-tight">Selamat Datang di EWS</h1>
                    <p class="mt-3 text-sm text-lime-100">Sistem manajemen pendidikan dini yang membantu sekolah
                        berjalan lebih rapi, cepat, dan akurat.</p>
                </div>
                <p class="text-sm text-lime-100">Kelola data siswa, guru, dan laporan dalam satu dashboard.</p>
            </aside>

            <div class="p-6 sm:p-10">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-slate-900">Masuk Akun</h2>
                    <p class="mt-1 text-sm text-slate-600">Gunakan email dan password Anda untuk melanjutkan.</p>
                </div>

                @if (session('status'))
                    <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email"
                            class="block text-sm font-semibold text-slate-700">{{ __('Email') }}</label>
                        <input id="email"
                            class="mt-2 block w-full rounded-xl border-slate-300 px-3 py-2.5 shadow-sm focus:border-lime-600 focus:ring-lime-600"
                            type="email" name="email" value="{{ old('email') }}" required autofocus
                            autocomplete="username">
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password"
                            class="block text-sm font-semibold text-slate-700">{{ __('Password') }}</label>
                        <div class="relative mt-2">
                            <input id="password"
                                class="block w-full rounded-xl border-slate-300 px-3 py-2.5 pr-11 shadow-sm focus:border-lime-600 focus:ring-lime-600"
                                type="password" name="password" required autocomplete="current-password">
                            <button type="button" id="togglePassword" tabindex="-1"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 transition hover:text-slate-600"
                                aria-label="Tampilkan password">
                                <i class="ti ti-eye text-lg" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <label for="remember_me" class="inline-flex items-center gap-2">
                            <input id="remember_me" type="checkbox"
                                class="rounded border-slate-300 text-lime-700 shadow-sm focus:ring-lime-600"
                                name="remember">
                            <span class="text-sm text-slate-600">{{ __('Remember me') }}</span>
                        </label>

                        {{-- Link install PWA — kecil, senada dengan style "forgot password" sebelumnya --}}
                        <a href="{{ route('install') }}"
                            class="inline-flex items-center gap-1 text-sm font-medium text-lime-700 transition hover:text-lime-600">
                            <i class="ti ti-device-mobile-down text-base"></i>
                            Cara Install
                        </a>
                    </div>

                    <button type="submit"
                        class="w-full rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                        {{ __('Log in') }}
                    </button>

                    {{-- Forgot password dipindah ke bawah tombol login, kecil & center --}}
                    @if (Route::has('password.request'))
                        <p class="text-center">
                            <a class="text-sm font-medium text-red-700 transition hover:text-red-600"
                                href="{{ route('password.request') }}">
                                {{ __('Forgot your password?') }}
                            </a>
                        </p>
                    @endif
                </form>
            </div>
        </section>
    </main>

    <script>
        (function() {
            const toggleBtn = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const icon = document.getElementById('togglePasswordIcon');

            if (!toggleBtn || !passwordInput || !icon) return;

            toggleBtn.addEventListener('click', function() {
                const isHidden = passwordInput.type === 'password';
                passwordInput.type = isHidden ? 'text' : 'password';

                icon.classList.toggle('ti-eye', !isHidden);
                icon.classList.toggle('ti-eye-off', isHidden);

                toggleBtn.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
            });
        })();
    </script>
</body>

</html>
