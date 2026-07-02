@extends('layouts.app')
@section('title', 'Monitoring Siswa — ' . ($siswa->nama ?? 'Detail'))

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">

        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('guru_bk.monitoring.show', $kelas->id) }}" class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Guru BK</p>
                    <h1 class="mt-1.5 text-2xl font-bold text-gray-900">Detail Siswa — {{ $siswa->nama ?? '-' }}</h1>
                    <p class="mt-1 text-xs text-gray-500">Kelas {{ $kelas->nama_kelas ?? '-' }} · NIS {{ $siswa->nis ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Breakdown Skor --}}
        @if ($hasilTerbaru)
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Kategori</p>
                    <div class="mt-2">@include('partials.badge-kategori', ['kategori' => $hasilTerbaru->kategori ?? null])</div>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Skor Akhir</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($hasilTerbaru->skor_akhir ?? 0, 2) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">C1 · Absensi</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($hasilTerbaru->c1 ?? 0, 2) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">C2 · Akademik</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($hasilTerbaru->c2 ?? 0, 2) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">C3 · Perilaku</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($hasilTerbaru->c3 ?? 0, 2) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">R1 · Absensi</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($hasilTerbaru->r1 ?? 0, 2) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">R2 · Akademik</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($hasilTerbaru->r2 ?? 0, 2) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">R3 · Perilaku</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($hasilTerbaru->r3 ?? 0, 2) }}</p>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Tren Harian</p>
                <div class="mt-2">@include('partials.trend-indicator', ['trend' => $trendHarian])</div>
            </div>
        @else
            <div class="rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center">
                <p class="font-medium text-gray-600">Belum ada hasil SAW</p>
                <p class="mt-1 text-sm text-gray-400">Proses perhitungan belum dilakukan untuk siswa ini.</p>
            </div>
        @endif

        {{-- Rekomendasi AI --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Rekomendasi AI</h3>
            @if ($rekomendasiAi)
                <div class="space-y-2">
                    @foreach ($rekomendasiAi as $key => $value)
                        @if (is_array($value))
                            <div>
                                <p class="text-xs font-semibold text-gray-700 uppercase tracking-wide">{{ $key }}</p>
                                <ul class="mt-1 list-disc space-y-1 pl-5 text-xs text-gray-600">
                                    @foreach ($value as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <p class="text-xs text-gray-600"><span class="font-semibold">{{ ucfirst($key) }}:</span> {{ $value }}</p>
                        @endif
                    @endforeach
                </div>
            @else
                @include('partials.saw-rekomendasi-empty')
            @endif
        </div>

        {{-- Filter Rentang --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Rentang Riwayat</h3>
            <form method="GET" action="{{ request()->fullUrlWithQuery([]) }}" class="flex flex-wrap items-center gap-2">
                @foreach ([7, 30, 90, 180, 365] as $r)
                    @php $active = request('range') == $r && !request('dari'); @endphp
                    <button type="submit" name="range" value="{{ $r }}" @if($active)disabled class="rounded-lg border border-pink-600 bg-pink-700 px-3 py-1.5 text-xs font-semibold text-white" @else class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 transition-colors" @endif>
                        {{ $r }} hari
                    </button>
                @endforeach
                <span class="text-xs text-gray-400">atau</span>
                <input type="date" name="dari" value="{{ request('dari') }}" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 focus:border-pink-600 focus:ring-2 focus:ring-pink-100">
                <span class="text-xs text-gray-400">—</span>
                <input type="date" name="sampai" value="{{ request('sampai') }}" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 focus:border-pink-600 focus:ring-2 focus:ring-pink-100">
                <button type="submit" class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 transition-colors">Terapkan</button>
            </form>
            <p class="mt-2 text-xs text-gray-400">Menampilkan: {{ \Carbon\Carbon::parse($dari)->translatedFormat('d M Y') }} — {{ \Carbon\Carbon::parse($sampai)->translatedFormat('d M Y') }}</p>
        </div>

        {{-- Chart.js --}}
        @if ($riwayat->count() > 1)
            @php
                $chartData = $riwayat->map(fn($r) => [
                    'tanggal' => \Carbon\Carbon::parse($r->tanggal_hitung)->format('d M Y'),
                    'skor' => (float) ($r->skor_akhir ?? 0),
                ])->values();
            @endphp
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Grafik Tren Skor</h3>
                <div class="relative h-72 w-full">
                    <canvas id="riwayatChart"></canvas>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const ctx = document.getElementById('riwayatChart').getContext('2d');
                    const labels = {!! json_encode($chartData->pluck('tanggal')) !!};
                    const data = {!! json_encode($chartData->pluck('skor')) !!};
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Skor Akhir',
                                data: data,
                                borderColor: '#db2777',
                                backgroundColor: 'rgba(219, 39, 119, 0.1)',
                                borderWidth: 2,
                                pointBackgroundColor: '#db2777',
                                pointRadius: 3,
                                fill: true,
                                tension: 0.2,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: {
                                    beginAtZero: false,
                                    min: 0,
                                    max: 1,
                                }
                            }
                        }
                    });
                });
            </script>
        @endif

        {{-- Tabel Riwayat --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
            <div class="px-5 py-3.5 border-b border-gray-100 bg-gray-50">
                <h3 class="text-sm font-semibold text-gray-900">Riwayat Perhitungan</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">Tanggal</th>
                            <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-700">Skor Akhir</th>
                            <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-700">Kategori</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($riwayat as $r)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3.5 text-sm text-gray-700">{{ \Carbon\Carbon::parse($r->tanggal_hitung)->translatedFormat('d M Y') }}</td>
                                <td class="px-5 py-3.5 text-center text-sm font-bold font-mono text-gray-800">{{ number_format($r->skor_akhir ?? 0, 2) }}</td>
                                <td class="px-5 py-3.5 text-center">@include('partials.badge-kategori', ['kategori' => $r->kategori ?? null])</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-5 py-10 text-center text-xs text-gray-400">Belum ada riwayat perhitungan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
