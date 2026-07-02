@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

        {{-- ── Header ── --}}
        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Absensi Mapel</p>
                <h1 class="mt-2 text-3xl font-bold text-slate-900">
                    {{ $jadwal->mapel?->nama ?? '-' }} — {{ $jadwal->kelas?->nama_kelas ?? '-' }}
                </h1>
                <p class="mt-2 text-sm text-slate-600">
                    {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }} &middot;
                    Jam {{ $jadwal->jam_mulai }} – {{ $jadwal->jam_selesai }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('guru_mapel.absensi.index', ['jadwal_id' => $jadwal->id]) }}"
                    class="inline-flex items-center gap-1.5 justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali
                </a>

                @if ($isReadOnly)
                    {{-- FIX #3 — bedakan label: rekap selesai vs bukan hari ini --}}
                    <span
                        class="inline-flex items-center rounded-lg bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-600">
                        @if ($isFinished)
                            Rekap — waktu habis
                        @else
                            Mode rekap
                        @endif
                    </span>
                @else
                    <span
                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-100 px-4 py-2.5 text-sm font-semibold text-emerald-700">
                        <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-400"></span>
                        Jadwal aktif
                    </span>
                @endif
            </div>
        </div>

        {{-- ── Flash messages ── --}}
        @if (session('success'))
            <div
                class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div
                class="mb-6 flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div
                class="mb-6 flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>
                    Data belum valid. Periksa kembali pilihan bab, materi, dan status siswa.
                    @foreach ($errors->all() as $err)
                        <br>– {{ $err }}
                    @endforeach
                </span>
            </div>
        @endif

        @if ($isMissingAttendance)
            <div
                class="mb-6 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Jadwal sedang berlangsung dan absensi <strong>belum diisi</strong>. Isi sebelum jam pelajaran berakhir.
            </div>
        @endif

        @if ($isReadOnly)
            <div
                class="mb-6 flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                @if ($isFinished)
                    Waktu mengabsen sudah berakhir. Data tidak dapat diubah, hanya bisa dilihat.
                @else
                    Jadwal ini hanya bisa dilihat, tidak bisa diubah.
                @endif
            </div>
        @endif

        {{-- ── Info ringkas jadwal ── --}}
        <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="grid gap-3 sm:grid-cols-4">
                <div class="rounded-xl bg-slate-50 px-4 py-3">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Guru</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $jadwal->guru?->nama ?? '-' }}</p>
                </div>
                <div class="rounded-xl bg-slate-50 px-4 py-3">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Mapel</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $jadwal->mapel?->nama ?? '-' }}</p>
                </div>
                <div class="rounded-xl bg-slate-50 px-4 py-3">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Kelas</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $jadwal->kelas?->nama_kelas ?? '-' }}</p>
                </div>
                <div class="rounded-xl bg-slate-50 px-4 py-3">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Total Siswa</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $totalStudents }} siswa</p>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(320px,0.42fr)]">

            {{-- ── Form / rekap siswa ── --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">
                            {{ $isReadOnly ? 'Rekap absensi siswa' : 'Isi absensi siswa' }}
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            @if ($isReadOnly)
                                Data absensi yang telah disimpan untuk jadwal ini.
                            @else
                                Pilih status untuk setiap siswa. Kolom menit muncul otomatis saat status terlambat.
                            @endif
                        </p>
                    </div>

                    {{-- Tombol bulk — hanya saat form aktif --}}
                    @if (!$isReadOnly && $students->isNotEmpty())
                        <div class="flex shrink-0 flex-wrap gap-2 text-xs">
                            <button type="button" id="bulk-hadir"
                                class="rounded-lg border border-emerald-300 bg-emerald-50 px-3 py-1.5 font-semibold text-emerald-700 transition hover:bg-emerald-100">
                                Semua hadir
                            </button>
                            <button type="button" id="bulk-alpha"
                                class="rounded-lg border border-rose-300 bg-rose-50 px-3 py-1.5 font-semibold text-rose-700 transition hover:bg-rose-100">
                                Semua alpha
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Progress bar (hanya saat form aktif) --}}
                @if (!$isReadOnly && $students->isNotEmpty())
                    <div class="mt-4" id="progress-wrapper">
                        <div class="mb-1 flex justify-between text-xs text-slate-500">
                            <span id="progress-label">{{ $filledCount }} / {{ $totalStudents }} siswa tersimpan</span>
                            <span id="progress-pct">–</span>
                        </div>
                        <div class="h-1.5 w-full rounded-full bg-slate-200">
                            <div id="progress-fill" class="h-1.5 rounded-full bg-blue-500 transition-all" style="width: 0%">
                            </div>
                        </div>
                    </div>
                @endif

                <form id="absensi-form" action="{{ route('guru_mapel.absensi.store', $jadwal) }}" method="POST"
                    class="mt-5">
                    @csrf

                    {{-- Info materi terakhir --}}
                    @if ($lastBabId || $lastMateriId)
                        @php
                            $lastMateri = \App\Models\Materi::find($lastMateriId);
                            $lastBab = \App\Models\Bab::find($lastBabId);
                        @endphp
                        @if ($lastMateri || $lastBab)
                            <div
                                class="mb-4 inline-flex items-center gap-2 rounded-lg bg-sky-50 px-4 py-2 text-sm text-sky-800">
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Terakhir diajarkan:
                                @if ($lastBab)
                                    Bab {{ $lastBab->urutan }}
                                @endif
                                @if ($lastMateri)
                                    — {{ $lastMateri->judul }}
                                @endif
                            </div>
                        @endif
                    @endif

                    {{-- ── Pilih Bab & Materi ── --}}
                    <div class="mb-6 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <h3 class="text-base font-semibold text-slate-900">Materi Pelajaran Hari Ini</h3>
                        <p class="mt-1 text-sm text-slate-500">Pilih bab dan materi yang diajarkan pada sesi ini.</p>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="bab-select"
                                    class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Bab <span class="text-rose-500">*</span>
                                </label>
                                <select id="bab-select" name="bab_id"
                                    class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-900 focus:ring-slate-900"
                                    @disabled($isReadOnly)>
                                    <option value="">Pilih Bab</option>
                                    @foreach ($babs as $bab)
                                        <option value="{{ $bab->id }}" @selected(old('bab_id', $lastBabId) == $bab->id)>
                                            Bab {{ $bab->urutan }}: {{ $bab->nama_bab }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('bab_id')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="materi-select"
                                    class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Materi <span class="text-rose-500">*</span>
                                </label>
                                <select id="materi-select" name="materi_id"
                                    class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-900 focus:ring-slate-900"
                                    @disabled($isReadOnly)>
                                    <option value="">
                                        {{ $babs->isEmpty() ? 'Belum ada bab' : 'Pilih Bab dahulu' }}
                                    </option>
                                </select>
                                @error('materi_id')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- ── Daftar siswa ── --}}
                    <div class="space-y-3">
                        @forelse ($students as $index => $student)
                            @php
                                $attendance = $attendanceByStudentId->get($student->id);
                                $selectedStatus = old("absensi.{$student->id}.status", $attendance?->status ?? 'hadir');
                                $lateMinutes = old(
                                    "absensi.{$student->id}.terlambat_menit",
                                    $attendance?->terlambat_menit ?? '',
                                );

                                $badgeClass = match ($selectedStatus) {
                                    'hadir' => 'bg-emerald-100 text-emerald-700',
                                    'izin' => 'bg-sky-100 text-sky-700',
                                    'sakit' => 'bg-violet-100 text-violet-700',
                                    'terlambat' => 'bg-amber-100 text-amber-700',
                                    'alpha' => 'bg-rose-100 text-rose-700',
                                    default => 'bg-slate-100 text-slate-600',
                                };
                            @endphp

                            <article class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-4 transition"
                                data-student-row>
                                <div class="grid gap-4 md:grid-cols-[auto_minmax(0,1fr)_200px_130px] md:items-center">

                                    {{-- Nomor urut --}}
                                    <div
                                        class="hidden h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-200 text-xs font-bold text-slate-600 md:flex">
                                        {{ $index + 1 }}
                                    </div>

                                    {{-- Nama + live badge --}}
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">{{ $student->nama }}</p>
                                        <p class="mt-0.5 text-xs text-slate-500">{{ $student->nis ?? '-' }}</p>

                                        {{-- FIX #6 — badge berubah live via JS --}}
                                        <span data-status-badge
                                            class="mt-2 inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badgeClass }}">
                                            {{ ucfirst($selectedStatus) }}
                                            @if ($selectedStatus === 'terlambat' && $lateMinutes)
                                                ({{ $lateMinutes }} mnt)
                                            @endif
                                        </span>
                                    </div>

                                    {{-- Select status --}}
                                    <div>
                                        <label
                                            class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Status
                                        </label>
                                        <select name="absensi[{{ $student->id }}][status]" data-status-select
                                            class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-900 focus:ring-slate-900"
                                            @disabled($isReadOnly)>
                                            <option value="hadir" @selected($selectedStatus === 'hadir')>Hadir</option>
                                            <option value="izin" @selected($selectedStatus === 'izin')>Izin</option>
                                            <option value="sakit" @selected($selectedStatus === 'sakit')>Sakit</option>
                                            <option value="alpha" @selected($selectedStatus === 'alpha')>Alpha</option>
                                            <option value="terlambat" @selected($selectedStatus === 'terlambat')>Terlambat</option>
                                        </select>
                                    </div>

                                    {{-- Input menit terlambat --}}
                                    <div data-late-wrapper class="{{ $selectedStatus === 'terlambat' ? '' : 'hidden' }}">
                                        <label
                                            class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Menit
                                        </label>
                                        <input type="number" min="0" max="600"
                                            name="absensi[{{ $student->id }}][terlambat_menit]"
                                            value="{{ $lateMinutes }}" placeholder="Otomatis"
                                            class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-900 focus:ring-slate-900"
                                            @disabled($isReadOnly)>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div
                                class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                                Belum ada siswa di kelas ini.
                            </div>
                        @endforelse
                    </div>

                    {{-- FIX #6 — tombol submit hanya aktif jika materi sudah dipilih (dihandle JS) --}}
                    @if ($students->isNotEmpty() && !$isReadOnly)
                        <div class="mt-5 flex items-center justify-between gap-3 border-t border-slate-100 pt-5">
                            <p class="text-xs text-slate-500">Periksa kembali sebelum menyimpan.</p>
                            <button type="button" id="submit-btn"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-40"
                                disabled>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Simpan Absensi
                            </button>
                        </div>
                        <p id="materi-hint" class="mt-2 text-right text-xs text-amber-600">
                            Pilih materi terlebih dahulu untuk mengaktifkan tombol simpan.
                        </p>
                    @endif
                </form>
            </section>

            {{-- ── Sidebar ── --}}
            <aside class="space-y-4">

                {{-- Ringkasan status —live update via JS --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">Ringkasan status</h2>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        @foreach (['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpha' => 'Alpha', 'terlambat' => 'Terlambat'] as $status => $label)
                            @php
                                $summaryClass = match ($status) {
                                    'hadir' => 'bg-emerald-50 text-emerald-700',
                                    'izin' => 'bg-sky-50 text-sky-700',
                                    'sakit' => 'bg-violet-50 text-violet-700',
                                    'terlambat' => 'bg-amber-50 text-amber-700',
                                    default => 'bg-rose-50 text-rose-700',
                                };
                            @endphp
                            <div class="rounded-xl px-4 py-3 {{ $summaryClass }}">
                                <p class="text-xs font-medium uppercase tracking-wide">{{ $label }}</p>
                                <p class="mt-1 text-2xl font-bold" data-summary-count="{{ $status }}">
                                    {{ $statusCounts[$status] ?? 0 }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Riwayat tersimpan --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">Riwayat tersimpan</h2>
                    <p class="mt-1 text-sm text-slate-500">Data absensi yang sudah tersimpan di database.</p>

                    <div class="mt-4 max-h-[480px] space-y-2 overflow-y-auto pr-1">
                        @forelse ($attendances->unique('siswa_id') as $attendance)
                            @php
                                $rBadge = match ($attendance->status) {
                                    'hadir' => 'bg-emerald-100 text-emerald-700',
                                    'izin' => 'bg-sky-100 text-sky-700',
                                    'sakit' => 'bg-violet-100 text-violet-700',
                                    'terlambat' => 'bg-amber-100 text-amber-700',
                                    'alpha' => 'bg-rose-100 text-rose-700',
                                    default => 'bg-slate-100 text-slate-600',
                                };
                            @endphp
                            <article class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-900">
                                            {{ $attendance->siswa?->nama ?? '-' }}
                                        </p>
                                        <p class="mt-0.5 text-xs text-slate-500">
                                            {{ $attendance->jam_masuk?->format('H:i') ?? '-' }}
                                            @if ($attendance->status === 'terlambat' && $attendance->terlambat_menit)
                                                &middot; {{ $attendance->terlambat_menit }} mnt
                                            @endif
                                        </p>
                                    </div>
                                    <span
                                        class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $rBadge }}">
                                        {{ ucfirst($attendance->status ?? 'belum') }}
                                    </span>
                                </div>
                            </article>
                        @empty
                            <div
                                class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
                                Belum ada absensi tersimpan.
                            </div>
                        @endforelse
                    </div>
                </div>
            </aside>
        </div>
    </div>

    {{-- ── Modal konfirmasi submit ── --}}
    @if (!$isReadOnly)
        <div id="confirm-modal"
            class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4 backdrop-blur-sm">
            <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-bold text-slate-900">Simpan absensi?</h3>
                <p class="mt-2 text-sm text-slate-600">
                    Data akan disimpan dan masih bisa diubah selama jadwal berlangsung.
                </p>
                <div class="mt-6 flex gap-3">
                    <button type="button" id="modal-cancel"
                        class="flex-1 rounded-xl border border-slate-300 bg-white py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="button" id="modal-confirm"
                        class="flex-1 rounded-xl bg-slate-900 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                        Ya, simpan
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── JavaScript (hanya saat form aktif) ── --}}
    @if (!$isReadOnly)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // ── Konstanta ───────────────────────────────────────────
                const TOTAL = @json($totalStudents);

                const BADGE_CLASSES = {
                    hadir: 'bg-emerald-100 text-emerald-700',
                    izin: 'bg-sky-100 text-sky-700',
                    sakit: 'bg-violet-100 text-violet-700',
                    terlambat: 'bg-amber-100 text-amber-700',
                    alpha: 'bg-rose-100 text-rose-700',
                };

                const BADGE_LABELS = {
                    hadir: 'Hadir',
                    izin: 'Izin',
                    sakit: 'Sakit',
                    terlambat: 'Terlambat',
                    alpha: 'Alpha',
                };

                // ── Elemen ──────────────────────────────────────────────
                const selects = document.querySelectorAll('[data-status-select]');
                const babSelect = document.getElementById('bab-select');
                const materiSelect = document.getElementById('materi-select');
                const submitBtn = document.getElementById('submit-btn');
                const materiHint = document.getElementById('materi-hint');
                const modal = document.getElementById('confirm-modal');
                const modalConfirm = document.getElementById('modal-confirm');
                const modalCancel = document.getElementById('modal-cancel');
                const progressFill = document.getElementById('progress-fill');
                const progressLabel = document.getElementById('progress-label');
                const progressPct = document.getElementById('progress-pct');

                // ── FIX #6 — aktifkan tombol simpan hanya jika materi sudah dipilih ──
                function checkMateriSelected() {
                    const selected = materiSelect && materiSelect.value !== '';
                    if (submitBtn) {
                        submitBtn.disabled = !selected;
                        submitBtn.classList.toggle('opacity-40', !selected);
                        submitBtn.classList.toggle('cursor-not-allowed', !selected);
                    }
                    if (materiHint) {
                        materiHint.classList.toggle('hidden', selected);
                    }
                }

                if (materiSelect) {
                    materiSelect.addEventListener('change', checkMateriSelected);
                }

                // ── Sync badge + input menit terlambat ──────────────────
                function syncRow(select) {
                    const article = select.closest('[data-student-row]');
                    const wrapper = article?.querySelector('[data-late-wrapper]');
                    const badge = article?.querySelector('[data-status-badge]');
                    const lateInput = wrapper?.querySelector('input');
                    const status = select.value;

                    // Terlambat input visibility
                    if (wrapper) {
                        const show = status === 'terlambat';
                        wrapper.classList.toggle('hidden', !show);
                        if (!show && lateInput) lateInput.value = '';
                    }

                    // Live badge update
                    if (badge) {
                        const baseClasses = 'mt-2 inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ';
                        badge.className = baseClasses + (BADGE_CLASSES[status] ?? 'bg-slate-100 text-slate-600');
                        badge.textContent = BADGE_LABELS[status] ?? status;
                    }
                }

                selects.forEach(s => {
                    syncRow(s); // init state
                    s.addEventListener('change', function() {
                        syncRow(this);
                        updateSummaryCounts();
                    });
                });

                // ── Live summary count ──────────────────────────────────
                function updateSummaryCounts() {
                    const counts = {
                        hadir: 0,
                        izin: 0,
                        sakit: 0,
                        alpha: 0,
                        terlambat: 0
                    };
                    selects.forEach(s => {
                        if (s.value in counts) counts[s.value]++;
                    });
                    Object.entries(counts).forEach(([status, count]) => {
                        const el = document.querySelector('[data-summary-count="' + status + '"]');
                        if (el) el.textContent = count;
                    });

                    // Progress bar — semua baris selalu punya nilai (default hadir)
                    const filled = selects.length;
                    const pct = TOTAL > 0 ? Math.round(filled / TOTAL * 100) : 0;
                    if (progressLabel) progressLabel.textContent = filled + ' / ' + TOTAL + ' siswa diisi';
                    if (progressPct) progressPct.textContent = pct + '%';
                    if (progressFill) progressFill.style.width = pct + '%';
                }
                updateSummaryCounts();

                // ── Bulk status ─────────────────────────────────────────
                function applyBulk(status, btn, originalLabel) {
                    selects.forEach(s => {
                        s.value = status;
                        syncRow(s);
                    });
                    updateSummaryCounts();

                    btn.textContent = '✓ Diterapkan';
                    setTimeout(() => {
                        btn.textContent = originalLabel;
                    }, 1500);
                }

                const bulkHadir = document.getElementById('bulk-hadir');
                const bulkAlpha = document.getElementById('bulk-alpha');
                if (bulkHadir) bulkHadir.addEventListener('click', () => applyBulk('hadir', bulkHadir, 'Semua hadir'));
                if (bulkAlpha) bulkAlpha.addEventListener('click', () => applyBulk('alpha', bulkAlpha, 'Semua alpha'));

                // ── Modal konfirmasi ────────────────────────────────────
                const form = document.getElementById('absensi-form');
                if (submitBtn && modal && form) {
                    submitBtn.addEventListener('click', function() {
                        if (this.disabled) return;
                        modal.classList.remove('hidden');
                        modal.classList.add('flex');
                    });

                    function closeModal() {
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                    }

                    modalCancel.addEventListener('click', closeModal);
                    modal.addEventListener('click', e => {
                        if (e.target === modal) closeModal();
                    });

                    modalConfirm.addEventListener('click', function() {
                        this.disabled = true;
                        this.textContent = 'Menyimpan...';
                        submitBtn.disabled = true;
                        submitBtn.textContent = 'Menyimpan...';
                        form.submit();
                    });
                }

                // ── Bab → Materi dinamis ────────────────────────────────
                function loadMateris(babId, autoSelectId = null) {
                    if (!babId) {
                        materiSelect.innerHTML = '<option value="">' +
                            (@json($babs->isEmpty()) ? 'Belum ada bab' : 'Pilih Bab dahulu') +
                            '</option>';
                        materiSelect.disabled = true;
                        checkMateriSelected();
                        return;
                    }

                    materiSelect.disabled = true;
                    materiSelect.innerHTML = '<option value="">Memuat...</option>';
                    checkMateriSelected();

                    const url = @json(route('guru_mapel.absensi.materis', $jadwal)) +
                        '?bab_id=' + encodeURIComponent(babId);

                    fetch(url)
                        .then(r => {
                            if (!r.ok) throw new Error('Network error');
                            return r.json();
                        })
                        .then(data => {
                            if (data.length === 0) {
                                materiSelect.innerHTML = '<option value="">Tidak ada materi di bab ini</option>';
                                materiSelect.disabled = true;
                            } else {
                                materiSelect.innerHTML = '<option value="">Pilih Materi</option>';
                                data.forEach(m => {
                                    const opt = document.createElement('option');
                                    opt.value = m.id;
                                    opt.textContent = m.urutan + '. ' + m.judul;
                                    materiSelect.appendChild(opt);
                                });
                                materiSelect.disabled = false;

                                if (autoSelectId && data.some(m => m.id == autoSelectId)) {
                                    materiSelect.value = autoSelectId;
                                }
                            }
                            checkMateriSelected();
                        })
                        .catch(() => {
                            materiSelect.innerHTML = '<option value="">Gagal memuat materi</option>';
                            materiSelect.disabled = false;
                            checkMateriSelected();
                        });
                }

                if (babSelect && materiSelect) {
                    babSelect.addEventListener('change', function() {
                        loadMateris(this.value);
                    });

                    const initialBabId = @json($lastBabId);
                    const initialMateriId = @json($lastMateriId);

                    if (initialBabId) {
                        loadMateris(initialBabId, initialMateriId);
                    } else {
                        materiSelect.disabled = true;
                        checkMateriSelected();
                    }
                }
            });
        </script>
    @endif
@endsection
