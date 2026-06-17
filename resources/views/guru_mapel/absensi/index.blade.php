@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Absensi Mapel</p>
                <h1 class="mt-2 text-3xl font-bold text-slate-900">Jadwal Hari Ini - {{ $todayHari }}</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-600">
                    Checklist hanya bisa dibuka saat jam mengajar aktif dan hanya untuk kelas yang sedang diajar.
                    Di luar jadwal berjalan, akses checklist akan dikunci.
                </p>
            </div>

            <a href="{{ route('guru_mapel.dashboard') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                Kembali ke Dashboard
            </a>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                {{ session('error') }}
            </div>
        @endif

        @if (!$activeJadwal && $jadwals->isNotEmpty())
            <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                Belum masuk jadwal mengabsen. Silakan tunggu sampai jam pelajaran yang sedang berjalan.
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)]">
            <section class="space-y-4">
                @forelse ($jadwals as $jadwal)
                    @php
                        $studentCount = $jadwal->kelas?->siswas?->count() ?? 0;
                        $previewStudents = $jadwal->kelas?->siswas?->sortBy('nama')->take(8) ?? collect();
                        $statusLabel = $jadwal->is_ongoing
                            ? 'Sedang berlangsung'
                            : ($jadwal->is_upcoming
                                ? 'Belum mulai'
                                : 'Selesai');
                        $statusClasses = $jadwal->is_ongoing
                            ? 'bg-emerald-100 text-emerald-700'
                            : ($jadwal->is_upcoming
                                ? 'bg-amber-100 text-amber-700'
                                : 'bg-slate-100 text-slate-600');
                    @endphp

                    <article
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-300">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $jadwal->jam_mulai }}
                                        - {{ $jadwal->jam_selesai }}</span>
                                    <span
                                        class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">{{ $jadwal->kelas?->nama_kelas ?? '-' }}</span>
                                    <span
                                        class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">{{ $jadwal->mapel?->nama ?? '-' }}</span>
                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">{{ $statusLabel }}</span>
                                </div>

                                <h2 class="mt-3 text-xl font-bold text-slate-900">{{ $jadwal->mapel?->nama ?? '-' }}</h2>
                                <p class="mt-1 text-sm text-slate-600">{{ $jadwal->kelas?->nama_kelas ?? '-' }} •
                                    {{ $studentCount }} siswa • {{ $jadwal->guru?->nama ?? $guru->nama }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $jadwal->availability_message }}</p>
                            </div>

                            <div class="flex shrink-0 gap-2 sm:flex-col sm:items-end">
                                @if ($jadwal->is_ongoing)
                                    <a href="{{ route('guru_mapel.absensi.show', $jadwal) }}"
                                        class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                                        Buka Checklist
                                    </a>
                                @else
                                    <button type="button" disabled
                                        class="inline-flex items-center justify-center rounded-xl bg-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-400">
                                        Terkunci
                                    </button>
                                @endif
                            </div>
                        </div>

                        <details class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <summary class="cursor-pointer text-sm font-semibold text-slate-700">Lihat siswa kelas ini
                            </summary>
                            <div class="mt-4 flex flex-wrap gap-2">
                                @forelse ($previewStudents as $siswa)
                                    <span
                                        class="rounded-full bg-white px-3 py-1 text-xs font-medium text-slate-700 ring-1 ring-slate-200">{{ $siswa->nama }}</span>
                                @empty
                                    <span class="text-sm text-slate-500">Belum ada siswa di kelas ini.</span>
                                @endforelse

                                @if ($studentCount > $previewStudents->count())
                                    <span
                                        class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-600">+{{ $studentCount - $previewStudents->count() }}
                                        lainnya</span>
                                @endif
                            </div>
                        </details>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm">
                        <h2 class="text-lg font-semibold text-slate-900">Tidak ada jadwal hari ini</h2>
                        <p class="mt-2 text-sm text-slate-500">Pastikan jadwal guru sudah diisi oleh admin untuk hari
                            {{ $todayHari }}.</p>
                    </div>
                @endforelse
            </section>

            <aside class="space-y-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Akses cepat</p>
                    <h3 class="mt-2 text-lg font-bold text-slate-900">Alur checklist</h3>
                    <ol class="mt-4 space-y-3 text-sm text-slate-600">
                        <li class="flex gap-3"><span
                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs font-semibold text-white">1</span><span>Pilih
                                jadwal kelas yang sedang berjalan.</span></li>
                        <li class="flex gap-3"><span
                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs font-semibold text-white">2</span><span>Buka
                                checklist saat status jadwal berubah menjadi aktif.</span></li>
                        <li class="flex gap-3"><span
                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs font-semibold text-white">3</span><span>Centang
                                siswa yang hadir, lalu simpan.</span></li>
                    </ol>
                </div>

                @if ($activeJadwal)
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Jadwal aktif</p>
                        <h3 class="mt-2 text-lg font-bold text-emerald-950">{{ $activeJadwal->mapel?->nama ?? '-' }}</h3>
                        <p class="mt-1 text-sm text-emerald-900">{{ $activeJadwal->kelas?->nama_kelas ?? '-' }} •
                            {{ $activeJadwal->getAttendanceWindowLabel() }}</p>
                        <a href="{{ route('guru_mapel.absensi.show', $activeJadwal) }}"
                            class="mt-4 inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                            Buka Checklist
                        </a>
                    </div>
                @endif
            </aside>
        </div>
    </div>
@endsection
