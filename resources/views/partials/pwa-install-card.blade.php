<div id="pwaInstallCard" class="hidden rounded-xl border border-lime-200 bg-lime-50 p-5">
    <div class="flex items-start gap-4">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-lime-600 text-white">
            <i class="ti ti-device-mobile-down text-lg"></i>
        </div>

        <div class="flex-1">
            <div class="flex items-start justify-between gap-2">
                <h3 class="text-sm font-bold text-slate-900">Install Aplikasi EWS</h3>
                <button type="button" id="pwaDismissBtn"
                    class="shrink-0 rounded-md p-1 text-slate-400 transition hover:bg-lime-100 hover:text-slate-600"
                    aria-label="Tutup">
                    <i class="ti ti-x text-sm"></i>
                </button>
            </div>

            <p class="mt-1 text-xs text-slate-600">
                Akses lebih cepat dan praktis langsung dari perangkat Anda.
            </p>

            <div class="mt-3 flex flex-wrap items-center gap-2">
                <!-- Muncul hanya kalau browser support native install prompt -->
                <button type="button" id="pwaInstallBtn"
                    class="hidden inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-red-700">
                    <i class="ti ti-download text-sm"></i>
                    Install Sekarang
                </button>

                <a href="{{ route('install') }}" id="pwaTutorialBtn"
                    class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-red-700">
                    <i class="ti ti-help text-sm"></i>
                    <span id="pwaTutorialLabel">Cara Install</span>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        const card = document.getElementById('pwaInstallCard');
        const installBtn = document.getElementById('pwaInstallBtn');
        const tutorialBtn = document.getElementById('pwaTutorialBtn');
        const tutorialLabel = document.getElementById('pwaTutorialLabel');
        const dismissBtn = document.getElementById('pwaDismissBtn');
        let deferredPrompt = null;

        // Sudah di-dismiss sebelumnya?
        if (localStorage.getItem('pwa_install_dismissed') === '1') {
            return;
        }

        // Sudah jalan sebagai PWA (standalone)?
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches ||
            window.navigator.standalone === true;
        if (isStandalone) {
            return;
        }

        const isIOS = /iphone|ipad|ipod/i.test(navigator.userAgent);

        // Tampilkan card
        card.classList.remove('hidden');

        if (isIOS) {
            tutorialLabel.textContent = 'Cara Install di iPhone/iPad';
        }

        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            deferredPrompt = e;
            installBtn.classList.remove('hidden');
            // Kalau tombol install native muncul, tombol tutorial jadi secondary
            tutorialBtn.classList.remove('bg-red-600', 'text-white', 'hover:bg-red-700');
            tutorialBtn.classList.add('border', 'border-lime-300', 'bg-white', 'text-lime-700',
                'hover:bg-lime-100');
        });

        window.addEventListener('appinstalled', function() {
            card.classList.add('hidden');
            deferredPrompt = null;
        });

        installBtn.addEventListener('click', async function() {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            const {
                outcome
            } = await deferredPrompt.userChoice;
            if (outcome === 'accepted') {
                card.classList.add('hidden');
            }
            deferredPrompt = null;
            installBtn.classList.add('hidden');
        });

        dismissBtn.addEventListener('click', function() {
            card.classList.add('hidden');
            localStorage.setItem('pwa_install_dismissed', '1');
        });
    })();
</script>
