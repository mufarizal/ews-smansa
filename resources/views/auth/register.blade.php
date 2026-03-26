<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register | EWS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-lime-50 text-slate-900 antialiased">
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -left-24 top-0 h-80 w-80 rounded-full bg-lime-300/40 blur-3xl"></div>
        <div class="absolute -right-20 bottom-0 h-96 w-96 rounded-full bg-blue-300/30 blur-3xl"></div>
    </div>

    <main class="mx-auto flex min-h-screen w-full max-w-6xl items-center px-4 py-10 sm:px-6 lg:px-8">
        <section
            class="grid w-full overflow-hidden rounded-3xl border border-lime-200 bg-white shadow-xl shadow-lime-900/10 md:grid-cols-2">
            <aside class="hidden bg-lime-700 p-10 text-white md:flex md:flex-col md:justify-between">
                <div>
                    <img src="{{ asset('img/logo.png') }}" alt="Logo EWS" class="h-16 w-16 rounded-lg bg-white/90 p-1">
                    <h1 class="mt-6 text-3xl font-bold leading-tight">Buat Akun EWS</h1>
                    <p class="mt-3 text-sm text-lime-100">Daftarkan akun Anda untuk mulai menggunakan fitur manajemen
                        pendidikan yang terintegrasi.</p>
                </div>
                <p class="text-sm text-lime-100">Satu akun untuk akses data siswa, guru, dan laporan sekolah.</p>
            </aside>

            <div class="p-6 sm:p-10">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-slate-900">Daftar Akun Baru</h2>
                    <p class="mt-1 text-sm text-slate-600">Lengkapi data berikut untuk membuat akun.</p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="name"
                            class="block text-sm font-semibold text-slate-700">{{ __('Name') }}</label>
                        <input id="name"
                            class="mt-2 block w-full rounded-xl border-slate-300 px-3 py-2.5 shadow-sm focus:border-lime-600 focus:ring-lime-600"
                            type="text" name="name" value="{{ old('name') }}" required autofocus
                            autocomplete="name">
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email"
                            class="block text-sm font-semibold text-slate-700">{{ __('Email') }}</label>
                        <input id="email"
                            class="mt-2 block w-full rounded-xl border-slate-300 px-3 py-2.5 shadow-sm focus:border-lime-600 focus:ring-lime-600"
                            type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password"
                            class="block text-sm font-semibold text-slate-700">{{ __('Password') }}</label>
                        <input id="password"
                            class="mt-2 block w-full rounded-xl border-slate-300 px-3 py-2.5 shadow-sm focus:border-lime-600 focus:ring-lime-600"
                            type="password" name="password" required autocomplete="new-password">
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation"
                            class="block text-sm font-semibold text-slate-700">{{ __('Confirm Password') }}</label>
                        <input id="password_confirmation"
                            class="mt-2 block w-full rounded-xl border-slate-300 px-3 py-2.5 shadow-sm focus:border-lime-600 focus:ring-lime-600"
                            type="password" name="password_confirmation" required autocomplete="new-password">
                        @error('password_confirmation')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between gap-4 pt-1">
                        <a class="text-sm font-medium text-red-700 transition hover:text-red-600"
                            href="{{ route('login') }}">
                            {{ __('Already registered?') }}
                        </a>

                        <button type="submit"
                            class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                            {{ __('Register') }}
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </main>
</body>

</html>
