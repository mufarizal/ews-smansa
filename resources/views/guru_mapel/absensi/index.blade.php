@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

        {{-- ── Header ── --}}
        <div class="mb-8">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Absensi Mapel</p>
            <h1 class="mt-2 text-3xl font-bold text-slate-900">Jadwal Hari Ini — {{ $todayHari }}</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-600">
                Absensi hanya bisa diisi saat jam pelajaran berlangsung. Setelah jam selesai, data masih bisa dilihat
                namun tidak bisa diubah.
            </p>
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

        {{-- ── Alert: jadwal aktif belum diabsen ── --}}
        @if ($activeJadwal && !$activeJadwal->has_attendance_today)
            <div
                class="mb-6 flex items-center justify-between gap-4 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3">
                <div class="flex items-center gap-3 text-sm text-amber-900">
                    <svg class="h-5 w-5 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>
                        <strong>Jadwal sedang berjalan</strong> — absensi
                        <strong>{{ $activeJadwal->mapel?->nama }}</strong> belum diisi.
                    </span>
                </div>
                <a href="{{ route('guru_mapel.absensi.show', $activeJadwal) }}"
                    class="shrink-0 rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-amber-700">
                    Isi Sekarang
                </a>
            </div>
        @elseif (!$activeJadwal && $jadwals->isNotEmpty())
            <div
                class="mb-6 flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Tidak ada jadwal yang sedang berjalan saat ini.
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1.3fr)_minmax(0,0.7fr)] lg:items-start">

            {{-- ── Daftar jadwal ── --}}
            <section class="space-y-4">
                @forelse ($jadwals as $jadwal)
                    @php
                        $studentCount = $jadwal->kelas?->siswas?->count() ?? 0;
                        $previewStudents = $jadwal->kelas?->siswas?->sortBy('nama')->take(8) ?? collect();
                        $filledCount = $jadwal->attendance_count ?? 0;

                        if ($jadwal->is_ongoing && !$jadwal->has_attendance_today) {
                            $statusLabel = 'Aktif — belum diabsen';
                            $statusClasses = 'bg-amber-100 text-amber-800';
                            $dotColor = 'bg-amber-400 animate-pulse';
                        } elseif ($jadwal->is_ongoing) {
                            $statusLabel = 'Aktif — sudah diisi';
                            $statusClasses = 'bg-emerald-100 text-emerald-700';
                            $dotColor = 'bg-emerald-400';
                        } elseif ($jadwal->is_upcoming) {
                            $statusLabel = 'Belum mulai';
                            $statusClasses = 'bg-slate-100 text-slate-500';
                            $dotColor = null;
                        } elseif ($jadwal->is_finished && $jadwal->has_attendance_today) {
                            $statusLabel = 'Selesai — ada rekap';
                            $statusClasses = 'bg-slate-100 text-slate-500';
                            $dotColor = null;
                        } elseif ($jadwal->is_finished) {
                            $statusLabel = 'Terlewat — tidak diabsen';
                            $statusClasses = 'bg-rose-100 text-rose-700';
                            $dotColor = null;
                        } else {
                            $statusLabel = 'Tidak tersedia';
                            $statusClasses = 'bg-slate-100 text-slate-500';
                            $dotColor = null;
                        }

                        $borderClass = $jadwal->is_ongoing
                            ? 'border-l-4 border-l-amber-400 border-t border-r border-b border-slate-200 bg-amber-50/30'
                            : 'border-slate-200';
                    @endphp

                    <article
                        class="rounded-2xl border bg-white p-5 shadow-sm transition hover:border-slate-300 {{ $borderClass }}">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0 flex-1">

                                {{-- Waktu & badge status --}}
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                        {{ $jadwal->jam_mulai }} – {{ $jadwal->jam_selesai }}
                                    </span>
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">
                                        @if ($dotColor)
                                            <span class="h-1.5 w-1.5 rounded-full {{ $dotColor }}"></span>
                                        @endif
                                        {{ $statusLabel }}
                                    </span>
                                </div>

                                <h2 class="mt-3 truncate text-xl font-bold text-slate-900">
                                    {{ $jadwal->mapel?->nama ?? '-' }}
                                </h2>
                                <p class="mt-1 text-sm text-slate-600">
                                    {{ $jadwal->kelas?->nama_kelas ?? '-' }} &middot;
                                    {{ $jadwal->guru?->nama ?? $guru->nama }}
                                </p>

                                {{-- Progress absensi --}}
                                @if ($jadwal->has_attendance_today && $studentCount > 0)
                                    @php $pct = min(100, round($filledCount / $studentCount * 100)); @endphp
                                    <div class="mt-3">
                                        <div class="mb-1 flex justify-between text-xs text-slate-500">
                                            <span>{{ $filledCount }} / {{ $studentCount }} siswa diabsen</span>
                                            <span>{{ $pct }}%</span>
                                        </div>
                                        <div class="h-1.5 w-full rounded-full bg-slate-200">
                                            <div class="h-1.5 rounded-full bg-emerald-500 transition-all"
                                                style="width: {{ $pct }}%"></div>
                                        </div>
                                    </div>
                                @elseif ($jadwal->is_ongoing)
                                    <p class="mt-2 text-sm font-semibold text-amber-700">
                                        Absensi belum diisi — {{ $studentCount }} siswa menunggu.
                                    </p>
                                @elseif ($jadwal->availability_message)
                                    <p class="mt-2 text-xs text-slate-500">{{ $jadwal->availability_message }}</p>
                                @endif
                            </div>

                            {{-- Tombol aksi --}}
                            <div class="flex w-full gap-2 lg:w-auto lg:flex-col lg:items-end">
                                @if ($jadwal->is_ongoing)
                                    <a href="{{ route('guru_mapel.absensi.show', $jadwal) }}"
                                        class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-red-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 lg:w-auto">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        {{ $jadwal->has_attendance_today ? 'Edit Absensi' : 'Isi Absensi' }}
                                    </a>
                                @elseif ($jadwal->is_finished && $jadwal->has_attendance_today)
                                    <a href="{{ route('guru_mapel.absensi.show', $jadwal) }}"
                                        class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 lg:w-auto">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        Lihat Rekap
                                    </a>
                                @else
                                    <button type="button" disabled
                                        class="inline-flex w-full cursor-not-allowed items-center justify-center rounded-lg bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-400 lg:w-auto">
                                        @if ($jadwal->is_upcoming)
                                            Belum mulai
                                        @elseif ($jadwal->is_finished)
                                            Terlewat
                                        @else
                                            Terkunci
                                        @endif
                                    </button>
                                @endif
                            </div>
                        </div>

                        {{-- FIX #7 — accordion TIDAK otomatis terbuka; guru buka sendiri jika perlu --}}
                        <details class="mt-4 rounded-xl border border-slate-200 bg-white p-4">
                            <summary class="cursor-pointer select-none text-sm font-semibold text-slate-700">
                                Lihat daftar siswa ({{ $studentCount }})
                            </summary>
                            <div class="mt-4 flex flex-wrap gap-2">
                                @forelse ($previewStudents as $siswa)
                                    <span
                                        class="rounded-full bg-slate-50 px-3 py-1 text-xs font-medium text-slate-700 ring-1 ring-slate-200">
                                        {{ $siswa->nama }}
                                    </span>
                                @empty
                                    <span class="text-sm text-slate-500">Belum ada siswa di kelas ini.</span>
                                @endforelse
                                @if ($studentCount > $previewStudents->count())
                                    <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-600">
                                        +{{ $studentCount - $previewStudents->count() }} lainnya
                                    </span>
                                @endif
                            </div>
                        </details>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm">
                        <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <h2 class="mt-4 text-lg font-semibold text-slate-900">Tidak ada jadwal hari ini</h2>
                        <p class="mt-2 text-sm text-slate-500">
                            Pastikan jadwal guru sudah diisi oleh kurikulum untuk hari {{ $todayHari }}.
                        </p>
                    </div>
                @endforelse
            </section>

            {{-- ── Sidebar ── --}}
            <aside class="space-y-4 lg:sticky lg:top-6">

                {{-- Panduan --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Panduan</p>
                    <h3 class="mt-2 text-lg font-bold text-slate-900">Aturan absensi</h3>
                    <ol class="mt-4 space-y-3 text-sm text-slate-600">
                        <li class="flex gap-3">
                            <span
                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs font-semibold text-white">1</span>
                            <span>Absensi dibuka saat jam pelajaran sedang berlangsung.</span>
                        </li>
                        <li class="flex gap-3">
                            <span
                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs font-semibold text-white">2</span>
                            <span>Pilih status: <strong>hadir</strong>, izin, sakit, alpha, atau terlambat.</span>
                        </li>
                        <li class="flex gap-3">
                            <span
                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs font-semibold text-white">3</span>
                            <span>Setelah jam selesai, data tidak bisa diubah lagi.</span>
                        </li>
                    </ol>
                </div>

                {{-- Jadwal aktif sekarang --}}
                @if ($activeJadwal)
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-700">Sedang berlangsung</p>
                        <h3 class="mt-2 text-lg font-bold text-amber-950">{{ $activeJadwal->mapel?->nama ?? '-' }}</h3>
                        <p class="mt-1 text-sm text-amber-900">
                            {{ $activeJadwal->kelas?->nama_kelas ?? '-' }} &middot;
                            {{ $activeJadwal->jam_mulai }} – {{ $activeJadwal->jam_selesai }}
                        </p>
                        @if (!$activeJadwal->has_attendance_today)
                            <p class="mt-2 text-xs font-semibold text-amber-800">⚠ Belum diabsen</p>
                        @else
                            <p class="mt-2 text-xs font-semibold text-emerald-700">✓ Absensi sudah tersimpan</p>
                        @endif
                        <a href="{{ route('guru_mapel.absensi.show', $activeJadwal) }}"
                            class="mt-4 inline-flex w-full items-center justify-center rounded-lg bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-700">
                            {{ $activeJadwal->has_attendance_today ? 'Edit Absensi' : 'Isi Absensi' }}
                        </a>
                    </div>
                @endif

                {{-- Ringkasan hari ini (akurat) --}}
                @if ($jadwals->isNotEmpty())
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Ringkasan hari ini</p>
                        <div class="mt-3 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-600">Total jadwal</span>
                                <span class="font-semibold text-slate-900">{{ $summaryStats['total'] }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-600">Sudah diabsen</span>
                                <span class="font-semibold text-emerald-700">{{ $summaryStats['sudah'] }}</span>
                            </div>
                            @if ($summaryStats['aktif_kosong'] > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="font-medium text-amber-700">Aktif, belum diisi</span>
                                    <span class="font-semibold text-amber-700">{{ $summaryStats['aktif_kosong'] }}</span>
                                </div>
                            @endif
                            @if ($summaryStats['terlewat'] > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-rose-600">Terlewat</span>
                                    <span class="font-semibold text-rose-600">{{ $summaryStats['terlewat'] }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Progress bar keseluruhan --}}
                        @if ($summaryStats['total'] > 0)
                            @php $pctDone = round($summaryStats['sudah'] / $summaryStats['total'] * 100); @endphp
                            <div class="mt-4">
                                <div class="mb-1 flex justify-between text-xs text-slate-500">
                                    <span>Progress absensi</span>
                                    <span>{{ $pctDone }}%</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-slate-100">
                                    <div class="h-2 rounded-full bg-emerald-500 transition-all"
                                        style="width: {{ $pctDone }}%"></div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </aside>
        </div>
    </div>
@endsection
