@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-8">
        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Generate QR Absensi Harian</h1>
                <p class="mt-2 text-gray-600">Buat QR code untuk siswa melakukan absensi</p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                @if (!empty($qrSessions) && count($qrSessions) > 0)
                    <form action="{{ route('guru_piket.qr.reset') }}" method="POST"
                        onsubmit="return confirm('Reset session QR?')">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                            Reset Session
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div
                class="mb-6 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                <svg class="h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <p class="font-semibold">Terjadi kesalahan:</p>
                <ul class="mt-1 list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-1">
                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Pengaturan Absensi</h2>

                    <form action="{{ route('guru_piket.qr.generate') }}" method="POST"
                        onsubmit="return beforeSubmitQRForm(event)">
                        @csrf

                        <div class="mb-6">
                            <label class="mb-2 block text-sm font-semibold text-gray-700">Jenis Absensi</label>
                            <div class="flex gap-4 space-y-2">
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="tipe" value="masuk"
                                        class="cursor-pointer border-gray-300"
                                        {{ request('tipe', 'masuk') == 'masuk' ? 'checked' : '' }}
                                        onchange="toggleJamMaksimal()">
                                    <span class="text-sm text-gray-700">Masuk (CHECK-IN)</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="tipe" value="pulang"
                                        class="cursor-pointer border-gray-300"
                                        {{ request('tipe') == 'pulang' ? 'checked' : '' }} onchange="toggleJamMaksimal()">
                                    <span class="text-sm text-gray-700">Pulang (CHECK-OUT)</span>
                                </label>
                            </div>
                            @error('tipe')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @include('partials.date-time', [
                            'dateInput' => [
                                'name' => 'tanggal',
                                'label' => 'Tanggal Berlaku',
                                'value' => request('tanggal', now()->toDateString()),
                                'theme' => 'blue',
                                'required' => true,
                                'help' => 'Pilih tanggal absensi berlaku',
                            ],
                        ])

                        @include('partials.date-time', [
                            'timeInput' => [
                                'name' => 'jam_batas',
                                'label' => 'Jam Batas',
                                'value' => request('jam_batas', '07:00'),
                                'theme' => 'blue',
                                'step' => '300',
                                'required' => true,
                                'help' => 'Masuk: Threshold untuk terlambat | Pulang: Jam mulai checkout',
                                'id' => 'jamBatasInput',
                            ],
                        ])

                        <div class="mb-6" id="jamMaksimalDiv"
                            style="{{ request('tipe') != 'pulang' ? '' : 'display: none;' }}">
                            @php
                                $jamMaksimalValue = request('jam_maksimal', '08:00');
                                [$jamVal, $menitVal] = explode(':', $jamMaksimalValue);
                            @endphp
                            <label class="block text-sm font-semibold text-gray-700">Jam Maksimal Check-In (Hard
                                Deadline)</label>

                            <input type="hidden" id="jam_maksimal" name="jam_maksimal" value="{{ $jamMaksimalValue }}" />

                            <div class="mt-1.5 flex items-center gap-3">
                                <div class="flex-1">
                                    <label class="text-xs font-semibold text-gray-600">Jam</label>
                                    <input type="number" id="jam_maksimal_jam" min="0" max="23" step="1"
                                        value="{{ $jamVal }}"
                                        class="w-full rounded-lg border border-gray-300 px-2 py-2 text-sm font-semibold focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        onchange="updateJamMaksimalDisplay()" oninput="updateJamMaksimalDisplay()" />
                                </div>

                                <div class="mt-5 text-xl font-bold text-gray-400">:</div>

                                <div class="flex-1">
                                    <label class="text-xs font-semibold text-gray-600">Menit</label>
                                    <input type="number" id="jam_maksimal_menit" min="0" max="59"
                                        step="1" value="{{ $menitVal }}"
                                        class="w-full rounded-lg border border-gray-300 px-2 py-2 text-sm font-semibold focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        onchange="updateJamMaksimalDisplay()" oninput="updateJamMaksimalDisplay()" />
                                </div>

                                <div class="mt-5">
                                    <div class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-900"
                                        id="jam_maksimal_display">{{ $jamMaksimalValue }}</div>
                                </div>
                            </div>

                            <script>
                                function updateJamMaksimalDisplay() {
                                    const jamField = document.getElementById('jam_maksimal_jam');
                                    const menitField = document.getElementById('jam_maksimal_menit');
                                    const displayField = document.getElementById('jam_maksimal_display');

                                    let jam = parseInt(jamField.value) || 0;
                                    let menit = parseInt(menitField.value) || 0;

                                    jam = Math.max(0, Math.min(23, jam));
                                    menit = Math.max(0, Math.min(59, menit));

                                    const timeValue = String(jam).padStart(2, '0') + ':' + String(menit).padStart(2, '0');
                                    displayField.textContent = timeValue;
                                }
                            </script>

                            <p class="mt-1 text-xs text-gray-500">Siswa tidak bisa absen setelah jam ini (untuk tipe masuk)
                            </p>
                            @error('jam_maksimal')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                            class="w-full rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700">
                            Generate QR Code
                        </button>
                    </form>

                    <script>
                        function beforeSubmitQRForm(event) {
                            event.preventDefault();

                            const jamBatasJam = document.getElementById('jam_batas_jam');
                            const jamBatasMenit = document.getElementById('jam_batas_menit');
                            const jamBatasHidden = document.getElementById('jam_batas');

                            if (jamBatasJam && jamBatasMenit && jamBatasHidden) {
                                let jam = parseInt(jamBatasJam.value) || 0;
                                let menit = parseInt(jamBatasMenit.value) || 0;
                                jam = Math.max(0, Math.min(23, jam));
                                menit = Math.max(0, Math.min(59, menit));
                                jamBatasHidden.value = String(jam).padStart(2, '0') + ':' + String(menit).padStart(2, '0');
                            }

                            const jamMaksimalJam = document.getElementById('jam_maksimal_jam');
                            const jamMaksimalMenit = document.getElementById('jam_maksimal_menit');
                            const jamMaksimalHidden = document.getElementById('jam_maksimal');

                            if (jamMaksimalJam && jamMaksimalMenit && jamMaksimalHidden) {
                                let jam = parseInt(jamMaksimalJam.value) || 0;
                                let menit = parseInt(jamMaksimalMenit.value) || 0;
                                jam = Math.max(0, Math.min(23, jam));
                                menit = Math.max(0, Math.min(59, menit));
                                jamMaksimalHidden.value = String(jam).padStart(2, '0') + ':' + String(menit).padStart(2, '0');
                            }

                            event.target.submit();
                        }
                    </script>

                    <div class="mt-4 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2.5 text-xs text-blue-900">
                        <p class="font-semibold">ℹ️ Tips Penggunaan:</p>
                        <ul class="mt-1 list-disc space-y-1 pl-4">
                            <li>Set jam mulai sesuai jadwal sekolah</li>
                            <li>Durasi minimal 5 menit, max 24 jam</li>
                            <li>QR akan otomatis expire setelah durasi</li>
                        </ul>
                    </div>

                    <div class="mt-4 rounded-lg border border-purple-200 bg-purple-50 px-3 py-2.5 text-xs text-purple-900">
                        <p class="font-semibold">🔄 Tentang Regenerate QR:</p>
                        <ul class="mt-1 list-disc space-y-1 pl-4">
                            <li>Boleh di-generate berkali-kali untuk prevent cheating</li>
                            <li>Siswa tetap bisa checkout meski QR berubah</li>
                            <li>Checkout tetap berfungsi dengan QR terbaru</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6">
                @php
                    $currentQRSession = $qrSession ?? collect($qrSessions)->first();
                @endphp

                @if ($currentQRSession)
                    <div class="rounded-lg border-2 border-green-200 bg-gradient-to-r from-green-50 to-emerald-50 p-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-200">
                                <svg class="h-6 w-6 text-green-700" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-bold text-green-900">QR ABSEN MASUK (CHECK-IN)</p>
                                <p class="text-sm text-green-800">Siswa scan 1x untuk check-in, scan lagi nanti untuk
                                    check-out</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-3 md:grid-cols-3">
                        <div class="rounded-lg border border-gray-200 bg-white p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total Siswa</p>
                            <p class="mt-1 text-2xl font-bold text-gray-900">{{ $totalStudents ?? 0 }}</p>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-white p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Sudah Absen</p>
                            <p class="mt-1 text-2xl font-bold text-green-600">0</p>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-white p-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Belum Absen</p>
                            <p class="mt-1 text-2xl font-bold text-orange-600">{{ $totalStudents ?? 0 }}</p>
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-6">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">QR Code</h3>

                        <div class="mb-4 flex justify-center rounded-lg bg-gray-50 p-6">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode($qrCodeUrl) }}"
                                alt="QR Code" class="rounded">
                        </div>

                        <div class="grid gap-3 text-center text-sm md:grid-cols-2">
                            <div class="rounded-lg border border-blue-200 bg-blue-50 p-3">
                                <p class="text-gray-600">Tanggal</p>
                                <p class="mt-1 font-semibold text-gray-900">
                                    {{ Carbon\Carbon::parse(data_get($currentQRSession, 'tanggal'))->format('d M Y') }}</p>
                            </div>
                            <div class="rounded-lg border border-blue-200 bg-blue-50 p-3">
                                <p class="text-gray-600">QR Di-generate</p>
                                <p class="mt-1 font-semibold text-gray-900">
                                    {{ Carbon\Carbon::parse(data_get($currentQRSession, 'generated_at'))->format('H:i') }}
                                </p>
                            </div>
                            <div class="rounded-lg border border-green-200 bg-green-50 p-3">
                                <p class="text-gray-600">Batas On-Time</p>
                                <p class="mt-1 font-semibold text-green-700">
                                    {{ Carbon\Carbon::parse(data_get($currentQRSession, 'jam_batas'))->format('H:i') }}</p>
                            </div>
                            <div class="rounded-lg border border-purple-200 bg-purple-50 p-3">
                                <p class="text-gray-600">Tipe</p>
                                <p class="mt-1 font-semibold text-gray-900">
                                    @if (data_get($currentQRSession, 'tipe') === 'masuk')
                                        <span class="rounded bg-blue-100 px-2 py-1 text-blue-700">Masuk (Check-In)</span>
                                    @else
                                        <span class="rounded bg-purple-100 px-2 py-1 text-purple-700">Pulang
                                            (Check-Out)</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div
                            class="mt-4 rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-xs text-yellow-900">
                            <p class="font-semibold">⚠️ Cara Kerja QR:</p>
                            <ul class="mt-2 list-disc space-y-1 pl-4">
                                @if (data_get($currentQRSession, 'tipe') === 'masuk')
                                    <li>QR berlaku jam
                                        {{ Carbon\Carbon::parse(data_get($currentQRSession, 'jam_batas'))->format('H:i') }}
                                        -
                                        {{ Carbon\Carbon::parse(data_get($currentQRSession, 'jam_maksimal'))->format('H:i') }}
                                    </li>
                                    <li>Siswa yg absen sebelum/saat jam
                                        <strong>{{ Carbon\Carbon::parse(data_get($currentQRSession, 'jam_batas'))->format('H:i') }}</strong>
                                        = <span class="font-semibold text-green-700">✓ HADIR</span>
                                    </li>
                                    <li>Siswa yg absen SETELAH jam
                                        <strong>{{ Carbon\Carbon::parse(data_get($currentQRSession, 'jam_batas'))->format('H:i') }}</strong>
                                        = <span class="font-semibold text-yellow-700">⏱ TERLAMBAT</span>
                                    </li>
                                    <li>Tidak bisa absen SETELAH jam
                                        <strong>{{ Carbon\Carbon::parse(data_get($currentQRSession, 'jam_maksimal'))->format('H:i') }}</strong>
                                        (hard deadline)
                                    </li>
                                @else
                                    <li>QR untuk checkout dimulai jam
                                        {{ Carbon\Carbon::parse(data_get($currentQRSession, 'jam_batas'))->format('H:i') }}
                                    </li>
                                    <li>Siswa hanya bisa checkout jika sudah check-in terlebih dahulu</li>
                                    <li>Durasi checkout berlaku sampai akhir hari</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                @else
                    <div
                        class="col-span-1 md:col-span-2 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <h3 class="mt-2 text-lg font-medium text-gray-900">Belum ada QR Code</h3>
                        <p class="mt-1 text-sm text-gray-500">Buat QR code terlebih dahulu dengan mengisi form di sebelah
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function toggleJamMaksimal() {
            const tipeRadios = document.getElementsByName('tipe');
            const jamMaksimalDiv = document.getElementById('jamMaksimalDiv');

            for (let radio of tipeRadios) {
                if (radio.value === 'masuk' && radio.checked) {
                    jamMaksimalDiv.style.display = '';
                    break;
                } else if (radio.value === 'pulang' && radio.checked) {
                    jamMaksimalDiv.style.display = 'none';
                    break;
                }
            }
        }
    </script>
@endsection
