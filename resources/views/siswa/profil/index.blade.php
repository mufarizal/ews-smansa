@extends('layouts.app')

@section('title', 'Profil Perkembangan Saya')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Profil Perkembangan</p>
            <h1 class="mt-1.5 text-2xl font-bold text-gray-900">{{ $siswa->nama }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $siswa->kelas->nama_kelas ?? '-' }} • NIS: {{ $siswa->nis }}</p>
        </div>

        <form method="GET" class="flex items-end gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div>
                <label class="mb-1 block text-xs text-gray-600">Bulan</label>
                <select name="bulan" class="rounded-lg border border-gray-300 px-2 py-1 text-sm" onchange="this.form.submit()">
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>{{ DateTime::createFromFormat('!m', $i)->format('F') }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs text-gray-600">Tahun</label>
                <select name="tahun" class="rounded-lg border border-gray-300 px-2 py-1 text-sm" onchange="this.form.submit()">
                    @for ($i = now()->year - 1; $i <= now()->year + 1; $i++)
                        <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
        </form>
    </div>

    {{-- Ringkasan --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        <div class="rounded-2xl border border-emerald-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Tugas</p>
            <p class="mt-2 text-3xl font-bold text-emerald-700">{{ $summary['totalTugas'] ?? 0 }}</p>
            <p class="mt-1 text-xs text-slate-500">Selesai: {{ $summary['tugasSelesai'] ?? 0 }}</p>
        </div>
        <div class="rounded-2xl border border-blue-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Ujian</p>
            <p class="mt-2 text-3xl font-bold text-blue-700">{{ $summary['totalUjianPublish'] ?? 0 }}</p>
            <p class="mt-1 text-xs text-slate-500">Dikerjakan: {{ $summary['ujianDikerjakan'] ?? 0 }}</p>
        </div>
        <div class="rounded-2xl border border-violet-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Rata-rata Nilai</p>
            <p class="mt-2 text-3xl font-bold text-violet-700">{{ number_format($rataTugas + $rataUjian > 0 ? ($rataTugas + $rataUjian) / 2 : 0, 1) }}</p>
            <p class="mt-1 text-xs text-slate-500">Tugas & Ujian</p>
        </div>
        <div class="rounded-2xl border border-sky-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Kehadiran</p>
            <p class="mt-2 text-3xl font-bold text-sky-700">{{ number_format($kehadiran, 1) }}%</p>
            <p class="mt-1 text-xs text-slate-500">Mapel</p>
        </div>
        <div class="rounded-2xl border border-amber-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Skor Sikap</p>
            <p class="mt-2 text-3xl font-bold text-amber-700">{{ $skorPerilaku }}</p>
            <p class="mt-1 text-xs text-slate-500">Positif − Negatif</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Status Perkembangan</p>
            <div class="mt-3">@include('partials.badge-status-siswa', ['kategori' => $hasilEws->kategori ?? null])</div>
        </div>
    </div>

    {{-- Status & Perkembangan --}}
    @if ($hasilEws)
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-slate-900">Gambaran Perkembangan</h3>
            <p class="mt-1 text-xs text-gray-500">Ringkasan tiga aspek utama di semester ini.</p>

            <div class="mt-5 grid gap-5 sm:grid-cols-3">
                @php
                    $aspek = [
                        ['label' => 'Akademik', 'value' => $hasilEws->c1_akademik, 'color' => 'bg-violet-500'],
                        ['label' => 'Kehadiran', 'value' => $hasilEws->c2_absensi, 'color' => 'bg-sky-500'],
                        ['label' => 'Sikap & Perilaku', 'value' => $hasilEws->c3_perilaku, 'color' => 'bg-amber-500'],
                    ];
                @endphp
                @foreach ($aspek as $a)
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-medium text-slate-700">{{ $a['label'] }}</span>
                            <span class="text-slate-500">{{ number_format($a['value'], 1) }}</span>
                        </div>
                        <div class="mt-2 h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                            <div class="{{ $a['color'] }} h-2.5 rounded-full" style="width: {{ max(0, min(100, $a['value'])) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center">
            <p class="font-medium text-gray-600">Belum ada data perkembangan</p>
            <p class="mt-1 text-sm text-gray-400">Proses perhitungan belum dilakukan untuk periode ini.</p>
        </div>
    @endif

    {{-- Grafik Perkembangan --}}
    @if ($riwayatEws->count() > 1)
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-slate-900">Grafik Perkembangan Saya</h3>
            @php
                $chartData = $riwayatEws->map(fn($r) => [
                    'tanggal' => \Carbon\Carbon::parse($r->tanggal_hitung)->format('d M Y'),
                    'skor' => (float) ($r->skor_akhir ?? 0),
                ])->values();
                $chartScores = $chartData->pluck('skor');
                $chartMin = max(0, floor(($chartScores->min() - 0.05) * 100) / 100);
                $chartMax = min(1, ceil(($chartScores->max() + 0.05) * 100) / 100);
            @endphp
            <div class="relative mt-4 h-72 w-full">
                <canvas id="perkembanganChart"></canvas>
            </div>
        </div>

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const canvas = document.getElementById('perkembanganChart');
                    if (!canvas || typeof Chart === 'undefined') return;

                    const ctx = canvas.getContext('2d');
                    const labels = @json($chartData->pluck('tanggal'));
                    const data = @json($chartData->pluck('skor'));

                    const gradient = ctx.createLinearGradient(0, 0, 0, 260);
                    gradient.addColorStop(0, 'rgba(16, 185, 129, 0.25)');
                    gradient.addColorStop(1, 'rgba(16, 185, 129, 0.02)');

                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Skor Perkembangan',
                                data: data,
                                borderColor: '#10b981',
                                backgroundColor: gradient,
                                borderWidth: 2.5,
                                pointBackgroundColor: '#10b981',
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
                                legend: { display: false },
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
                                    ticks: { callback: function(value) { return Number(value).toFixed(2); } },
                                    grid: { color: 'rgba(148, 163, 184, 0.16)' }
                                },
                                x: { grid: { display: false } }
                            }
                        }
                    });
                });
            </script>
        @endpush
    @endif

    {{-- Catatan & Saran --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-base font-semibold text-slate-900">Catatan & Saran untuk Saya</h3>
                <p class="mt-1 text-xs text-gray-500">Hasil refleksi kondisimu dan langkah yang bisa kamu lakukan.</p>
            </div>
            <form method="POST" action="{{ route('siswa.profil.generate-saran') }}">
                @csrf
                <button type="submit"
                    class="rounded-lg border border-emerald-600 bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 transition-colors">
                    Muat ulang saran saya
                </button>
            </form>
        </div>

        @if ($rekomendasi && !empty($rekomendasi->rekomendasi))
            @php $rec = $rekomendasi->rekomendasi[0] ?? $rekomendasi->rekomendasi; @endphp
            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4">
                    <p class="mb-2 text-sm font-semibold text-rose-800">Kondisimu
                        <span class="ml-2 rounded-full bg-white px-2 py-0.5 text-[10px] font-semibold text-rose-700">{{ count($rec['penyebab'] ?? []) }} poin</span>
                    </p>
                    @if (!empty($rec['penyebab']))
                        <ul class="space-y-2 text-sm text-rose-900">
                            @foreach ($rec['penyebab'] as $item)
                                <li class="rounded-lg bg-white/80 px-3 py-2">{{ $item }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-rose-900">Belum ada rincian.</p>
                    @endif
                </div>

                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                    <p class="mb-2 text-sm font-semibold text-emerald-800">Saran untukmu
                        <span class="ml-2 rounded-full bg-white px-2 py-0.5 text-[10px] font-semibold text-emerald-700">{{ count($rec['saran'] ?? []) }} poin</span>
                    </p>
                    @if (!empty($rec['saran']))
                        <ul class="space-y-2 text-sm text-emerald-900">
                            @foreach ($rec['saran'] as $item)
                                <li class="rounded-lg bg-white/80 px-3 py-2">{{ $item }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-emerald-900">Belum ada saran.</p>
                    @endif
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-400">
                Diperbarui {{ \Carbon\Carbon::parse($rekomendasi->generated_at)->diffForHumans() }}
                @if ($rekomendasi->provider_used)
                    • {{ $rekomendasi->provider_used }}
                @endif
            </p>
        @else
            <div class="mt-4 rounded-xl border border-dashed border-gray-300 bg-gray-50 p-6 text-center">
                <p class="text-sm text-gray-600">Belum ada catatan & saran.</p>
                <p class="mt-1 text-xs text-gray-400">Klik "Muat ulang saran saya" untuk menghasilkan refleksi perkembanganmu.</p>
            </div>
        @endif
    </div>

    {{-- Tabs --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
        <div class="flex gap-1 border-b border-gray-200 bg-gray-50 px-2">
            <a href="{{ route('siswa.profil.index', ['tab' => 'nilai', 'bulan' => $bulan, 'tahun' => $tahun]) }}"
                class="rounded-t-lg px-4 py-3 text-sm font-medium {{ $tab === 'nilai' ? 'border-b-2 border-emerald-600 bg-white text-emerald-900' : 'text-gray-600 hover:bg-white/50' }}">
                Akademik
            </a>
            <a href="{{ route('siswa.profil.index', ['tab' => 'absensi', 'bulan' => $bulan, 'tahun' => $tahun]) }}"
                class="rounded-t-lg px-4 py-3 text-sm font-medium {{ $tab === 'absensi' ? 'border-b-2 border-emerald-600 bg-white text-emerald-900' : 'text-gray-600 hover:bg-white/50' }}">
                Absensi
            </a>
            <a href="{{ route('siswa.profil.index', ['tab' => 'perilaku', 'bulan' => $bulan, 'tahun' => $tahun]) }}"
                class="rounded-t-lg px-4 py-3 text-sm font-medium {{ $tab === 'perilaku' ? 'border-b-2 border-emerald-600 bg-white text-emerald-900' : 'text-gray-600 hover:bg-white/50' }}">
                Sikap & Perilaku
            </a>
        </div>

        <div class="p-6">
            @if ($tab === 'nilai')
                <div class="space-y-6">
                    <div>
                        <h3 class="mb-3 text-base font-semibold text-slate-900">Tugas Terbaru</h3>
                        @if ($tugasRiwayat->isEmpty())
                            <p class="text-sm text-gray-500">Belum ada tugas.</p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="border-b border-gray-200 bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left">Tugas</th>
                                            <th class="px-4 py-2 text-left">Mapel</th>
                                            <th class="px-4 py-2 text-center">Nilai</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($tugasRiwayat as $tugas)
                                            @php $nilai = $tugas->nilaiTugas->first(); @endphp
                                            <tr class="border-b border-gray-100">
                                                <td class="px-4 py-2">{{ $tugas->judul }}</td>
                                                <td class="px-4 py-2">{{ $tugas->guruMapelKelas->mapel->nama ?? '-' }}</td>
                                                <td class="px-4 py-2 text-center">{{ $nilai?->nilai ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    <div>
                        <h3 class="mb-3 text-base font-semibold text-slate-900">Ujian Terbaru</h3>
                        @if ($ujianRiwayat->isEmpty())
                            <p class="text-sm text-gray-500">Belum ada ujian.</p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="border-b border-gray-200 bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left">Ujian</th>
                                            <th class="px-4 py-2 text-left">Mapel</th>
                                            <th class="px-4 py-2 text-center">Nilai</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($ujianRiwayat as $hasil)
                                            <tr class="border-b border-gray-100">
                                                <td class="px-4 py-2">{{ $hasil->ujianHarian->judul ?? '-' }}</td>
                                                <td class="px-4 py-2">{{ $hasil->ujianHarian->guruMapelKelas->mapel->nama ?? '-' }}</td>
                                                <td class="px-4 py-2 text-center">{{ $hasil->nilai }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @elseif ($tab === 'absensi')
                <div class="space-y-6">
                    <div>
                        <h3 class="mb-3 text-base font-semibold text-slate-900">Absensi Harian</h3>
                        @if ($absensiHarian->isEmpty())
                            <p class="text-sm text-gray-500">Belum ada absensi harian.</p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="border-b border-gray-200 bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left">Tanggal</th>
                                            <th class="px-4 py-2 text-left">Status</th>
                                            <th class="px-4 py-2 text-center">Terlambat</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($absensiHarian as $a)
                                            <tr class="border-b border-gray-100">
                                                <td class="px-4 py-2">{{ \Carbon\Carbon::parse($a->tanggal)->format('d/m/Y') }}</td>
                                                <td class="px-4 py-2">{{ ucfirst($a->status) }}</td>
                                                <td class="px-4 py-2 text-center">{{ $a->terlambat_menit }} menit</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    <div>
                        <h3 class="mb-3 text-base font-semibold text-slate-900">Absensi Mapel</h3>
                        @if ($absensiMapel->isEmpty())
                            <p class="text-sm text-gray-500">Belum ada absensi mapel.</p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="border-b border-gray-200 bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left">Tanggal</th>
                                            <th class="px-4 py-2 text-left">Mapel</th>
                                            <th class="px-4 py-2 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($absensiMapel as $a)
                                            <tr class="border-b border-gray-100">
                                                <td class="px-4 py-2">{{ \Carbon\Carbon::parse($a->tanggal)->format('d/m/Y') }}</td>
                                                <td class="px-4 py-2">{{ $a->jadwal?->mapel?->nama ?? $a->mapel?->nama ?? '-' }}</td>
                                                <td class="px-4 py-2 text-center">{{ ucfirst($a->status) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @elseif ($tab === 'perilaku')
                <div class="space-y-6">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5">
                            <p class="text-sm text-emerald-700">Poin Positif</p>
                            <p class="mt-2 text-3xl font-bold text-emerald-700">{{ $perilakuPositif }}</p>
                        </div>
                        <div class="rounded-xl border border-rose-200 bg-rose-50 p-5">
                            <p class="text-sm text-rose-700">Poin Negatif</p>
                            <p class="mt-2 text-3xl font-bold text-rose-700">{{ $perilakuNegatif }}</p>
                        </div>
                    </div>

                    <div>
                        <h3 class="mb-3 text-base font-semibold text-slate-900">Riwayat Sikap & Perilaku</h3>
                        @if ($riwayatPerilaku->isEmpty())
                            <p class="text-sm text-gray-500">Belum ada catatan perilaku.</p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="border-b border-gray-200 bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left">Tanggal</th>
                                            <th class="px-4 py-2 text-left">Jenis</th>
                                            <th class="px-4 py-2 text-left">Keterangan</th>
                                            <th class="px-4 py-2 text-center">Poin</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($riwayatPerilaku as $p)
                                            <tr class="border-b border-gray-100">
                                                <td class="px-4 py-2">{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}</td>
                                                <td class="px-4 py-2">
                                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $p->perilaku->jenis === 'positif' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                                        {{ ucfirst($p->perilaku->jenis) }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-2">{{ $p->perilaku->nama_perilaku ?? '-' }}@if ($p->catatan) — {{ $p->catatan }}@endif</td>
                                                <td class="px-4 py-2 text-center font-semibold {{ $p->perilaku->jenis === 'positif' ? 'text-emerald-700' : 'text-rose-700' }}">{{ $p->perilaku->poin }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
