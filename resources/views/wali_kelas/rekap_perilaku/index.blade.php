@extends('layouts.app')

@section('title', 'Rekap Perilaku Kelas Saya')

@section('content')
<div class="mx-auto max-w-7xl">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Rekap Perkembangan</p>
            <h1 class="mt-1.5 text-2xl font-bold text-gray-900">Perkembangan Siswa Kelas Saya</h1>
            <p class="mt-1 text-sm text-gray-500">Ringkasan perilaku, absensi, dan nilai akademik siswa.</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-5 py-3.5 text-left">Siswa</th>
                    <th class="px-5 py-3.5 text-left">Kelas</th>
                    <th class="px-5 py-3.5 text-center">Nilai Akademik</th>
                    <th class="px-5 py-3.5 text-center">Absensi</th>
                    <th class="px-5 py-3.5 text-center">Perilaku</th>
                    <th class="px-5 py-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($rekap as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-4">
                            <span class="font-medium text-gray-900">{{ $item->siswa->nama }}</span>
                        </td>
                        <td class="px-5 py-4">
                            {{ $item->siswa->kelas->nama_kelas ?? '-' }}
                        </td>
                        <td class="px-5 py-4 text-center">
                            <div class="text-xs">
                                <span class="text-gray-500">Tugas:</span>
                                <span class="font-mono text-gray-900">{{ $item->rata_rata_tugas }}</span>
                                <span class="text-gray-500">/ Ujian:</span>
                                <span class="font-mono text-gray-900">{{ $item->rata_rata_ujian }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <div class="text-xs">
                                <span class="text-rose-700 font-bold">{{ $item->alpha }} alpha</span>
                                <span class="text-gray-400 mx-1">|</span>
                                <span class="text-amber-700">{{ $item->terlambat }} terlambat</span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <div class="text-xs">
                                <span class="text-emerald-700">{{ $item->jumlah_positif }} positif</span>
                                <span class="mx-1 text-gray-400">|</span>
                                <span class="text-rose-700">{{ $item->jumlah_negatif }} negatif</span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('wali_kelas.rekap-perilaku.show', $item->siswa) }}"
                                class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-medium text-green-900 hover:bg-green-50">
                                Detail
                                <i class="ti ti-arrow-right text-xs"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
