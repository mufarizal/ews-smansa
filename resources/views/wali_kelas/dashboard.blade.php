@extends('layouts.app')
@section('title', 'Dashboard Wali Kelas')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">

        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Wali Kelas</p>
                <h1 class="mt-1.5 text-2xl font-bold text-gray-900">Dashboard</h1>
                <p class="mt-1 text-sm text-gray-500">Ringkasan status Early Warning System kelas yang Anda ampu.</p>
            </div>
        </div>

        @if (!$kelas)
            <div class="rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center">
                <h2 class="text-lg font-semibold text-gray-900">Belum ada kelas yang diampu</h2>
                <p class="mt-2 text-sm text-gray-500">Data kelas akan muncul setelah Anda ditetapkan sebagai wali kelas.</p>
            </div>
        @else
            {{-- Semester Aktif + Trend Mingguan --}}
            @if ($semester)
                <div class="flex flex-wrap items-center gap-3 rounded-lg border border-blue-100 bg-blue-50 px-4 py-2.5">
                    <span class="h-2 w-2 shrink-0 rounded-full bg-blue-500"></span>
                    <span class="text-sm text-blue-800">
                        Semester aktif: <strong>{{ $semester->nama }}</strong>
                        <span class="text-blue-600 text-xs ml-1">
                            ({{ \Carbon\Carbon::parse($semester->tanggal_mulai)->translatedFormat('d M Y') }}
                            – {{ \Carbon\Carbon::parse($semester->tanggal_selesai)->translatedFormat('d M Y') }})
                        </span>
                    </span>
                    @include('partials.trend-mingguan-badge', ['trend' => $trendMingguanKelas])
                </div>
            @endif

            {{-- Stat Ringkasan Global --}}
            @if ($ringkasan->total > 0)
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                        <p class="text-3xl font-bold text-gray-800">{{ $ringkasan->total }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">Total Siswa</p>
                    </div>
                    <div class="rounded-xl border border-rose-200 bg-rose-50 p-5 shadow-sm">
                        <p class="text-3xl font-bold text-rose-600">{{ $ringkasan->binaan }}</p>
                        <p class="text-sm font-medium text-rose-700 mt-0.5">Binaan</p>
                    </div>
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                        <p class="text-3xl font-bold text-amber-600">{{ $ringkasan->perhatian }}</p>
                        <p class="text-sm font-medium text-amber-700 mt-0.5">Perhatian</p>
                    </div>
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                        <p class="text-3xl font-bold text-emerald-600">{{ $ringkasan->aman }}</p>
                        <p class="text-sm font-medium text-emerald-700 mt-0.5">Aman</p>
                    </div>
                </div>
            @endif

            {{-- Siswa Prioritas --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="border-b border-gray-100 bg-gray-50 px-5 py-3.5">
                    <h2 class="text-sm font-semibold text-gray-900">Siswa Prioritas</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Diurutkan dari skor terendah</p>
                </div>
                @if ($siswaTerurut->isEmpty())
                    <div class="py-12 text-center">
                        <p class="text-sm text-gray-400">Belum ada data SAW.</p>
                        <p class="text-xs text-gray-300 mt-1">Generate analisis SAW terlebih dahulu.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 bg-gray-50">
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">No</th>
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">Nama Siswa</th>
                                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-700">Kategori</th>
                                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-700">Skor Akhir</th>
                                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-700">Tren Harian</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($siswaTerurut as $i => $item)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-5 py-3.5 text-xs font-mono text-gray-400">{{ $i + 1 }}</td>
                                        <td class="px-5 py-3.5">
                                            <div class="flex items-center gap-2.5">
                                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-600">
                                                    {{ strtoupper(substr($item->siswa->nama ?? '?', 0, 1)) }}
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $item->siswa->nama ?? '-' }}</p>
                                                    <p class="text-xs text-gray-400">NIS {{ $item->siswa->nis ?? '' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3.5 text-center">@include('partials.badge-kategori', ['kategori' => $item->kategori ?? null])</td>
                                        <td class="px-5 py-3.5 text-center text-sm font-bold font-mono text-gray-800">{{ number_format($item->skor_akhir ?? 0, 2) }}</td>
                                        <td class="px-5 py-3.5 text-center">@include('partials.trend-indicator', ['trend' => $item->trend_harian ?? null])</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Jadwal Hari Ini --}}
            @if ($jadwals->isNotEmpty())
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                    <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-emerald-800">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Jadwal Mengampu — {{ $todayHari }}
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($jadwals as $j)
                            <div class="flex items-center gap-2.5 rounded-lg border border-emerald-200 bg-white px-3 py-2">
                                <span class="font-mono text-xs font-semibold text-emerald-700">{{ substr((string) $j->jam_mulai, 0, 5) }}</span>
                                <span class="h-3 w-px bg-emerald-200"></span>
                                <span class="text-xs font-medium text-gray-700">{{ $j->mapel?->nama ?? '–' }}</span>
                                <span class="rounded bg-emerald-100 px-1.5 py-0.5 text-xs text-emerald-700">{{ $j->kelas?->nama_kelas ?? '–' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif

    </div>
@endsection
