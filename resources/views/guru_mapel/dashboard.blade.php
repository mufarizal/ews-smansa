@extends('layouts.app')

@section('title', 'Dashboard Guru Mapel')

@section('content')
    <div class="min-h-screen bg-slate-50/80 py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Dashboard Guru Mapel</p>
                    <h1 class="mt-2 text-3xl font-bold text-slate-900">{{ $todayDate }}</h1>
                </div>
                <div class="flex gap-4">
                    <div class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-center shadow-sm">
                        <p class="text-xs text-slate-500">Total Kelas</p>
                        <p class="mt-1 text-2xl font-bold text-slate-900">{{ $totalKelas }}</p>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-lg font-semibold text-slate-900">Jadwal Mengajar Hari Ini</h2>
                    @if ($jadwalHariIni->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200">
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Jam</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Kelas</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Mapel</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($jadwalHariIni as $jadwal)
                                        <tr class="hover:bg-slate-50 transition">
                                            <td class="px-4 py-3 text-slate-900 font-medium">
                                                {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} -
                                                {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                                            </td>
                                            <td class="px-4 py-3 text-slate-700">{{ $jadwal->kelas->nama_kelas ?? '-' }}</td>
                                            <td class="px-4 py-3 text-slate-700">{{ $jadwal->mapel->nama_mapel ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-sm text-slate-500">Tidak ada jadwal mengajar hari ini.</p>
                    @endif
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-lg font-semibold text-slate-900">Statistik Perilaku Siswa</h2>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="rounded-xl border border-slate-200 bg-white p-4 text-center">
                            <p class="text-sm text-slate-500">Total Catatan</p>
                            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $totalPerilaku }}</p>
                        </div>
                        <div class="rounded-xl border border-emerald-200 bg-white p-4 text-center">
                            <p class="text-sm text-emerald-600">Perilaku Positif</p>
                            <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $positifCount }}</p>
                        </div>
                        <div class="rounded-xl border border-rose-200 bg-white p-4 text-center">
                            <p class="text-sm text-rose-600">Perilaku Negatif</p>
                            <p class="mt-2 text-3xl font-bold text-rose-600">{{ $negatifCount }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-lg font-semibold text-slate-900">Aktivitas Terbaru Tugas & Ujian</h2>
                    @if ($recentActivities->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200">
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Tipe</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Siswa</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Kelas</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Judul</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Nilai / Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Waktu</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($recentActivities as $activity)
                                        <tr class="hover:bg-slate-50 transition">
                                            <td class="px-4 py-3">
                                                @if ($activity['type'] === 'tugas')
                                                    <span class="inline-flex items-center rounded-full bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-700">
                                                        Tugas
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center rounded-full bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700">
                                                        Ujian
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-slate-900">{{ $activity['siswa']->nama ?? '-' }}</td>
                                            <td class="px-4 py-3 text-slate-700">{{ $activity['kelas']->nama_kelas ?? '-' }}</td>
                                            <td class="px-4 py-3 text-slate-700">{{ $activity['judul'] }}</td>
                                            <td class="px-4 py-3">
                                                @if ($activity['nilai'] !== null)
                                                    <span class="font-semibold text-slate-900">{{ $activity['nilai'] }}</span>
                                                @else
                                                    <span class="text-xs text-slate-500">
                                                        {{ match($activity['status']) {
                                                            'selesai' => 'Selesai',
                                                            'mengerjakan' => 'Sedang dikerjakan',
                                                            'tidak_mengerjakan' => 'Tidak mengerjakan',
                                                            default => '-',
                                                        } }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-slate-500 text-xs">
                                                {{ $activity['date']?->locale('id')->diffForHumans() ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-sm text-slate-500">Belum ada aktivitas tugas atau ujian yang dikerjakan siswa.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
