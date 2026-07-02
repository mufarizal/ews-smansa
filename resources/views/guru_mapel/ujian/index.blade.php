@extends('layouts.app')
@section('title', 'Daftar Ujian Harian')

@section('content')
<div class="mx-auto max-w-7xl">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Manajemen Ujian</p>
            <h1 class="mt-1.5 text-2xl font-bold text-gray-900">Ujian Harian</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola ujian dan soal untuk mata pelajaran Anda.</p>
        </div>
        <a href="{{ route('guru_mapel.ujian.create') }}"
            class="inline-flex items-center gap-2 rounded-lg bg-green-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-800">
            <i class="ti ti-plus text-base"></i>
            Tambah Ujian
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($ujians->isEmpty())
        <div class="overflow-hidden rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100">
                <i class="ti ti-file-text text-2xl text-gray-400"></i>
            </div>
            <h2 class="text-base font-semibold text-gray-900">Belum ada ujian</h2>
            <p class="mt-1.5 text-sm text-gray-500">Mulai dengan menambahkan ujian pertama untuk mata pelajaran Anda.</p>
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-5 py-3.5 text-left">Judul</th>
                        <th class="px-5 py-3.5 text-left">Mata Pelajaran</th>
                        <th class="px-5 py-3.5 text-left">Kelas</th>
                        <th class="px-5 py-3.5 text-center">Durasi</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
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
                                <span class="text-gray-700">{{ $ujian->guruMapelKelas->mapel->nama ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-gray-700">{{ $ujian->guruMapelKelas->kelas->nama_kelas ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="text-gray-600">{{ $ujian->durasi_menit }} menit</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if ($ujian->status === 'draft')
                                    <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">
                                        Draft
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                        Publish
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('guru_mapel.ujian.show', $ujian) }}"
                                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium text-green-700 hover:bg-green-50">
                                        <i class="ti ti-eye text-sm"></i>
                                        Lihat
                                    </a>
                                    @if ($ujian->status === 'draft')
                                        <a href="{{ route('guru_mapel.ujian.edit', $ujian) }}"
                                            class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-50">
                                            <i class="ti ti-edit text-sm"></i>
                                            Edit
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection