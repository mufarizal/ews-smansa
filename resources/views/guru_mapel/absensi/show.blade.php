@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Checklist Absensi Mapel</p>
                <h1 class="mt-2 text-3xl font-bold text-slate-900">{{ $jadwal->mapel?->nama ?? '-' }} -
                    {{ $jadwal->kelas?->nama_kelas ?? '-' }}</h1>
                <p class="mt-2 text-sm text-slate-600">{{ \Carbon\Carbon::parse($tanggal)->format('d M Y') }} • Jam
                    {{ $jadwal->getAttendanceWindowLabel() }}</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('guru_mapel.absensi.index', ['jadwal_id' => $jadwal->id]) }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                    Kembali
                </a>
                <span
                    class="inline-flex items-center rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700">
                    Checklist aktif
                </span>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-xl bg-slate-50 px-4 py-3">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Guru</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $jadwal->guru?->nama ?? '-' }}</p>
                </div>
                <div class="rounded-xl bg-slate-50 px-4 py-3">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Waktu</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $jadwal->getAttendanceWindowLabel() }}</p>
                </div>
                <div class="rounded-xl bg-slate-50 px-4 py-3">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Total Siswa</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $students->count() }} siswa</p>
                </div>
            </div>
            <p class="mt-4 text-sm text-slate-600">Centang siswa yang hadir lalu simpan. Siswa yang tidak dicentang akan
                otomatis ditandai alpha untuk jadwal ini.</p>
        </div>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
            <section class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">Daftar siswa kelas ini</h2>
                    <p class="mt-1 text-sm text-slate-500">Daftar ini mengikuti jadwal yang sedang berjalan. Checklist yang
                        dicentang akan disimpan sebagai hadir.</p>

                    <form action="{{ route('guru_mapel.absensi.store', $jadwal) }}" method="POST" class="mt-5">
                        @csrf
                        <div class="space-y-3">
                            @forelse ($students as $student)
                                @php
                                    $attendance = $attendanceByStudentId->get($student->id);
                                    $isPresent = in_array($student->id, $presentStudentIds, true);
                                    $status = $attendance?->status ?? 'belum';
                                    $statusLabel = match ($status) {
                                        'hadir' => 'Hadir',
                                        'terlambat' => 'Terlambat',
                                        'alpha' => 'Alpha',
                                        default => 'Belum dicatat',
                                    };
                                    $statusClass = match ($status) {
                                        'hadir' => 'bg-emerald-100 text-emerald-700',
                                        'terlambat' => 'bg-amber-100 text-amber-700',
                                        'alpha' => 'bg-rose-100 text-rose-700',
                                        default => 'bg-slate-100 text-slate-600',
                                    };
                                @endphp

                                <label
                                    class="flex cursor-pointer items-center justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 transition hover:border-slate-300 hover:bg-white">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">{{ $student->nama }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $student->nis ?? '-' }}</p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                                        <input type="checkbox" name="hadir_siswa_ids[]" value="{{ $student->id }}"
                                            class="h-5 w-5 rounded border-slate-300 text-slate-900 focus:ring-slate-900"
                                            @checked($isPresent)>
                                    </div>
                                </label>
                            @empty
                                <div
                                    class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                                    Belum ada siswa di kelas ini.
                                </div>
                            @endforelse
                        </div>

                        @if ($students->isNotEmpty())
                            <div class="mt-5 flex justify-end">
                                <button type="submit"
                                    class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                                    Simpan Checklist
                                </button>
                            </div>
                        @endif
                    </form>
                </div>
            </section>

            <section class="space-y-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">Ringkasan status</h2>
                    <p class="mt-1 text-sm text-slate-500">Status otomatis terbentuk dari checklist yang disimpan.</p>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-xl bg-slate-50 px-4 py-3">
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Hadir</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ count($presentStudentIds) }} siswa</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-4 py-3">
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Alpha</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">
                                {{ max($students->count() - count($presentStudentIds), 0) }} siswa</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">Riwayat terkini</h2>
                    <p class="mt-1 text-sm text-slate-500">Data tersimpan dari jadwal ini pada hari berjalan.</p>

                    <div class="mt-4 space-y-3">
                        @forelse ($attendances as $attendance)
                            <article class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">
                                            {{ $attendance->siswa?->nama ?? '-' }}</p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ $attendance->siswa?->kelas?->nama_kelas ?? '-' }} •
                                            {{ $attendance->jam_masuk?->format('H:i:s') ?? '-' }}</p>
                                    </div>
                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-semibold {{ $attendance->status === 'hadir' ? 'bg-emerald-100 text-emerald-700' : ($attendance->status === 'terlambat' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') }}">{{ ucfirst($attendance->status ?? 'belum') }}</span>
                                </div>
                            </article>
                        @empty
                            <div
                                class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                                Belum ada checklist tersimpan untuk jadwal ini.
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
