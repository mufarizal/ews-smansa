@extends('layouts.app')
@section('title', 'Monitoring Siswa — ' . ($siswa->nama ?? 'Detail'))

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">

        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('guru_bk.monitoring.show', $kelas->id) }}"
                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Guru BK</p>
                    <h1 class="mt-1.5 text-2xl font-bold text-gray-900">Detail Siswa — {{ $siswa->nama ?? '-' }}</h1>
                    <p class="mt-1 text-xs text-gray-500">Kelas {{ $kelas->nama_kelas ?? '-' }} · NIS
                        {{ $siswa->nis ?? '-' }}</p>
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
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($hasilTerbaru->skor_akhir ?? 0, 2) }}
                    </p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">C1 · Akademik</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($hasilTerbaru->c1_akademik ?? 0, 2) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">C2 · Absensi</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($hasilTerbaru->c2_absensi ?? 0, 2) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">C3 · Perilaku</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($hasilTerbaru->c3_perilaku ?? 0, 2) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">R1 · Akademik</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($hasilTerbaru->r1_akademik ?? 0, 2) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">R2 · Absensi</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($hasilTerbaru->r2_absensi ?? 0, 2) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">R3 · Perilaku</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($hasilTerbaru->r3_perilaku ?? 0, 2) }}</p>
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
        @if ($rekomendasiAi)
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Rekomendasi AI</h3>
                        <p class="mt-1 text-xs text-gray-500">Hasil analisis dan saran untuk siswa ini.
                        </p>
                    </div>
                    @if ($aiRekomendasiHistory->count() > 1)
                        <form method="GET" action="{{ route('guru_bk.monitoring.siswa', [$kelas->id, $siswa->id]) }}"
                            class="flex items-center gap-2">
                            <input type="hidden" name="range" value="{{ request('range') }}">
                            <input type="hidden" name="dari" value="{{ request('dari') }}">
                            <input type="hidden" name="sampai" value="{{ request('sampai') }}">
                            <select name="ai_filter_id" onchange="this.form.submit()"
                                class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 focus:border-pink-600 focus:ring-2 focus:ring-pink-100">
                                <option value="">Rekomendasi Terbaru</option>
                                @foreach ($aiRekomendasiHistory as $record)
                                    @php
                                        $isSelected = $aiFilterId == $record->id;
                                    @endphp
                                    <option value="{{ $record->id }}" @if($isSelected) selected @endif>
                                        {{ $record->provider_used ?? 'unknown' }} —
                                        {{ \Carbon\Carbon::parse($record->generated_at)->translatedFormat('d M Y H:i') }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    @endif
                </div>

                @if (!empty($rekomendasiAi['generated_at']))
                    <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                        <span>Terakhir diperbarui
                            {{ \Carbon\Carbon::parse($rekomendasiAi['generated_at'])->diffForHumans() }}</span>
                        @if (!empty($rekomendasiAi['provider_used']))
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-600">
                                {{ $rekomendasiAi['provider_used'] }}
                            </span>
                        @endif
                    </div>
                @endif

                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    <div class="rounded-xl border border-rose-200 bg-rose-50 p-4">
                        <p class="text-sm font-semibold text-rose-800 mb-2">Penyebab
                            <span class="ml-2 rounded-full bg-white px-2 py-0.5 text-[10px] font-semibold text-rose-700">{{ count($rekomendasiAi['penyebab'] ?? []) }} poin</span>
                        </p>
                        @if (!empty($rekomendasiAi['penyebab']))
                            <ul class="space-y-2 text-sm text-rose-900">
                                @foreach ($rekomendasiAi['penyebab'] as $item)
                                    <li class="rounded-lg bg-white/80 px-3 py-2">{{ $item }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="mt-3 text-sm text-rose-900">Tidak ada rincian penyebab.</p>
                        @endif
                    </div>

                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                        <p class="text-sm font-semibold text-emerald-800 mb-2">Saran
                            <span class="ml-2 rounded-full bg-white px-2 py-0.5 text-[10px] font-semibold text-emerald-700">{{ count($rekomendasiAi['saran'] ?? []) }} poin</span>
                        </p>
                        @if (!empty($rekomendasiAi['saran']))
                            <ul class="space-y-2 text-sm text-emerald-900">
                                @foreach ($rekomendasiAi['saran'] as $item)
                                    <li class="rounded-lg bg-white/80 px-3 py-2">{{ $item }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="mt-3 text-sm text-emerald-900">Tidak ada saran yang diberikan.</p>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Rekomendasi AI</h3>
                        <p class="mt-1 text-xs text-gray-500">Hasil rekomendasi akan muncul otomatis setelah analisis selesai dihitung.
                        </p>
                    </div>
                </div>
                <div class="mt-3">
                    @include('partials.saw-rekomendasi-empty')
                </div>
            </div>
        @endif

        {{-- Filter Rentang --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Rentang Riwayat</h3>
            <form method="GET" action="{{ route('guru_bk.monitoring.siswa', [$kelas->id, $siswa->id]) }}"
                class="flex flex-wrap items-center gap-2">
                @foreach ([7, 30, 90, 180, 365] as $r)
                    @php $active = request('range') == $r && !request('dari'); @endphp
                    <button type="submit" name="range" value="{{ $r }}"
                        @if ($active) disabled class="rounded-lg border border-pink-600 bg-pink-700 px-3 py-1.5 text-xs font-semibold text-white" @else class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 transition-colors" @endif>
                        {{ $r }} hari
                    </button>
                @endforeach
                <span class="text-xs text-gray-400">atau</span>
                <input type="date" name="dari" value="{{ request('dari') }}"
                    class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 focus:border-pink-600 focus:ring-2 focus:ring-pink-100">
                <span class="text-xs text-gray-400">—</span>
                <input type="date" name="sampai" value="{{ request('sampai') }}"
                    class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 focus:border-pink-600 focus:ring-2 focus:ring-pink-100">
                <button type="submit"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 transition-colors">Terapkan</button>
                @if (request()->hasAny(['range', 'dari', 'sampai']))
                    <a href="{{ route('guru_bk.monitoring.siswa', [$kelas->id, $siswa->id]) }}"
                        class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                        Reset
                    </a>
                @endif
            </form>
            <p class="mt-2 text-xs text-gray-400">Menampilkan:
                {{ \Carbon\Carbon::parse($dari)->translatedFormat('d M Y') }} —
                {{ \Carbon\Carbon::parse($sampai)->translatedFormat('d M Y') }}</p>
        </div>

        {{-- Chart.js --}}
        @if ($riwayat->count() > 1)
            @php
                $chartData = $riwayat
                    ->map(
                        fn($r) => [
                            'tanggal' => \Carbon\Carbon::parse($r->tanggal_hitung)->format('d M Y'),
                            'skor' => (float) ($r->skor_akhir ?? 0),
                        ],
                    )
                    ->values();
                $chartScores = $chartData->pluck('skor');
                $chartMin = max(0, floor(($chartScores->min() - 0.05) * 100) / 100);
                $chartMax = min(1, ceil(($chartScores->max() + 0.05) * 100) / 100);
            @endphp
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Grafik Tren Skor</h3>
                <div class="relative h-72 w-full">
                    <canvas id="riwayatChart"></canvas>
                </div>
            </div>

            @push('scripts')
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const canvas = document.getElementById('riwayatChart');
                        if (!canvas || typeof Chart === 'undefined') {
                            return;
                        }

                        const ctx = canvas.getContext('2d');
                        const labels = @json($chartData->pluck('tanggal'));
                        const data = @json($chartData->pluck('skor'));

                        const gradient = ctx.createLinearGradient(0, 0, 0, 260);
                        gradient.addColorStop(0, 'rgba(219, 39, 119, 0.25)');
                        gradient.addColorStop(1, 'rgba(219, 39, 119, 0.02)');

                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Skor Akhir',
                                    data: data,
                                    borderColor: '#db2777',
                                    backgroundColor: gradient,
                                    borderWidth: 2.5,
                                    pointBackgroundColor: '#db2777',
                                    pointBorderColor: '#ffffff',
                                    pointBorderWidth: 2,
                                    pointRadius: 4,
                                    pointHoverRadius: 5,
                                    fill: true,
                                    tension: 0.35,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return 'Skor: ' + Number(context.parsed.y).toFixed(2);
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        min: {{ $chartMin }},
                                        max: {{ $chartMax }},
                                        ticks: {
                                            callback: function(value) {
                                                return Number(value).toFixed(2);
                                            }
                                        },
                                        grid: {
                                            color: 'rgba(148, 163, 184, 0.16)'
                                        }
                                    },
                                    x: {
                                        grid: {
                                            display: false
                                        }
                                    }
                                }
                            }
                        });
                    });
                </script>
            @endpush
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
                                <td class="px-5 py-3.5 text-sm text-gray-700">
                                    {{ \Carbon\Carbon::parse($r->tanggal_hitung)->translatedFormat('d M Y') }}</td>
                                <td class="px-5 py-3.5 text-center text-sm font-bold font-mono text-gray-800">
                                    {{ number_format($r->skor_akhir ?? 0, 2) }}</td>
                                <td class="px-5 py-3.5 text-center">@include('partials.badge-kategori', ['kategori' => $r->kategori ?? null])</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-5 py-10 text-center text-xs text-gray-400">Belum ada riwayat
                                    perhitungan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
