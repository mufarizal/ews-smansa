@extends('layouts.app')
@section('title', 'Daftar Ujian')

@section('content')
<div class="mx-auto max-w-7xl">
    <div class="mb-6">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Ujian Harian</p>
        <h1 class="mt-1.5 text-2xl font-bold text-gray-900">Daftar Ujian</h1>
        <p class="mt-1 text-sm text-gray-500">Ujian yang tersedia untuk mata pelajaran Anda.</p>
    </div>

    @if ($ujians->isEmpty())
        <div class="overflow-hidden rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100">
                <i class="ti ti-file-text text-2xl text-gray-400"></i>
            </div>
            <h2 class="text-base font-semibold text-gray-900">Belum ada ujian</h2>
            <p class="mt-1.5 text-sm text-gray-500">Belum ada ujian yang tersedia untuk mata pelajaran Anda.</p>
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
            <table class="w-full text-sm hidden sm:table">
                <thead class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-5 py-3.5 text-left">Judul</th>
                        <th class="px-5 py-3.5 text-left">Mapel</th>
                        <th class="px-5 py-3.5 text-left">Tanggal</th>
                        <th class="px-5 py-3.5 text-center">Durasi</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($ujians as $ujian)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $ujian->judul }}</p>
                                    @if ($ujian->bab)
                                        <p class="mt-0.5 text-xs text-gray-500">Bab {{ $ujian->bab->urutan }}: {{ $ujian->bab->nama_bab }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-gray-600">{{ $ujian->guruMapelKelas->mapel->nama ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-gray-600">{{ \Carbon\Carbon::parse($ujian->tanggal_ujian)->format('d/m/Y') }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="text-gray-600">{{ $ujian->durasi_menit }} menit</span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('siswa.ujian.show', $ujian) }}"
                                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-green-800">
                                    <i class="ti ti-play text-base"></i>
                                    Kerjakan
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="sm:hidden">
                @foreach ($ujians as $ujian)
                    <article class="border-b border-gray-100 p-4 last:border-0">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">{{ $ujian->judul }}</p>
                                <p class="mt-0.5 text-xs text-gray-500">{{ $ujian->guruMapelKelas->mapel->nama ?? '-' }}</p>
                                <p class="mt-1 text-xs text-gray-500">{{ \Carbon\Carbon::parse($ujian->tanggal_ujian)->format('d/m/Y') }} • {{ $ujian->durasi_menit }} menit</p>
                                @if ($ujian->bab)
                                    <p class="mt-1 text-xs text-gray-400">Bab {{ $ujian->bab->urutan }}: {{ $ujian->bab->nama_bab }}</p>
                                @endif
                            </div>
                            <div class="shrink-0">
                                <a href="{{ route('siswa.ujian.show', $ujian) }}"
                                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-green-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-800">
                                    <i class="ti ti-play text-sm"></i>
                                    Kerjakan
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection