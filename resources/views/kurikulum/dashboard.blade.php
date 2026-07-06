@extends('layouts.app')

@section('title', 'Dashboard Kurikulum')

@section('content')
    <div class="min-h-screen bg-slate-50/80 py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Dashboard Kurikulum</p>
                <h1 class="mt-2 text-3xl font-bold text-slate-900">Ringkasan Akademik</h1>
            </div>

            @if ($activeSemester)
                <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Semester Aktif</p>
                    <div class="mt-2 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="text-xl font-bold text-slate-900">{{ $activeSemester->nama }}</h2>
                        <p class="text-sm text-slate-600">
                            Periode:
                            {{ \Carbon\Carbon::parse($activeSemester->tanggal_mulai)->locale('id')->isoFormat('D MMMM YYYY') }}
                            -
                            {{ \Carbon\Carbon::parse($activeSemester->tanggal_selesai)->locale('id')->isoFormat('D MMMM YYYY') }}
                            @if ($activeSemester->keterangan)
                                <span class="text-slate-400">| {{ $activeSemester->keterangan }}</span>
                            @endif
                        </p>
                    </div>
                </div>
            @else
                <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-6 shadow-sm">
                    <p class="text-sm font-semibold text-amber-800">Belum ada semester aktif.</p>
                    <a href="{{ route('kurikulum.semesters.index') }}"
                        class="mt-2 inline-flex items-center gap-2 rounded-lg bg-amber-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-800">
                        Aktifkan Semester
                    </a>
                </div>
            @endif

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Total Mapel</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ $summary['totalMapel'] }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Total Kelas</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ $summary['totalKelas'] }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Total Guru</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ $summary['totalGuru'] }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Total Siswa</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ $summary['totalSiswa'] }}</p>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">Jadwal Hari Ini ({{ $todayHari }})</h2>
                    @if ($jadwalHariIni->count() > 6)
                        <span class="text-xs font-medium text-slate-400">{{ $jadwalHariIni->count() }} jadwal</span>
                    @endif
                </div>

                @if ($jadwalHariIni->isNotEmpty())
                    <div
                        class="mt-4 max-h-72 overflow-y-auto pr-1 -mr-1
                    [&::-webkit-scrollbar]:w-1.5
                    [&::-webkit-scrollbar-track]:bg-transparent
                    [&::-webkit-scrollbar-thumb]:rounded-full
                    [&::-webkit-scrollbar-thumb]:bg-slate-200">
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            @foreach ($jadwalHariIni as $jadwal)
                                <div
                                    class="rounded-xl border border-slate-200 p-3 hover:border-slate-300 hover:bg-slate-50 transition">
                                    <p class="text-[11px] font-medium text-slate-400">
                                        {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }}
                                        -
                                        {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                                    </p>
                                    <p class="mt-1.5 truncate text-sm font-semibold text-slate-900">
                                        {{ $jadwal->kelas->nama_kelas ?? '-' }}
                                    </p>
                                    <p class="truncate text-xs text-slate-500">{{ $jadwal->mapel->nama ?? '-' }}</p>
                                    <p class="truncate text-xs text-slate-400">{{ $jadwal->guru->nama ?? '-' }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <p class="mt-4 text-sm text-slate-500">Tidak ada jadwal pelajaran hari ini.</p>
                @endif
            </div>
        </div>
    </div>
@endsection
