@extends('layouts.app')
@section('title', 'Absensi QR')
@section('content')
    <div class="mx-auto max-w-2xl px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Absensi QR</h1>
            <p class="mt-2 text-gray-600">Pindai QR code dari guru piket atau gunakan kamera untuk scan barcode</p>
        </div>

        {{-- Success Message --}}
        @if (session('success'))
            <div
                class="mb-6 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-4 text-sm text-green-800">
                <svg class="h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                <div>
                    <p class="font-semibold">Absensi Berhasil!</p>
                    <p class="mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        {{-- Error Message --}}
        @if (session('error'))
            <div
                class="mb-6 flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-800">
                <svg class="h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd" />
                </svg>
                <div>
                    <p class="font-semibold">Absensi Gagal</p>
                    <p class="mt-0.5">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <div class="grid gap-6 md:grid-cols-2">
            {{-- QR Scanner Card --}}
            <div class="rounded-lg border border-gray-200 bg-white p-6">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">Scan QR Code</h2>

                <div id="scanner" class="mb-4 rounded-lg bg-gray-100 p-4">
                    <video id="qr-video" class="w-full rounded-lg" style="max-height: 300px;" autoplay playsinline
                        muted></video>
                </div>

                <div class="mb-4 rounded-lg bg-blue-50 p-4" id="scanner-status">
                    <p class="text-sm text-blue-800">🔄 Preparing camera...</p>
                </div>

                <div class="space-y-2">
                    <button type="button" id="start-scanner"
                        class="w-full flex items-center justify-center gap-2 rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
                            </path>
                        </svg>
                        Mulai Scan
                    </button>

                    <button type="button" id="stop-scanner"
                        class="w-full rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 disabled:opacity-50"
                        disabled>
                        Hentikan Scan
                    </button>

                    <button type="button" id="scan-again-button"
                        class="w-full hidden rounded-lg bg-green-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-800"
                        onclick="resetForNextScan()">
                        Scan Lagi (Check-out)
                    </button>
                </div>

                {{-- Fallback Button --}}
                <div class="mt-4 border-t pt-4">
                    <p class="mb-3 text-xs text-gray-600">💡 Tidak punya kamera? Gunakan tombol fallback:</p>
                    <button type="button" id="testing-button"
                        class="w-full rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-800"
                        onclick="handleTestingButton()">
                        Proses Absensi (Testing)
                    </button>
                </div>
            </div>

            {{-- Info Card --}}
            <div class="space-y-4">
                {{-- Data Siswa --}}
                @if (auth()->user()->siswa)
                    <div class="rounded-lg border border-gray-200 bg-white p-6">
                        <h3 class="mb-4 font-semibold text-gray-900">Data Siswa</h3>
                        <div class="space-y-3 text-sm">
                            <div>
                                <p class="text-gray-600">Nama</p>
                                <p class="mt-1 font-medium text-gray-900">
                                    {{ auth()->user()->siswa->nama ?? auth()->user()->name }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">NIS</p>
                                <p class="mt-1 font-medium text-gray-900">{{ auth()->user()->siswa->nis ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Kelas</p>
                                <p class="mt-1 font-medium text-gray-900">
                                    {{ auth()->user()->siswa->kelas->nama_kelas ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Status Absensi Hari Ini --}}
                    @php
                        $todayAbsensi = \App\Models\Absensi::where('siswa_id', auth()->user()->siswa->id)
                            ->where('tanggal', now()->toDateString())
                            ->where('tipe', 'harian')
                            ->first();
                    @endphp

                    @if ($todayAbsensi)
                        <div class="rounded-lg border-2 border-blue-200 bg-blue-50 p-4"
                            data-absensi-status="{{ $todayAbsensi->jam_pulang ? 'complete' : 'checkin-only' }}">
                            <p class="text-sm font-semibold text-blue-900">📋 Status Absensi Hari Ini:</p>
                            <div class="mt-3 space-y-2 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-blue-700">✅ Check-in:</span>
                                    <span
                                        class="font-semibold text-blue-900">{{ $todayAbsensi->jam_masuk ? $todayAbsensi->jam_masuk->format('H:i:s') : '-' }}</span>
                                </div>
                                @if ($todayAbsensi->jam_pulang)
                                    <div class="flex items-center justify-between">
                                        <span class="text-blue-700">✅ Check-out:</span>
                                        <span
                                            class="font-semibold text-blue-900">{{ $todayAbsensi->jam_pulang->format('H:i:s') }}</span>
                                    </div>
                                    <div class="border-t border-blue-200 pt-2 mt-2">
                                        <p class="text-center font-semibold text-green-700">✓ Absensi Selesai</p>
                                    </div>
                                @else
                                    <div class="border-t border-blue-200 pt-2 mt-2">
                                        <p class="rounded bg-amber-100 p-2 text-center font-semibold text-amber-700">⏰
                                            Menunggu Check-out</p>
                                        <p class="mt-2 text-xs text-blue-700 text-center">Scan QR lagi nanti untuk check-out
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Petunjuk Penggunaan --}}
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                        <p class="text-xs font-semibold text-amber-900">⚠️ Cara Menggunakan:</p>
                        <ul class="mt-2 space-y-1 text-xs text-amber-800">
                            <li>1. Klik tombol "Mulai Scan"</li>
                            <li>2. Arahkan kamera ke QR code guru piket</li>
                            <li>3. QR code akan terbaca otomatis → Check-in tercatat</li>
                            <li>4. <strong>SCAN LAGI</strong> nanti saat pulang untuk check-out</li>
                        </ul>
                    </div>

                    {{-- Workflow Info --}}
                    <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
                        <p class="text-xs font-semibold text-blue-900">ℹ️ Workflow Check-in & Check-out:</p>
                        <ul class="mt-2 space-y-1 text-xs text-blue-800">
                            <li><strong>🔵 PAGI (Check-in):</strong> Scan 1x → jam masuk tercatat</li>
                            <li><strong>🟢 SORE (Check-out):</strong> Scan lagi → jam pulang tercatat</li>
                            <li><strong>📍 Lihat status absensi Anda di kartu di atas</strong></li>
                            <li>Gunakan DEVICE YANG SAMA saat scan</li>
                        </ul>
                    </div>

                    {{-- QR Update Info --}}
                    <div class="rounded-lg border border-cyan-200 bg-cyan-50 p-4">
                        <p class="text-xs font-semibold text-cyan-900">🔄 Jika QR Berubah:</p>
                        <ul class="mt-2 space-y-1 text-xs text-cyan-800">
                            <li>Guru piket sering generate QR baru (cegah cheating)</li>
                            <li><strong>✓ Tetap bisa checkout</strong> dengan QR terbaru</li>
                            <li>Scan dengan device yang sama saat check-in</li>
                        </ul>
                    </div>
                @else
                    <div class="rounded-lg border border-red-200 bg-red-50 p-4">
                        <p class="text-xs font-semibold text-red-900">❌ Data Tidak Terhubung</p>
                        <p class="mt-2 text-xs text-red-800">Akun Anda belum terhubung dengan data siswa. Silakan hubungi
                            admin untuk verifikasi data akun Anda.</p>
                        <p class="mt-2 text-xs text-red-700"><strong>Email Anda:</strong> {{ auth()->user()->email }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- QR Code Scanner Library --}}
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>

    <script>
        let scanner = null;
        let isScanning = false;
        let hasProcessed = false;

        const startButton = document.getElementById('start-scanner');
        const stopButton = document.getElementById('stop-scanner');
        const videoElement = document.getElementById('qr-video');
        const statusElement = document.getElementById('scanner-status');

        // Generate device fingerprint dari browser UA + screen res
        function generateDeviceId() {
            const ua = navigator.userAgent;
            const screen_res = window.screen.width + 'x' + window.screen.height;
            const fingerprint = ua + screen_res;

            // Simple hash function (not cryptographically secure, but good enough for device ID)
            let hash = 0;
            for (let i = 0; i < fingerprint.length; i++) {
                const char = fingerprint.charCodeAt(i);
                hash = ((hash << 5) - hash) + char;
                hash = hash & hash; // Convert to 32bit integer
            }
            return Math.abs(hash).toString(36);
        }

        // Get GPS location from browser
        async function getGPSLocation() {
            return new Promise((resolve, reject) => {
                if (!navigator.geolocation) {
                    reject(new Error('Geolocation not supported'));
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        resolve({
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude,
                            accuracy: position.coords.accuracy
                        });
                    },
                    (error) => {
                        reject(error);
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            });
        }

        function setStatus(message, type = 'info') {
            const colors = {
                'info': 'bg-blue-50 text-blue-800',
                'success': 'bg-green-50 text-green-800',
                'error': 'bg-red-50 text-red-800',
                'warning': 'bg-yellow-50 text-yellow-800',
            };
            statusElement.className =
                `mb-4 rounded-lg p-4 text-sm font-medium border ${type === 'info' ? 'border-blue-200 ' : type === 'success' ? 'border-green-200 ' : type === 'error' ? 'border-red-200 ' : 'border-yellow-200 '}${colors[type]}`;
            statusElement.textContent = message;
        }

        async function startScanner() {
            try {
                // Detect if using HTTP and suggest HTTPS or localhost
                const isSecure = window.location.protocol === 'https:' || window.location.hostname ===
                    'localhost' || window.location.hostname === '127.0.0.1';

                // Cek apakah getUserMedia tersedia
                const hasGetUserMedia = navigator.mediaDevices && navigator.mediaDevices.getUserMedia;

                if (!hasGetUserMedia) {
                    setStatus('❌ Browser Anda tidak mendukung akses kamera', 'error');
                    return;
                }

                if (!isSecure && !window.location.hostname.includes('192.168') && !window.location.hostname
                    .includes('10.0')) {
                    setStatus(
                        '⚠️ Akses kamera hanya tersedia di HTTPS atau localhost. Browser menolak HTTP non-secure',
                        'error');
                    return;
                }

                setStatus('⏳ Meminta akses kamera belakang...', 'info');

                // Try dengan back camera dulu (environment), fallback ke any camera
                let stream;
                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: {
                                ideal: "environment"
                            } // Back camera
                        },
                        audio: false
                    });
                } catch (e) {
                    // Fallback ke camera any
                    console.warn('Back camera not available, using default');
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: true,
                        audio: false
                    });
                }

                videoElement.srcObject = stream;
                videoElement.onloadedmetadata = () => {
                    videoElement.play();
                };

                isScanning = true;
                startButton.disabled = true;
                stopButton.disabled = false;

                setStatus('📱 Kamera aktif - arahkan ke QR code', 'success');

                // Start scanning
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');

                function scanFrame() {
                    if (!isScanning) return;

                    if (videoElement.readyState === videoElement.HAVE_ENOUGH_DATA) {
                        try {
                            canvas.width = videoElement.videoWidth;
                            canvas.height = videoElement.videoHeight;
                            ctx.drawImage(videoElement, 0, 0, canvas.width, canvas.height);

                            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                            const code = jsQR(imageData.data, imageData.width, imageData.height, {
                                inversionAttempts: 2,
                            });

                            if (code) {
                                handleQRCode(code.data);
                                return;
                            }
                        } catch (scanError) {
                            console.error('Scan frame error:', scanError);
                        }
                    }

                    requestAnimationFrame(scanFrame);
                }

                scanFrame();
            } catch (error) {
                let errorMsg = 'Tidak bisa mengakses kamera';

                if (error.name === 'NotAllowedError') {
                    errorMsg = 'Akses kamera ditolak. Cek izin kamera di browser → setelan situs';
                } else if (error.name === 'NotFoundError') {
                    errorMsg = 'Perangkat Anda tidak memiliki kamera';
                } else if (error.name === 'NotReadableError') {
                    errorMsg = 'Kamera sudah dipakai app lain. Tutup aplikasi lain dulu';
                } else if (error.name === 'SecurityError' || error.message.includes('secure')) {
                    errorMsg = 'Browser: Akses kamera harus via HTTPS. Untuk test, ganti ke localhost:8000';
                } else if (error.message) {
                    errorMsg = error.message;
                }

                setStatus('❌ ' + errorMsg, 'error');
                console.error('Camera init error:', error);
                console.log('Hostname:', window.location.hostname);
                console.log('Protocol:', window.location.protocol);
            }
        }

        startButton.addEventListener('click', () => {
            startScanner();
        });

        stopButton.addEventListener('click', () => {
            isScanning = false;
            if (videoElement.srcObject) {
                const tracks = videoElement.srcObject.getTracks();
                tracks.forEach(track => {
                    track.stop();
                    track.enabled = false;
                });
                videoElement.srcObject = null;
            }
            startButton.disabled = false;
            stopButton.disabled = true;
            hasProcessed = false;
            setStatus('⏹️ Scanner dihentikan', 'warning');
        });

        function handleQRCode(data) {
            // Prevent double processing
            if (hasProcessed) return;
            hasProcessed = true;

            isScanning = false;
            if (videoElement.srcObject) {
                const tracks = videoElement.srcObject.getTracks();
                tracks.forEach(track => {
                    track.stop();
                    track.enabled = false;
                });
                videoElement.srcObject = null;
            }
            startButton.disabled = false;
            stopButton.disabled = true;

            setStatus('✅ QR code terdeteksi! Mengumpulkan data lokasi...', 'success');

            // Get GPS location
            getGPSLocation()
                .then(gps => {
                    setStatus('✅ Lokasi diterima. Sedang memproses absensi...', 'success');

                    const deviceId = generateDeviceId();
                    const payload = {
                        qr_code: data,
                        latitude: gps.latitude,
                        longitude: gps.longitude,
                        accuracy: gps.accuracy,
                        device_id: deviceId
                    };

                    // Submit via AJAX (bukan form submit) untuk avoid logout
                    setTimeout(() => {
                        submitAbsensi(payload);
                    }, 500);
                })
                .catch(error => {
                    console.error('GPS error:', error);
                    setStatus('❌ Tidak bisa mendapatkan lokasi GPS: ' + error.message, 'error');
                    hasProcessed = false;
                    startButton.disabled = false;
                });
        }

        // Submit attendance via AJAX
        function submitAbsensi(payload) {
            fetch('{{ route('siswa.qr.process') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content'),
                    },
                    body: JSON.stringify(payload)
                })
                .then(response => {
                    // Check if response is actually JSON
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        return response.text().then(text => {
                            throw new Error('Server error: Expected JSON response, got ' + (contentType ||
                                'unknown') + '. ' + text.slice(0, 120));
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Show different message based on status
                        let message = data.message;
                        if (data.status === 'checkin') {
                            message = '✅ Check-in berhasil! Jam masuk: ' + data.jam_masuk +
                                '\n\n⏰ Ingat: Scan lagi nanti untuk check-out (jam pulang).';
                            setStatus(message, 'success');

                            // Show "Scan Lagi" button untuk checkout
                            const scanAgainBtn = document.getElementById('scan-again-button');
                            if (scanAgainBtn) {
                                scanAgainBtn.classList.remove('hidden');
                            }

                            // Jangan reload page, user bisa scan lagi nanti
                            // Refresh page setelah 5 detik untuk update status card dari database
                            setTimeout(() => {
                                window.location.reload();
                            }, 5000);
                        } else if (data.status === 'checkout') {
                            message = '✅ Check-out berhasil! Jam pulang: ' + data.jam_pulang +
                                '\n\n✓ Absensi hari ini selesai.';
                            setStatus(message, 'success');

                            // Hide "Scan Lagi" button
                            const scanAgainBtn = document.getElementById('scan-again-button');
                            if (scanAgainBtn) {
                                scanAgainBtn.classList.add('hidden');
                            }

                            // Reload page after checkout (final step)
                            setTimeout(() => {
                                window.location.reload();
                            }, 3000);
                        }
                    } else {
                        setStatus('❌ ' + data.message, 'error');
                        hasProcessed = false;
                        // Re-enable scanning
                        startButton.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Absensi error:', error);
                    let errorMsg = error.message || 'Gagal memproses absensi';

                    // Better error messages
                    if (errorMsg.includes('JSON')) {
                        errorMsg = 'Server error: Response format tidak valid. Hubungi admin.';
                    } else if (errorMsg.includes('Failed to fetch')) {
                        errorMsg = 'Koneksi ke server gagal. Cek internet Anda.';
                    }

                    setStatus('❌ ' + errorMsg, 'error');
                    hasProcessed = false;
                    startButton.disabled = false;
                });
        }

        // Initialize on page load - AUTO START CAMERA
        window.addEventListener('load', async () => {
            setStatus('🎥 Auto-loading kamera...', 'info');
            // Cek apakah sudah ada check-in (lihat dari status card)
            const todayAbsensi = document.querySelector('[data-absensi-status]');
            if (todayAbsensi && !todayAbsensi.dataset.absensiStatus.includes('none')) {
                // Sudah ada absensi, show "Scan Lagi" button untuk checkout
                const scanAgainBtn = document.getElementById('scan-again-button');
                if (scanAgainBtn) {
                    scanAgainBtn.classList.remove('hidden');
                }
                setStatus('✅ Check-in sudah tercatat. Siap untuk check-out.', 'success');
            } else {
                // Belum ada absensi, auto-load camera untuk check-in
                await startScanner();
            }
        });

        // Reset untuk scan lagi (checkout)
        function resetForNextScan() {
            hasProcessed = false;
            setStatus('🎥 Ready untuk check-out. Mulai scan...', 'info');
            startScanner();
        }

        // Handle testing button (fallback untuk no camera)
        function handleTestingButton() {
            if (hasProcessed) {
                setStatus('⚠️ Sedang memproses... Tunggu sebentar', 'warning');
                return;
            }

            setStatus('⏳ Mengumpulkan data lokasi...', 'info');

            // Get GPS location
            getGPSLocation()
                .then(gps => {
                    setStatus('⏳ Sedang memproses absensi...', 'info');

                    const deviceId = generateDeviceId();
                    const payload = {
                        latitude: gps.latitude,
                        longitude: gps.longitude,
                        accuracy: gps.accuracy,
                        device_id: deviceId
                    };

                    submitAbsensi(payload);
                })
                .catch(error => {
                    console.error('GPS error:', error);
                    setStatus('❌ Tidak bisa mendapatkan lokasi GPS: ' + error.message, 'error');
                });
        }
    </script>
@endsection
