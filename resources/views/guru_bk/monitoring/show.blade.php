@extends('layouts.app')
@section('title', 'Monitoring SAW — ' . ($kelas->nama_kelas ?? 'Kelas'))

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">

        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('guru_bk.monitoring.index') }}" class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Guru BK</p>
                    <h1 class="mt-1.5 text-2xl font-bold text-gray-900">{{ $kelas->nama_kelas ?? '-' }}</h1>
                    <div class="mt-1">
                        @include('partials.trend-mingguan-badge', ['trend' => $trendMingguanKelas])
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                @php
                    $filters = [
                        ['label' => 'Semua', 'value' => ''],
                        ['label' => 'Binaan', 'value' => 'binaan'],
                        ['label' => 'Perhatian', 'value' => 'perhatian'],
                        ['label' => 'Aman', 'value' => 'aman'],
                    ];
                @endphp
                @foreach ($filters as $f)
                    @php
                        $isActive = $kategoriFilter === $f['value'] || ($f['value'] === '' && empty($kategoriFilter));
                        $baseClasses = 'rounded-lg border px-3 py-1.5 text-xs font-medium transition-colors';
                        $activeClasses = 'border-pink-600 bg-pink-700 text-white';
                        $inactiveClasses = 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50';
                    @endphp
                    <a href="{{ route('guru_bk.monitoring.show', $kelas->id) . '?kategori=' . $f['value'] }}"
                       class="{{ $baseClasses }} {{ $isActive ? $activeClasses : $inactiveClasses }}">
                        {{ $f['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Tabel Siswa --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">No</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">Nama Siswa</th>
                            <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-700">Kategori</th>
                            <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-700">Skor Akhir</th>
                            <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-700">Tren Harian</th>
                            <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-700">Data</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($siswaTerurut as $i => $item)
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
                                <td class="px-5 py-3.5 text-center">
                                    @include('partials.badge-kategori', ['kategori' => $item->kategori ?? null])
                                    @if (!empty($item->data_tidak_lengkap))
                                        <span class="ml-1.5 rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-500">Data tidak lengkap</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-center text-sm font-bold font-mono text-gray-800">{{ number_format($item->skor_akhir ?? 0, 2) }}</td>
                                <td class="px-5 py-3.5 text-center">@include('partials.trend-indicator', ['trend' => $item->trend_harian ?? null])</td>
                                <td class="px-5 py-3.5 text-center text-xs text-gray-500">{{ $item->data_tidak_lengkap ? 'Tidak lengkap' : 'Lengkap' }}</td>
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('guru_bk.monitoring.siswa', [$kelas->id, $item->id]) }}" class="text-xs font-medium text-pink-700 hover:text-pink-800 transition-colors">
                                        Lihat detail →
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center">
                                    <p class="font-medium text-gray-600">Belum ada data siswa</p>
                                    <p class="mt-1 text-sm text-gray-400">Data SAW untuk kelas ini belum tersedia.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
