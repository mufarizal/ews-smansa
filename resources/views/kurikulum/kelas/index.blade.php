@extends('layouts.app')
@section('title', 'Manajemen Kelas')

@section('content')

    @if (session('success'))
        <div
            class="mb-6 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            <svg class="h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manajemen Kelas</h1>
            <p class="mt-2 text-gray-600">Kelola daftar kelas</p>
        </div>
        <a href="{{ route('kurikulum.kelas.create') }}"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-green-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-green-800">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Kelas
        </a>
    </div>

    <div class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-lg border border-gray-200 bg-white p-3.5">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total Kelas</p>
            <p class="mt-1 text-xl font-bold text-gray-900">{{ $kelas->count() }}</p>
        </div>


    </div>

    <form method="GET" action="{{ route('kurikulum.kelas.index') }}" class="mb-6 grid gap-3 lg:grid-cols-3">
        <div class="relative lg:col-span-2">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none"
                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari kelas, wali kelas..."
                class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-800 placeholder-gray-400 outline-none transition focus:border-green-600 focus:ring-2 focus:ring-green-100">
        </div>

        <div class="flex gap-2 lg:col-span-4">
            <button type="submit"
                class="inline-flex items-center justify-center rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-800">
                Terapkan Filter
            </button>
            <a href="{{ route('kurikulum.kelas.index') }}"
                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                Reset
            </a>
        </div>
    </form>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">#</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">Nama Kelas</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">Data Guru (Wali Kelas)</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">Jumlah Siswa</th>
                        <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-700">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="kelas-tbody">
                    @forelse ($kelas as $i => $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3.5 text-xs font-mono text-gray-400">{{ $i + 1 }}</td>
                            <td class="px-5 py-3.5 font-medium text-gray-900">{{ $item->nama_kelas }}</td>

                            <td class="px-5 py-3.5 text-gray-700">
                                @if ($item->waliKelas)
                                    <div class="font-medium text-gray-900">{{ $item->waliKelas->nama }}</div>
                                    <div class="text-xs text-gray-500">{{ $item->waliKelas->nip }}</div>
                                @else
                                    <span class="text-gray-400">Belum dipilih</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-gray-700">{{ $item->siswa_count }}</td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('kurikulum.kelas.edit', $item->id) }}"
                                        class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-600 hover:bg-green-50 hover:border-green-300 hover:text-green-700 transition"
                                        title="Edit">
                                        <i class="ti ti-pencil"></i>
                                        <span class="hidden sm:inline">Edit</span>
                                    </a>
                                    <form action="{{ route('kurikulum.kelas.destroy', $item->id) }}" method="POST"
                                        class="inline"
                                        onsubmit="return confirm('Yakin hapus kelas {{ addslashes($item->nama_kelas) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 hover:border-red-300 transition"
                                            title="Hapus">
                                            <i class="ti ti-trash"></i>
                                            <span class="hidden sm:inline">Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <div class="mb-3 text-5xl">🏫</div>
                                <p class="font-medium text-gray-600">Belum ada data kelas</p>
                                <p class="mt-1 text-sm text-gray-500">Tambahkan kelas pertama untuk memulai</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
