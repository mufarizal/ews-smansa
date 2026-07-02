@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="mx-auto max-w-7xl">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Profil Saya</p>
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

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
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
        <div class="rounded-2xl border border-sky-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Absensi Harian</p>
            <p class="mt-2 text-3xl font-bold text-sky-700">{{ $summary['harianHadir'] ?? 0 }}/{{ $summary['totalAbsensiHarian'] ?? 0 }}</p>
            <p class="mt-1 text-xs text-slate-500">Hadir dari total catatan</p>
        </div>
        <div class="rounded-2xl border border-indigo-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Absensi Mapel</p>
            <p class="mt-2 text-3xl font-bold text-indigo-700">{{ $summary['mapelHadir'] ?? 0 }}/{{ $summary['totalAbsensiMapel'] ?? 0 }}</p>
            <p class="mt-1 text-xs text-slate-500">Hadir dari total catatan</p>
        </div>
    </div>

    <div class="mt-6">
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <div class="flex gap-1 border-b border-gray-200 bg-gray-50 px-2">
                <a href="{{ route('siswa.profil.index', ['tab' => 'nilai', 'bulan' => $bulan, 'tahun' => $tahun]) }}"
                    class="rounded-t-lg px-4 py-3 text-sm font-medium {{ $tab === 'nilai' ? 'border-b-2 border-green-600 bg-white text-green-900' : 'text-gray-600 hover:bg-white/50' }}">
                    Nilai
                </a>
                <a href="{{ route('siswa.profil.index', ['tab' => 'absensi', 'bulan' => $bulan, 'tahun' => $tahun]) }}"
                    class="rounded-t-lg px-4 py-3 text-sm font-medium {{ $tab === 'absensi' ? 'border-b-2 border-green-600 bg-white text-green-900' : 'text-gray-600 hover:bg-white/50' }}">
                    Absensi
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
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
