@extends('layouts.app')
@section('title', 'Daftar Tugas')

@section('content')
<div class="mx-auto max-w-7xl">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Manajemen Tugas</p>
            <h1 class="mt-1.5 text-2xl font-bold text-gray-900">Tugas Siswa</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola tugas dan nilai untuk mata pelajaran Anda.</p>
        </div>
        <a href="{{ route('guru_mapel.tugas.create') }}"
            class="inline-flex items-center gap-2 rounded-lg bg-green-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-800">
            <i class="ti ti-plus text-base"></i>
            Tambah Tugas
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($tugas->isEmpty())
        <div class="overflow-hidden rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100">
                <i class="ti ti-pencil text-2xl text-gray-400"></i>
            </div>
            <h2 class="text-base font-semibold text-gray-900">Belum ada tugas</h2>
            <p class="mt-1.5 text-sm text-gray-500">Mulai dengan menambahkan tugas pertama untuk mata pelajaran Anda.</p>
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-5 py-3.5 text-left">Judul</th>
                        <th class="px-5 py-3.5 text-left">Mata Pelajaran</th>
                        <th class="px-5 py-3.5 text-left">Kelas</th>
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
                                <span class="text-gray-700">{{ $item->guruMapelKelas->mapel->nama ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-gray-700">{{ $item->guruMapelKelas->kelas->nama_kelas ?? '-' }}</span>
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
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('guru_mapel.tugas.show', $item) }}"
                                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium text-green-700 hover:bg-green-50">
                                        <i class="ti ti-eye text-sm"></i>
                                        Lihat
                                    </a>
                                    <a href="{{ route('guru_mapel.tugas.edit', $item) }}"
                                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-50">
                                        <i class="ti ti-edit text-sm"></i>
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('guru_mapel.tugas.destroy', $item) }}"
                                        onsubmit="return confirm('Hapus tugas ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50">
                                            <i class="ti ti-trash text-sm"></i>
                                            Hapus
                                        </button>
                                    </form>
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