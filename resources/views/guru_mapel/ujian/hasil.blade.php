@extends('layouts.app')
@section('title', 'Hasil Ujian: ' . $ujianHarian->judul)

@section('content')
<div class="mx-auto max-w-7xl">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Hasil Ujian</p>
            <h1 class="mt-1.5 text-2xl font-bold text-gray-900">{{ $ujianHarian->judul }}</h1>
            <p class="mt-1 text-sm text-gray-500">
                {{ $ujianHarian->guruMapelKelas->mapel->nama ?? '-' }} — {{ $ujianHarian->guruMapelKelas->kelas->nama_kelas ?? '-' }}
            </p>
        </div>
        <a href="{{ route('guru_mapel.ujian.show', $ujianHarian) }}"
            class="inline-flex items-center gap-1.5 justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
            <i class="ti ti-arrow-left text-base"></i>
            Kembali
        </a>
    </div>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,0.5fr)] lg:items-start">
        <section>
            @if ($hasil->isEmpty())
                <div class="overflow-hidden rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100">
                        <i class="ti ti-file-text text-2xl text-gray-400"></i>
                    </div>
                    <h2 class="text-base font-semibold text-gray-900">Belum ada hasil</h2>
                    <p class="mt-1.5 text-sm text-gray-500">Siswa belum mengerjakan ujian ini.</p>
                </div>
            @else
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                    <table class="w-full text-sm">
                        <thead class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-5 py-3.5 text-left">Nama Siswa</th>
                                <th class="px-5 py-3.5 text-left">NIS</th>
                                <th class="px-5 py-3.5 text-center">Jawaban Benar</th>
                                <th class="px-5 py-3.5 text-center">Jawaban Salah</th>
                                <th class="px-5 py-3.5 text-center">Nilai</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($hasil as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-4">
                                        <p class="font-medium text-gray-900">{{ $item->siswa->nama ?? '-' }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="text-gray-600">{{ $item->siswa->nis ?? '-' }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                            {{ $item->jumlah_benar }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="inline-flex rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-700">
                                            {{ $item->jumlah_salah }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-700">
                                            {{ $item->nilai }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <aside class="space-y-4 lg:sticky lg:top-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 mb-3">Statistik</h2>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Total Siswa</span>
                        <span class="font-semibold text-slate-900">{{ $hasil->count() }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Rata-rata Nilai</span>
                        <span class="font-semibold text-slate-900">
                            {{ $hasil->avg('nilai') ? number_format($hasil->avg('nilai'), 1) : '-' }}
                        </span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Nilai Tertinggi</span>
                        <span class="font-semibold text-emerald-700">
                            {{ $hasil->max('nilai') ?? '-' }}
                        </span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Nilai Terendah</span>
                        <span class="font-semibold text-rose-700">
                            {{ $hasil->min('nilai') ?? '-' }}
                        </span>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection