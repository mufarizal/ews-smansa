@extends('layouts.app')
@section('title', 'Daftar Tugas')

@section('content')
<div class="mx-auto max-w-7xl">
    <div class="mb-6">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Tugas</p>
        <h1 class="mt-1.5 text-2xl font-bold text-gray-900">Daftar Tugas</h1>
        <p class="mt-1 text-sm text-gray-500">Tugas yang diberikan untuk mata pelajaran Anda.</p>
    </div>

    @if ($tugas->isEmpty())
        <div class="overflow-hidden rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100">
                <i class="ti ti-pencil text-2xl text-gray-400"></i>
            </div>
            <h2 class="text-base font-semibold text-gray-900">Belum ada tugas</h2>
            <p class="mt-1.5 text-sm text-gray-500">Belum ada tugas yang diberikan untuk mata pelajaran Anda.</p>
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
            <table class="w-full text-sm hidden sm:table">
                <thead class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-5 py-3.5 text-left">Judul</th>
                        <th class="px-5 py-3.5 text-left">Mapel</th>
                        <th class="px-5 py-3.5 text-center">Jenis</th>
                        <th class="px-5 py-3.5 text-left">Tanggal</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($tugas as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $item->judul }}</p>
                                    @if ($item->deskripsi)
                                        <p class="mt-0.5 text-xs text-gray-500 truncate max-w-xs">{{ Str::limit($item->deskripsi, 60) }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-gray-600">{{ $item->guruMapelKelas->mapel->nama ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ ($item->jenis ?? 'offline') === 'online' ? 'bg-blue-100 text-blue-700' : 'bg-stone-100 text-stone-700' }}">
                                    {{ ucfirst($item->jenis ?? 'Offline') }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-xs">
                                    <p class="text-gray-600">{{ \Carbon\Carbon::parse($item->tanggal_tugas)->format('d/m/Y') }}</p>
                                    @if ($item->tanggal_deadline)
                                        <p class="text-gray-500">Deadline: {{ \Carbon\Carbon::parse($item->tanggal_deadline)->format('d/m/Y') }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4 text-right">
                                @php
                                    $sudahSelesai = $item->nilaiTugas->first() && $item->nilaiTugas->first()->status === 'selesai';
                                @endphp
                                @if (($item->jenis ?? 'offline') === 'online' && $item->soalTugas->count() > 0 && !$sudahSelesai)
                                    <a href="{{ route('siswa.tugas.kerjakan', $item) }}"
                                        class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-green-800">
                                        <i class="ti ti-pencil text-base"></i>
                                        Kerjakan
                                    </a>
                                @elseif (($item->jenis ?? 'offline') === 'online' && $sudahSelesai)
                                    <a href="{{ route('siswa.tugas.show', $item) }}"
                                        class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
                                        <i class="ti ti-eye text-base"></i>
                                        Lihat Nilai
                                    </a>
                                @else
                                    <a href="{{ route('siswa.tugas.show', $item) }}"
                                        class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
                                        <i class="ti ti-eye text-base"></i>
                                        Lihat
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="sm:hidden">
                @foreach ($tugas as $item)
                    <article class="border-b border-gray-100 p-4 last:border-0">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">{{ $item->judul }}</p>
                                <p class="mt-0.5 text-xs text-gray-500">{{ $item->guruMapelKelas->mapel->nama ?? '-' }}</p>
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ ($item->jenis ?? 'offline') === 'online' ? 'bg-blue-100 text-blue-700' : 'bg-stone-100 text-stone-700' }}">
                                        {{ ucfirst($item->jenis ?? 'Offline') }}
                                    </span>
                                </div>
                            </div>
                            <div class="shrink-0">
                                @php
                                    $nilaiSaya = $item->nilaiTugas->first();
                                    $sudahSelesai = $nilaiSaya && $nilaiSaya->status === 'selesai';
                                @endphp
                                @if (($item->jenis ?? 'offline') === 'online' && $item->soalTugas->count() > 0 && !$sudahSelesai)
                                    <a href="{{ route('siswa.tugas.kerjakan', $item) }}"
                                        class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-green-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-800">
                                        <i class="ti ti-pencil text-sm"></i>
                                        Kerjakan
                                    </a>
                                @elseif (($item->jenis ?? 'offline') === 'online' && $sudahSelesai)
                                    <a href="{{ route('siswa.tugas.show', $item) }}"
                                        class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                        <i class="ti ti-eye text-sm"></i>
                                        Lihat Nilai
                                    </a>
                                @else
                                    <a href="{{ route('siswa.tugas.show', $item) }}"
                                        class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                        <i class="ti ti-eye text-sm"></i>
                                        Lihat
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection