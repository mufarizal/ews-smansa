@extends('layouts.app')

@section('title', 'Dashboard Siswa')

@section('content')
<div class="mx-auto max-w-7xl">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Dashboard</p>
            <h1 class="mt-1.5 text-2xl font-bold text-gray-900">Selamat Datang, {{ $siswa->nama }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $siswa->kelas->nama_kelas ?? '-' }} • NIS: {{ $siswa->nis }}</p>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-emerald-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Tugas (Minggu Ini)</p>
            <p class="mt-2 text-3xl font-bold text-emerald-700">{{ $summary['totalTugas'] ?? 0 }}</p>
            <p class="mt-1 text-xs text-slate-500">Selesai: {{ $summary['tugasSelesai'] ?? 0 }}</p>
        </div>
        <div class="rounded-2xl border border-blue-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Ujian (Minggu Ini)</p>
            <p class="mt-2 text-3xl font-bold text-blue-700">{{ $summary['totalUjianPublish'] ?? 0 }}</p>
            <p class="mt-1 text-xs text-slate-500">Dikerjakan: {{ $summary['ujianDikerjakan'] ?? 0 }}</p>
        </div>
        <div class="rounded-2xl border border-sky-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Absensi Harian (Bulan Ini)</p>
            <p class="mt-2 text-3xl font-bold text-sky-700">{{ $summary['harianHadir'] ?? 0 }}/{{ $summary['totalAbsensiHarian'] ?? 0 }}</p>
            <p class="mt-1 text-xs text-slate-500">Hadir dari total catatan</p>
        </div>
        <div class="rounded-2xl border border-indigo-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Absensi Mapel (Bulan Ini)</p>
            <p class="mt-2 text-3xl font-bold text-indigo-700">{{ $summary['mapelHadir'] ?? 0 }}/{{ $summary['totalAbsensiMapel'] ?? 0 }}</p>
            <p class="mt-1 text-xs text-slate-500">Hadir dari total catatan</p>
        </div>
    </div>

    @if ($hariIni->isNotEmpty())
        <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold text-slate-900">Jadwal Pelajaran</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-200 bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Jam</th>
                            <th class="px-4 py-2 text-left">Mapel</th>
                            <th class="px-4 py-2 text-left">Guru</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($hariIni as $jadwal)
                            <tr class="border-b border-gray-100">
                                <td class="px-4 py-2">{{ $jadwal->jam_mulai }} - {{ $jadwal->jam_selesai }}</td>
                                <td class="px-4 py-2">{{ $jadwal->mapel->nama ?? '-' }}</td>
                                <td class="px-4 py-2">{{ $jadwal->guru->nama ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
