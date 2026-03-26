<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Konfirmasi Password | EWS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-lime-50 text-slate-900 antialiased">
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -left-24 top-0 h-80 w-80 rounded-full bg-lime-300/40 blur-3xl"></div>
        <div class="absolute -right-20 bottom-0 h-96 w-96 rounded-full bg-red-300/30 blur-3xl"></div>
    </div>

    <main class="mx-auto flex min-h-screen w-full max-w-5xl items-center px-4 py-10 sm:px-6 lg:px-8">
        <section class="w-full rounded-3xl border border-lime-200 bg-white p-6 shadow-xl shadow-lime-900/10 sm:p-10">
            <div class="mb-8 flex items-center gap-3">
                <img src="{{ asset('img/logo.png') }}" alt="Logo EWS" class="h-12 w-12 rounded-lg bg-lime-100 p-1">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Konfirmasi Password</h1>
                    <p class="text-sm text-slate-600">Verifikasi akun sebelum melanjutkan ke area aman.</p>
                </div>
            </div>

            <p class="mb-5 rounded-xl border border-lime-100 bg-lime-50 px-4 py-3 text-sm text-slate-700">
                {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
            </p>

            <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="password"
                        class="block text-sm font-semibold text-slate-700">{{ __('Password') }}</label>
                    <input id="password"
                        class="mt-2 block w-full rounded-xl border-slate-300 px-3 py-2.5 shadow-sm focus:border-lime-600 focus:ring-lime-600"
                        type="password" name="password" required autocomplete="current-password">
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    {{ __('Confirm') }}
                </button>
            </form>
        </section>
    </main>
</body>

</html>
