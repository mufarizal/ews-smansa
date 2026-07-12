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
    <title>Cara Install Aplikasi EWS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
</head>

<body class="min-h-screen bg-lime-50 text-slate-900 antialiased">
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -left-24 top-0 h-80 w-80 rounded-full bg-lime-300/40 blur-3xl"></div>
        <div class="absolute -right-20 bottom-0 h-96 w-96 rounded-full bg-red-300/30 blur-3xl"></div>
    </div>

    <main class="mx-auto w-full max-w-2xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-8 flex items-center gap-4">
            <a href="{{ route('login') }}"
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-lime-200 bg-white text-slate-600 transition hover:bg-lime-50">
                <i class="ti ti-arrow-left text-base"></i>
            </a>
            <div>
                <h1 class="text-xl font-bold text-slate-900">Cara Install Aplikasi EWS</h1>
                <p class="text-sm text-slate-600">Panduan otomatis sesuai perangkat Anda</p>
            </div>
        </div>

        <div class="space-y-6">

            {{-- Sudah terinstall --}}
            <div id="install-success" class="hidden rounded-xl border border-green-200 bg-green-50 p-4">
                <div class="flex items-center gap-3">
                    <i class="ti ti-check text-xl text-green-600"></i>
                    <p class="text-sm font-semibold text-green-700">Aplikasi berhasil diinstall!</p>
                </div>
            </div>

            {{-- Android - native install prompt tersedia --}}
            <div id="android-chrome-prompt" class="hidden space-y-3">
                <div class="rounded-xl border border-lime-200 bg-white p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="text-2xl">🤖</span>
                        <h2 class="font-bold text-slate-900">Android - Google Chrome</h2>
                    </div>
                    <p class="text-sm text-slate-600 mb-4">
                        Google Chrome mendeteksi aplikasi PWA. Klik tombol di bawah untuk menginstall aplikasi.
                    </p>
                    <button id="install-btn-android"
                        class="w-full rounded-xl bg-red-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-red-700">
                        Install Sekarang
                    </button>
                </div>
            </div>

            {{-- Android - fallback manual --}}
            <div id="android-chrome-manual" class="hidden space-y-3">
                <div class="rounded-xl border border-lime-200 bg-white p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="text-2xl">🤖</span>
                        <h2 class="font-bold text-slate-900">Android - Google Chrome</h2>
                    </div>
                    <p class="text-sm text-slate-600 mb-4">
                        Ikuti langkah berikut untuk menginstall aplikasi EWS:
                    </p>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 rounded-lg bg-lime-50 p-3">
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-lime-600 text-xs font-bold text-white">
                                1
                            </div>
                            <p class="text-sm text-slate-700">Buka menu Chrome dengan tap ikon 3 titik di pojok kanan
                                atas.</p>
                        </div>
                        <div class="flex items-center gap-3 rounded-lg bg-lime-50 p-3">
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-lime-600 text-xs font-bold text-white">
                                2
                            </div>
                            <p class="text-sm text-slate-700">Pilih menu <strong>"Install App"</strong> atau
                                <strong>"Add to Home Screen"</strong>.</p>
                        </div>
                        <div class="flex items-center gap-3 rounded-lg bg-lime-50 p-3">
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-lime-600 text-xs font-bold text-white">
                                3
                            </div>
                            <p class="text-sm text-slate-700">Klik <strong>"Install"</strong> di dialog konfirmasi.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Windows - native install prompt tersedia --}}
            <div id="windows-edge-prompt" class="hidden space-y-3">
                <div class="rounded-xl border border-lime-200 bg-white p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="text-2xl">🖥️</span>
                        <h2 class="font-bold text-slate-900">Windows - Chrome atau Edge</h2>
                    </div>
                    <p class="text-sm text-slate-600 mb-4">
                        Browser mendeteksi aplikasi PWA. Klik tombol di bawah atau ikuti langkah manual.
                    </p>
                    <button id="install-btn-windows"
                        class="w-full rounded-xl bg-red-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-red-700">
                        Install Sekarang
                    </button>
                    <div class="mt-3 rounded-lg bg-stone-50 p-3 text-xs text-stone-600">
                        Atau: Klik ikon <strong>... (More)</strong> di address bar → <strong>"Install EWS"</strong>.
                    </div>
                </div>
            </div>

            {{-- Windows - fallback manual --}}
            <div id="windows-edge-manual" class="hidden space-y-3">
                <div class="rounded-xl border border-lime-200 bg-white p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="text-2xl">🖥️</span>
                        <h2 class="font-bold text-slate-900">Windows - Chrome atau Edge</h2>
                    </div>
                    <p class="text-sm text-slate-600 mb-4">
                        Ikuti langkah berikut untuk menginstall aplikasi EWS:
                    </p>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 rounded-lg bg-lime-50 p-3">
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-lime-600 text-xs font-bold text-white">
                                1
                            </div>
                            <p class="text-sm text-slate-700">Klik ikon <strong>... (More)</strong> di pojok kanan atas
                                address bar.</p>
                        </div>
                        <div class="flex items-center gap-3 rounded-lg bg-lime-50 p-3">
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-lime-600 text-xs font-bold text-white">
                                2
                            </div>
                            <p class="text-sm text-slate-700">Pilih <strong>"Install EWS"</strong> atau
                                <strong>"Apps"</strong> → <strong>"Install this site as an app"</strong>.</p>
                        </div>
                        <div class="flex items-center gap-3 rounded-lg bg-lime-50 p-3">
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-lime-600 text-xs font-bold text-white">
                                3
                            </div>
                            <p class="text-sm text-slate-700">Klik <strong>"Install"</strong> di dialog yang muncul.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- iOS Safari - selalu manual --}}
            <div id="ios-safari" class="hidden space-y-3">
                <div class="rounded-xl border border-lime-200 bg-white p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="text-2xl">🍎</span>
                        <h2 class="font-bold text-slate-900">iPhone / iPad - Safari</h2>
                    </div>
                    <p class="text-sm text-slate-600 mb-4">
                        Safari di iOS tidak mendukung instalasi PWA melalui tombol otomatis, tetapi Anda tetap bisa
                        menambahkan ke layar utama.
                    </p>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 rounded-lg bg-lime-50 p-3">
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-lime-600 text-xs font-bold text-white">
                                1
                            </div>
                            <p class="text-sm text-slate-700">Pastikan browser yang digunakan adalah
                                <strong>Safari</strong>.</p>
                        </div>
                        <div class="flex items-center gap-3 rounded-lg bg-lime-50 p-3">
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-lime-600 text-xs font-bold text-white">
                                2
                            </div>
                            <p class="text-sm text-slate-700">Tap ikon <strong>Share (Kotak dengan Panah)</strong> di
                                bagian bawah toolbar Safari.</p>
                        </div>
                        <div class="flex items-center gap-3 rounded-lg bg-lime-50 p-3">
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-lime-600 text-xs font-bold text-white">
                                3
                            </div>
                            <p class="text-sm text-slate-700">Gulir ke bawah dan pilih <strong>"Add to Home
                                    Screen"</strong>.</p>
                        </div>
                        <div class="flex items-center gap-3 rounded-lg bg-lime-50 p-3">
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-lime-600 text-xs font-bold text-white">
                                4
                            </div>
                            <p class="text-sm text-slate-700">Klik <strong>"Add"</strong> untuk menyelesaikan.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Fallback umum untuk browser lain --}}
            <div id="general-browser" class="hidden space-y-3">
                <div class="rounded-xl border border-lime-200 bg-white p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="text-2xl">🌐</span>
                        <h2 class="font-bold text-slate-900">Browser Lainnya</h2>
                    </div>
                    <p class="text-sm text-slate-600 mb-2">
                        Untuk browser lain yang mendukung PWA:
                    </p>
                    <ul class="space-y-2 text-sm text-slate-700">
                        <li class="flex items-start gap-2">
                            <i class="ti ti-check text-base text-lime-600 mt-0.5"></i>
                            <span>Menu browser → "Install App" atau "Add to Home Screen"</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="ti ti-check text-base text-lime-600 mt-0.5"></i>
                            <span>Menu browser → Apps / Install</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="ti ti-check text-base text-lime-600 mt-0.5"></i>
                            <span>Address bar → Install icon</span>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </main>

    <script>
        (function() {
            const ua = navigator.userAgent.toLowerCase();
            const isIOS = /iphone|ipad|ipod/.test(ua);
            const isAndroid = /android/.test(ua);
            const isWindows = /windows/.test(ua);
            const isChromeOrEdge = /chrome|edg/.test(ua) && !/opr|brave/.test(ua);

            let deferredPrompt = null;

            const sections = {
                success: document.getElementById('install-success'),
                androidPrompt: document.getElementById('android-chrome-prompt'),
                androidManual: document.getElementById('android-chrome-manual'),
                windowsPrompt: document.getElementById('windows-edge-prompt'),
                windowsManual: document.getElementById('windows-edge-manual'),
                ios: document.getElementById('ios-safari'),
                general: document.getElementById('general-browser'),
            };

            function hideAll() {
                Object.values(sections).forEach(el => el && el.classList.add('hidden'));
            }

            function show(el) {
                if (el) el.classList.remove('hidden');
            }

            // Sudah terinstall sebagai PWA (standalone mode)?
            const isStandalone = window.matchMedia('(display-mode: standalone)').matches ||
                window.navigator.standalone === true;

            if (isStandalone) {
                hideAll();
                show(sections.success);
                return;
            }

            // Render fallback manual dulu berdasarkan device/browser.
            // Ini akan di-upgrade otomatis ke versi "prompt" kalau
            // event beforeinstallprompt sempat fire (Chrome/Edge Android & Desktop).
            function renderFallback() {
                hideAll();
                if (isIOS) {
                    show(sections.ios);
                } else if (isAndroid) {
                    show(sections.androidManual);
                } else if (isWindows && isChromeOrEdge) {
                    show(sections.windowsManual);
                } else {
                    show(sections.general);
                }
            }

            renderFallback();

            // Browser support native install prompt
            window.addEventListener('beforeinstallprompt', function(e) {
                e.preventDefault();
                deferredPrompt = e;

                hideAll();
                if (isAndroid) {
                    show(sections.androidPrompt);
                } else if (isWindows) {
                    show(sections.windowsPrompt);
                } else {
                    show(sections.general);
                }
            });

            window.addEventListener('appinstalled', function() {
                hideAll();
                show(sections.success);
                deferredPrompt = null;
            });

            async function triggerInstall() {
                if (!deferredPrompt) return;
                deferredPrompt.prompt();
                const {
                    outcome
                } = await deferredPrompt.userChoice;
                if (outcome === 'accepted') {
                    hideAll();
                    show(sections.success);
                }
                deferredPrompt = null;
            }

            const btnAndroid = document.getElementById('install-btn-android');
            const btnWindows = document.getElementById('install-btn-windows');
            if (btnAndroid) btnAndroid.addEventListener('click', triggerInstall);
            if (btnWindows) btnWindows.addEventListener('click', triggerInstall);
        })();
    </script>
</body>

</html>
