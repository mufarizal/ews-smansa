@extends('layouts.app')
@section('title', $type === 'kegiatan' ? 'Edit Kegiatan Mingguan' : 'Edit Jadwal Pelajaran')

@section('content')

    <div class="mb-6">
        <a href="{{ route('kurikulum.jadwal.index', ['type' => $type]) }}"
            class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">
            {{ $type === 'kegiatan' ? 'Edit Kegiatan Mingguan' : 'Edit Jadwal Pelajaran' }}
        </h1>
        @if ($activeSemester)
            <p class="mt-1 text-sm text-gray-500">
                Semester aktif: <span class="font-medium text-green-700">{{ $activeSemester->nama }}</span>
            </p>
        @else
            <p class="mt-1 text-sm text-red-500">
                <i class="ti ti-alert-triangle align-text-bottom"></i>
                Belum ada semester aktif.
            </p>
        @endif
    </div>

    @if ($errors->any())
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-semibold mb-1">Terjadi kesalahan:</p>
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Tentukan ID dan route target berdasarkan type --}}
    @php
        $recordId = $type === 'kegiatan' ? $kegiatan->id : $jadwal->id;
    @endphp

    <form action="{{ route('kurikulum.jadwal.update', $recordId) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')
        <input type="hidden" name="type" value="{{ $type }}">

        {{-- Semester --}}
        <div class="rounded-lg border border-gray-200 bg-white">
            <div class="border-b border-gray-100 px-5 py-3.5">
                <h2 class="text-sm font-semibold text-gray-800">Semester</h2>
            </div>
            <div class="p-5">
                <label for="semester_id" class="block text-sm font-medium text-gray-700">
                    Semester <span class="text-red-500">*</span>
                </label>
                <select id="semester_id" name="semester_id" required
                    class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                           focus:border-green-600 focus:ring-2 focus:ring-green-100 sm:max-w-xs">
                    <option value="">-- Pilih Semester --</option>
                    @foreach ($semesterList as $sem)
                        @php $currentSemesterId = $type === 'kegiatan' ? $kegiatan->semester_id : $jadwal->semester_id; @endphp
                        <option value="{{ $sem->id }}"
                            {{ old('semester_id', $currentSemesterId) == $sem->id ? 'selected' : '' }}>
                            {{ $sem->nama }}{{ $sem->is_active ? ' (aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        @if ($type === 'pelajaran')
            {{-- ══════════════════════════════════════════════════ --}}
            {{-- FORM EDIT JADWAL PELAJARAN                        --}}
            {{-- ══════════════════════════════════════════════════ --}}

            {{-- Kelas, Mapel, Guru --}}
            <div class="rounded-lg border border-gray-200 bg-white">
                <div class="border-b border-gray-100 px-5 py-3.5">
                    <h2 class="text-sm font-semibold text-gray-800">Kelas, Mata Pelajaran & Guru</h2>
                    <p class="mt-0.5 text-xs text-gray-500">Pilih berurutan — guru akan terfilter otomatis sesuai kelas dan
                        mapel</p>
                </div>
                <div class="grid gap-4 p-5 sm:grid-cols-3">

                    <div>
                        <label for="kelas_id" class="block text-sm font-medium text-gray-700">
                            Kelas <span class="text-red-500">*</span>
                        </label>
                        <select id="kelas_id" name="kelas_id" required
                            class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                   focus:border-green-600 focus:ring-2 focus:ring-green-100">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach ($kelas as $item)
                                <option value="{{ $item->id }}"
                                    {{ old('kelas_id', $jadwal->kelas_id) == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="mapel_id" class="block text-sm font-medium text-gray-700">
                            Mata Pelajaran <span class="text-red-500">*</span>
                        </label>
                        <select id="mapel_id" name="mapel_id" required
                            class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                   focus:border-green-600 focus:ring-2 focus:ring-green-100">
                            <option value="">-- Pilih Mapel --</option>
                            @foreach ($mapel as $item)
                                <option value="{{ $item->id }}"
                                    {{ old('mapel_id', $jadwal->mapel_id) == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="guru_id" class="block text-sm font-medium text-gray-700">
                            Guru Pengajar <span class="text-red-500">*</span>
                        </label>
                        <select id="guru_id" name="guru_id" required
                            class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                   focus:border-green-600 focus:ring-2 focus:ring-green-100">
                            <option value="">-- Pilih Kelas & Mapel --</option>
                            @foreach ($guru as $item)
                                <option value="{{ $item->id }}"
                                    {{ old('guru_id', $jadwal->guru_id) == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama }}
                                </option>
                            @endforeach
                        </select>
                        <p id="guru_hint" class="mt-1 text-xs text-gray-400">Pilih kelas dan mapel terlebih dahulu</p>
                    </div>

                </div>
            </div>

            {{-- Hari & Jam --}}
            <div class="rounded-lg border border-gray-200 bg-white">
                <div class="border-b border-gray-100 px-5 py-3.5">
                    <h2 class="text-sm font-semibold text-gray-800">Hari & Jam Pelajaran</h2>
                    <p class="mt-0.5 text-xs text-gray-500">Jadwal berlaku setiap minggu selama semester aktif</p>
                </div>
                <div class="grid gap-4 p-5 sm:grid-cols-3">

                    <div>
                        <label for="hari" class="block text-sm font-medium text-gray-700">
                            Hari <span class="text-red-500">*</span>
                        </label>
                        <select id="hari" name="hari" required
                            class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                   focus:border-green-600 focus:ring-2 focus:ring-green-100">
                            <option value="">-- Pilih Hari --</option>
                            @foreach ($hariOptions as $hari)
                                <option value="{{ $hari }}"
                                    {{ old('hari', $jadwal->hari) === $hari ? 'selected' : '' }}>
                                    {{ $hari }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="jam_mulai" class="block text-sm font-medium text-gray-700">
                            Jam Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" id="jam_mulai" name="jam_mulai"
                            value="{{ old('jam_mulai', substr((string) $jadwal->jam_mulai, 0, 5)) }}" required
                            class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                   focus:border-green-600 focus:ring-2 focus:ring-green-100">
                    </div>

                    <div>
                        <label for="jam_selesai" class="block text-sm font-medium text-gray-700">
                            Jam Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" id="jam_selesai" name="jam_selesai"
                            value="{{ old('jam_selesai', substr((string) $jadwal->jam_selesai, 0, 5)) }}" required
                            class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                   focus:border-green-600 focus:ring-2 focus:ring-green-100">
                        <p id="durasi_info" class="mt-1 text-xs text-gray-400"></p>
                    </div>

                </div>
            </div>
        @else
            {{-- ══════════════════════════════════════════════════ --}}
            {{-- FORM EDIT KEGIATAN MINGGUAN                       --}}
            {{-- ══════════════════════════════════════════════════ --}}

            <div class="rounded-lg border border-purple-200 bg-purple-50 px-4 py-3 text-sm text-purple-800">
                <i class="ti ti-info-circle align-text-bottom mr-1"></i>
                Kegiatan mingguan dilaksanakan <strong>sebelum</strong> jam pelajaran dimulai.
            </div>

            {{-- Detail Kegiatan --}}
            <div class="rounded-lg border border-gray-200 bg-white">
                <div class="border-b border-gray-100 px-5 py-3.5">
                    <h2 class="text-sm font-semibold text-gray-800">Detail Kegiatan</h2>
                </div>
                <div class="grid gap-4 p-5 sm:grid-cols-2">

                    <div class="sm:col-span-2">
                        <label for="nama_kegiatan" class="block text-sm font-medium text-gray-700">
                            Nama Kegiatan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nama_kegiatan" name="nama_kegiatan"
                            value="{{ old('nama_kegiatan', $kegiatan->nama_kegiatan) }}" required maxlength="100"
                            placeholder="Contoh: Kerohanian, Upacara, Senam Pagi, Literasi..."
                            class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                   focus:border-green-600 focus:ring-2 focus:ring-green-100">
                    </div>

                    <div>
                        <label for="hari" class="block text-sm font-medium text-gray-700">
                            Hari <span class="text-red-500">*</span>
                        </label>
                        <select id="hari" name="hari" required
                            class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                   focus:border-green-600 focus:ring-2 focus:ring-green-100">
                            <option value="">-- Pilih Hari --</option>
                            @foreach ($hariOptions as $hari)
                                <option value="{{ $hari }}"
                                    {{ old('hari', $kegiatan->hari) === $hari ? 'selected' : '' }}>
                                    {{ $hari }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="minggu_ke" class="block text-sm font-medium text-gray-700">
                            Minggu ke- <span class="text-red-500">*</span>
                        </label>
                        <select id="minggu_ke" name="minggu_ke" required
                            class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                   focus:border-green-600 focus:ring-2 focus:ring-green-100">
                            <option value="">-- Pilih Minggu --</option>
                            @foreach ($mingguKeOptions as $val => $label)
                                <option value="{{ $val }}"
                                    {{ old('minggu_ke', $kegiatan->minggu_ke) == $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-400">Minggu ke berapa dalam bulan kegiatan ini berlangsung</p>
                    </div>

                </div>
            </div>

            {{-- Jam Kegiatan --}}
            <div class="rounded-lg border border-gray-200 bg-white">
                <div class="border-b border-gray-100 px-5 py-3.5">
                    <h2 class="text-sm font-semibold text-gray-800">Jam Kegiatan</h2>
                    <p class="mt-0.5 text-xs text-gray-500">Harus selesai sebelum jam pelajaran pertama dimulai</p>
                </div>
                <div class="grid gap-4 p-5 sm:grid-cols-2">

                    <div>
                        <label for="jam_mulai" class="block text-sm font-medium text-gray-700">
                            Jam Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" id="jam_mulai" name="jam_mulai"
                            value="{{ old('jam_mulai', substr((string) $kegiatan->jam_mulai, 0, 5)) }}" required
                            class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                   focus:border-green-600 focus:ring-2 focus:ring-green-100">
                    </div>

                    <div>
                        <label for="jam_selesai" class="block text-sm font-medium text-gray-700">
                            Jam Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" id="jam_selesai" name="jam_selesai"
                            value="{{ old('jam_selesai', substr((string) $kegiatan->jam_selesai, 0, 5)) }}" required
                            class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                   focus:border-green-600 focus:ring-2 focus:ring-green-100">
                        <p id="durasi_info" class="mt-1 text-xs text-gray-400"></p>
                    </div>

                </div>
            </div>

        @endif

        {{-- Catatan --}}
        <div class="rounded-lg border border-gray-200 bg-white">
            <div class="border-b border-gray-100 px-5 py-3.5">
                <h2 class="text-sm font-semibold text-gray-800">Catatan <span
                        class="font-normal text-gray-400">(opsional)</span></h2>
            </div>
            <div class="p-5">
                <textarea id="catatan" name="catatan" rows="3"
                    class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                           focus:border-green-600 focus:ring-2 focus:ring-green-100"
                    placeholder="{{ $type === 'kegiatan' ? 'Contoh: Dipimpin oleh wali kelas, dsb.' : 'Contoh: Jadwal reguler semester genap, pengganti guru cuti, dsb.' }}">{{ old('catatan', $type === 'kegiatan' ? $kegiatan->catatan : $jadwal->catatan) }}</textarea>
                <p class="mt-1 text-xs text-gray-400">Maksimal 500 karakter</p>
            </div>
        </div>

        {{-- Status Aktif --}}
        <div class="rounded-lg border border-gray-200 bg-white">
            <div class="border-b border-gray-100 px-5 py-3.5">
                <h2 class="text-sm font-semibold text-gray-800">Status</h2>
            </div>
            <div class="p-5">
                @php $isActive = $type === 'kegiatan' ? $kegiatan->is_active : $jadwal->is_active; @endphp
                <label class="flex items-center gap-2">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                        {{ old('is_active', $isActive) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                    <span class="text-sm font-medium text-gray-700">Aktif</span>
                </label>
                <p class="mt-1 text-xs text-gray-400">
                    {{ $type === 'kegiatan'
                        ? 'Jika tidak dicentang, kegiatan ini tidak akan muncul di jadwal aktif.'
                        : 'Jika tidak dicentang, jadwal ini tidak akan muncul dalam daftar jadwal aktif.' }}
                </p>
            </div>
        </div>

        {{-- Tombol --}}
<div class="flex justify-between gap-3">
             <a href="{{ route('kurikulum.jadwal.index', ['type' => $type]) }}"
                 class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                 <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                 </svg>
                 Kembali
             </a>
             <button type="submit"
                class="inline-flex items-center gap-1.5 rounded-lg bg-green-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-green-800">
                <i class="ti ti-check"></i>
                {{ $type === 'kegiatan' ? 'Simpan Perubahan Kegiatan' : 'Simpan Perubahan Jadwal' }}
            </button>
        </div>

    </form>

    @if ($type === 'pelajaran')
        <script>
            const kelasSelect = document.getElementById('kelas_id');
            const mapelSelect = document.getElementById('mapel_id');
            const guruSelect = document.getElementById('guru_id');
            const guruHint = document.getElementById('guru_hint');
            const assignments = @json($guruAssignments);

            function filterGuru() {
                const kelasId = Number(kelasSelect.value || 0);
                const mapelId = Number(mapelSelect.value || 0);

                const allowed = new Set(
                    assignments
                    .filter(a => Number(a.kelas_id) === kelasId && Number(a.mapel_id) === mapelId)
                    .map(a => Number(a.guru_id))
                );

                guruSelect.options[0].textContent = kelasId === 0 ?
                    '-- Pilih Kelas & Mapel --' :
                    mapelId === 0 ? '-- Pilih Mapel --' : '-- Pilih Guru --';

                Array.from(guruSelect.options).forEach((opt, i) => {
                    if (i === 0) return;
                    const id = Number(opt.value);
                    opt.hidden = opt.disabled = !(kelasId > 0 && mapelId > 0 && allowed.has(id));
                });

                if (guruSelect.options[guruSelect.selectedIndex]?.disabled) guruSelect.value = '';
                if (!guruSelect.value && allowed.size === 1) guruSelect.value = String([...allowed][0]);

                guruHint.textContent = kelasId === 0 ?
                    'Pilih kelas dan mapel terlebih dahulu' :
                    mapelId === 0 ? 'Pilih mapel untuk menampilkan guru yang sesuai' :
                    allowed.size === 0 ? 'Belum ada guru yang ditugaskan untuk kombinasi ini' :
                    `${allowed.size} guru tersedia`;
            }

            kelasSelect.addEventListener('change', filterGuru);
            mapelSelect.addEventListener('change', filterGuru);
            filterGuru();
        </script>
    @endif

    <script>
        const jamMulai = document.getElementById('jam_mulai');
        const jamSelesai = document.getElementById('jam_selesai');
        const durasiInfo = document.getElementById('durasi_info');

        function updateDurasi() {
            if (!jamMulai.value || !jamSelesai.value) {
                durasiInfo.textContent = '';
                return;
            }
            const [h1, m1] = jamMulai.value.split(':').map(Number);
            const [h2, m2] = jamSelesai.value.split(':').map(Number);
            const menit = (h2 * 60 + m2) - (h1 * 60 + m1);
            durasiInfo.textContent = menit > 0 ? `Durasi: ${menit} menit` : '';
        }

        jamMulai.addEventListener('change', updateDurasi);
        jamSelesai.addEventListener('change', updateDurasi);
        updateDurasi();
    </script>

@endsection
