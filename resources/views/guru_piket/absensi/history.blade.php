@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-slate-50/80 py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Riwayat Absensi</p>
                    <h1 class="mt-2 text-3xl font-bold text-slate-900">History berbasis QR Session</h1>
                    <p class="mt-2 max-w-2xl text-sm text-slate-600">
                        Pilih session tertentu, lalu filter siswa berdasarkan kelas, nama, atau status tanpa harus memuat
                        seluruh riwayat sekaligus.
                    </p>
                </div>
            </div>

            @if (session('error'))
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
                <aside class="space-y-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-semibold text-slate-900">Pilih Session</h2>
                                <p class="text-xs text-slate-500">Cari berdasarkan tanggal, jam, atau kode sesi</p>
                            </div>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                {{ collect($availableHistorySessions ?? [])->sum(fn($group) => count($group['sessions'] ?? [])) }}
                                sesi
                            </span>
                        </div>

                        <form method="GET" action="{{ route('guru_piket.attendance.history') }}" class="mb-4">
                            <input type="hidden" name="qr_session_id" value="{{ $selectedSessionId ?? '' }}">
                            <input type="hidden" name="status" value="{{ $status }}">
                            <input type="hidden" name="kelas_id" value="{{ $kelasId }}">
                            <input type="hidden" name="search" value="{{ $search }}">
                            <label for="session_search"
                                class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Filter
                                session</label>
                            <div class="flex gap-2">
                                <input id="session_search" name="session_search" type="search" value="{{ $sessionSearch }}"
                                    placeholder="Contoh: 13 Apr, 07:00, Masuk"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                <button type="submit"
                                    class="rounded-xl bg-slate-900 px-3 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                                    Cari
                                </button>
                            </div>
                        </form>

                        <div class="max-h-[72vh] space-y-4 overflow-y-auto pr-1">
                            @forelse ($availableHistorySessions ?? [] as $group)
                                <div>
                                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                                        {{ $group['date_label'] }}</p>
                                    <div class="space-y-2">
                                        @foreach ($group['sessions'] as $session)
                                            @php
                                                $isActive = (int) ($selectedSessionId ?? 0) === (int) $session['id'];
                                                $query = array_merge(request()->except(['qr_session_id', 'page']), [
                                                    'qr_session_id' => $session['id'],
                                                ]);
                                            @endphp
                                            <a href="{{ route('guru_piket.attendance.history', $query) }}"
                                                class="block rounded-xl border px-3 py-3 transition {{ $isActive ? 'border-blue-300 bg-blue-50 ring-1 ring-blue-200' : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50' }}">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div>
                                                        <p class="text-sm font-semibold text-slate-900">
                                                            {{ $session['generated_at'] ?? '-' }} •
                                                            {{ $session['tipe_label'] }}</p>
                                                        <p class="mt-1 text-xs text-slate-500">
                                                            Batas {{ $session['jam_batas'] ?? '-' }}
                                                            @if (!empty($session['jam_maksimal']) && $session['tipe'] === 'masuk')
                                                                • Max {{ $session['jam_maksimal'] }}
                                                            @endif
                                                        </p>
                                                    </div>
                                                    <span
                                                        class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $session['status_label'] === 'Aktif' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                                        {{ $session['status_label'] }}
                                                    </span>
                                                </div>
                                                <div class="mt-2 flex items-center justify-between text-xs text-slate-500">
                                                    <span>{{ $session['absensis_count'] }} data</span>
                                                    <span>{{ $session['kode_sesi'] }}</span>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <div
                                    class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                                    Belum ada QR session yang bisa ditampilkan.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </aside>

                <main class="space-y-6">
                    @if ($selectedSession)
                        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span
                                            class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">{{ $selectedSession->tipe === 'masuk' ? 'Masuk' : 'Pulang' }}</span>
                                        <span
                                            class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $selectedSession->tanggal?->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</span>
                                        <span
                                            class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">{{ $selectedSession->sudah_ditutup ? 'Ditutup' : 'Aktif' }}</span>
                                    </div>
                                    <h2 class="mt-3 text-2xl font-bold text-slate-900">Session
                                        {{ $selectedSession->generated_at?->format('H:i') ?? '-' }}</h2>
                                    <p class="mt-1 text-sm text-slate-600">Kode sesi <span
                                            class="font-semibold text-slate-900">{{ $selectedSession->kode_sesi }}</span> •
                                        {{ $selectedSession->absensis_count ?? 0 }} data absensi tercatat</p>
                                </div>

                                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Generated</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-900">
                                            {{ $selectedSession->generated_at?->format('H:i') ?? '-' }}</p>
                                    </div>
                                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Batas</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-900">
                                            {{ $selectedSession->jam_batas?->format('H:i') ?? '-' }}</p>
                                    </div>
                                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Max</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-900">
                                            {{ $selectedSession->jam_maksimal?->format('H:i') ?? '-' }}</p>
                                    </div>
                                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Tercatat</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $stats['tercatat'] }}</p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <p class="text-sm text-slate-500">Total Siswa</p>
                                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $stats['total'] }}</p>
                            </div>
                            <div class="rounded-2xl border border-emerald-200 bg-white p-4 shadow-sm">
                                <p class="text-sm text-slate-500">Hadir Tepat Waktu</p>
                                <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $stats['hadir'] }}</p>
                            </div>
                            <div class="rounded-2xl border border-amber-200 bg-white p-4 shadow-sm">
                                <p class="text-sm text-slate-500">Terlambat</p>
                                <p class="mt-2 text-3xl font-bold text-amber-600">{{ $stats['terlambat'] }}</p>
                            </div>
                            <div class="rounded-2xl border border-rose-200 bg-white p-4 shadow-sm">
                                <p class="text-sm text-slate-500">Tidak Hadir</p>
                                <p class="mt-2 text-3xl font-bold text-rose-600">{{ $stats['alpha'] }}</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <p class="text-sm text-slate-500">Belum Absen</p>
                                <p class="mt-2 text-3xl font-bold text-slate-700">{{ $stats['belum_absen'] }}</p>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <form method="GET" action="{{ route('guru_piket.attendance.history') }}" class="space-y-4">
                                <input type="hidden" name="qr_session_id" value="{{ $selectedSessionId }}">
                                <input type="hidden" name="session_search" value="{{ $sessionSearch }}">
                                @php
                                    $kelasList = $availableClasses ?? collect();
                                @endphp

                                <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_200px_160px_auto]">
                                    <div>
                                        <label for="search"
                                            class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Cari
                                            siswa</label>
                                        <input id="search" name="search" value="{{ $search }}" type="search"
                                            placeholder="Nama atau NIS"
                                            class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                    </div>

                                    <div>
                                        <label for="kelas_id"
                                            class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Kelas</label>
                                        <select id="kelas_id" name="kelas_id"
                                            class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                            <option value="">Semua kelas</option>
                                            @foreach ($kelasList as $kelas)
                                                <option value="{{ $kelas->id }}"
                                                    {{ (string) $kelasId === (string) $kelas->id ? 'selected' : '' }}>
                                                    {{ $kelas->nama_kelas }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label for="status"
                                            class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Status</label>
                                        <select id="status" name="status"
                                            class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                            <option value="semua" {{ $status === 'semua' ? 'selected' : '' }}>Semua
                                                status</option>
                                            <option value="hadir" {{ $status === 'hadir' ? 'selected' : '' }}>Hadir
                                            </option>
                                            <option value="terlambat" {{ $status === 'terlambat' ? 'selected' : '' }}>
                                                Terlambat</option>
                                            <option value="alpha" {{ $status === 'alpha' ? 'selected' : '' }}>Tidak Hadir
                                            </option>
                                        </select>
                                    </div>

                                    <div class="flex items-end gap-2">
                                        <button type="submit"
                                            class="rounded-xl bg-green-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-800">
                                            Terapkan
                                        </button>
                                        <a href="{{ route('guru_piket.attendance.history', ['qr_session_id' => $selectedSessionId]) }}"
                                            class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                            Reset
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </section>

                        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div
                                class="flex flex-col gap-2 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900">Data Absensi</h3>
                                    <p class="text-sm text-slate-500">Menampilkan {{ $attendances->count() }} dari
                                        {{ $attendances->total() }} data yang sesuai filter</p>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('guru_piket.attendance.export', array_merge(request()->except('format'), ['qr_session_id' => $selectedSessionId, 'format' => 'xlsx'])) }}"
                                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                        Export Excel
                                    </a>
                                    <a href="{{ route('guru_piket.attendance.export', array_merge(request()->except('format'), ['qr_session_id' => $selectedSessionId, 'format' => 'csv'])) }}"
                                        class="inline-flex items-center gap-2 rounded-xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-sky-700">
                                        Export CSV
                                    </a>
                                    <button type="button" onclick="window.print()"
                                        class="inline-flex items-center gap-2 rounded-xl bg-slate-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                                        Print
                                    </button>
                                </div>
                            </div>

                            @if ($attendances->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-slate-200">
                                        <thead class="bg-gradient-to-r from-slate-50 to-slate-100">
                                            <tr>
                                                <th
                                                    class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                                    #</th>
                                                <th
                                                    class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                                    Nama Siswa</th>
                                                <th
                                                    class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                                    Kelas</th>
                                                <th
                                                    class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-orange-600">
                                                    Guru Piket</th>
                                                <th
                                                    class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                                    Jam Masuk</th>
                                                <th
                                                    class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                                    Jam Pulang</th>
                                                <th
                                                    class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                                    Terlambat</th>
                                                <th
                                                    class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                                    Status</th>
                                                <th
                                                    class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                                    GPS</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200 bg-white">
                                            @foreach ($attendances as $attendance)
                                                <tr class="hover:bg-slate-50/60 transition-colors">
                                                    <td class="px-5 py-4 text-sm font-medium text-slate-900">
                                                        {{ $attendances->firstItem() + $loop->index }}</td>
                                                    <td class="px-5 py-4">
                                                        <div class="flex flex-col gap-1">
                                                            <p class="text-sm font-semibold text-slate-900">
                                                                {{ $attendance->siswa->nama ?? 'N/A' }}</p>
                                                            <p class="text-xs text-slate-500">NIS:
                                                                {{ $attendance->siswa->nis ?? '-' }}</p>
                                                        </div>
                                                    </td>
                                                    <td class="px-5 py-4 text-sm text-slate-600">
                                                        {{ $attendance->siswa->kelas->nama_kelas ?? 'N/A' }}</td>
                                                    <td class="px-5 py-4">
                                                        @if ($attendance->guru_id && $attendance->guru)
                                                            <span
                                                                class="inline-flex items-center gap-2 rounded-lg bg-orange-50 px-2.5 py-1.5 text-xs font-semibold text-orange-700 border border-orange-200">
                                                                <span class="h-2 w-2 rounded-full bg-orange-500"></span>
                                                                {{ $attendance->guru->nama ?? 'N/A' }}
                                                            </span>
                                                        @else
                                                            <span class="text-xs text-slate-400">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-5 py-4 text-sm text-slate-600">
                                                        {{ $attendance->jam_masuk ? $attendance->jam_masuk->format('H:i:s') : '-' }}
                                                    </td>
                                                    <td class="px-5 py-4 text-sm text-slate-600">
                                                        {{ $attendance->jam_pulang ? $attendance->jam_pulang->format('H:i:s') : '-' }}
                                                    </td>
                                                    <td class="px-5 py-4">
                                                        @if ($attendance->terlambat_menit)
                                                            <span
                                                                class="inline-flex items-center gap-1.5 rounded-lg bg-amber-50 px-2.5 py-1.5 text-xs font-semibold text-amber-700 border border-amber-200">
                                                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                                {{ $attendance->terlambat_menit }} menit
                                                            </span>
                                                        @else
                                                            <span class="text-xs text-slate-400">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-5 py-4">
                                                        @if ($attendance->status === 'hadir')
                                                            <span
                                                                class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2.5 py-1.5 text-xs font-semibold text-emerald-700 border border-emerald-200">
                                                                <span
                                                                    class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                                Hadir
                                                            </span>
                                                        @elseif($attendance->status === 'terlambat')
                                                            <span
                                                                class="inline-flex items-center gap-1.5 rounded-lg bg-amber-50 px-2.5 py-1.5 text-xs font-semibold text-amber-700 border border-amber-200">
                                                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                                Terlambat
                                                            </span>
                                                        @else
                                                            <span
                                                                class="inline-flex items-center gap-1.5 rounded-lg bg-rose-50 px-2.5 py-1.5 text-xs font-semibold text-rose-700 border border-rose-200">
                                                                <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                                                Tidak Hadir
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="px-5 py-4 text-sm text-slate-600">
                                                        {{ $attendance->akurasi_meter ? $attendance->akurasi_meter . ' m' : 'N/A' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="border-t border-slate-200 px-5 py-4">
                                    {{ $attendances->links() }}
                                </div>
                            @else
                                <div class="px-6 py-16 text-center">
                                    <div
                                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-100">
                                        <svg class="h-7 w-7 text-slate-400" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <h3 class="mt-4 text-base font-semibold text-slate-900">Tidak ada data yang cocok</h3>
                                    <p class="mt-2 text-sm text-slate-500">Coba ubah kelas, nama siswa, atau status filter.
                                    </p>
                                </div>
                            @endif
                        </section>
                    @else
                        <section
                            class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center shadow-sm">
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">
                                <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-lg font-semibold text-slate-900">Belum ada session QR</h3>
                            <p class="mt-2 text-sm text-slate-500">Generate QR terlebih dahulu supaya riwayat bisa
                                ditelusuri per session.</p>
                        </section>
                    @endif
                </main>
            </div>
        </div>
    </div>
@endsection
