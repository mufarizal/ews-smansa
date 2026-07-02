@extends('layouts.app')
@section('title', 'Detail Bab')

@section('content')
<div class="mx-auto max-w-7xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('guru_mapel.bab.index') }}"
                class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800">
                <i class="ti ti-arrow-left text-base"></i>
                Kembali ke daftar bab
            </a>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('guru_mapel.bab.edit', $bab) }}"
                class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100">
                <i class="ti ti-edit text-sm"></i>
                Edit Bab
            </a>
            <a href="{{ route('guru_mapel.bab.materi.create', $bab) }}"
                class="inline-flex items-center gap-1.5 rounded-lg bg-green-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-800">
                <i class="ti ti-plus text-sm"></i>
                Tambah Materi
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- Bab Info --}}
    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $bab->nama_bab }}</h1>
                @if ($bab->deskripsi)
                    <p class="mt-2 text-sm text-gray-600">{{ $bab->deskripsi }}</p>
                @endif
            </div>
            <span
                class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600">
                Urutan {{ $bab->urutan }}
            </span>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            <span class="rounded-full bg-blue-50 border border-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-700">
                {{ $bab->guruMapelKelas->mapel->nama ?? '-' }}
            </span>
            <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">
                {{ $bab->guruMapelKelas->kelas->nama_kelas ?? '-' }}
            </span>
        </div>
    </div>

    {{-- Materi List --}}
    <div class="rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-200 bg-gray-50 px-5 py-3.5">
            <h3 class="text-sm font-semibold text-gray-900">Daftar Materi</h3>
            <p class="text-xs text-gray-500 mt-0.5">{{ $bab->materi->count() }} materi dalam bab ini</p>
        </div>

        @if ($bab->materi->isEmpty())
            <div class="p-12 text-center">
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
                    <i class="ti ti-file-text text-xl text-gray-400"></i>
                </div>
                <p class="font-medium text-gray-600">Belum ada materi</p>
                <p class="mt-1 text-sm text-gray-400">Tambahkan materi pertama untuk bab ini.</p>
                <a href="{{ route('guru_mapel.bab.materi.create', $bab) }}"
                    class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-800">
                    <i class="ti ti-plus text-base"></i>
                    Tambah Materi
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-5 py-3.5 text-left">Judul</th>
                            <th class="px-5 py-3.5 text-left">File</th>
                            <th class="px-5 py-3.5 text-center">Urutan</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($bab->materi as $materi)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4">
                                    <p class="font-medium text-gray-900">{{ $materi->judul }}</p>
                                    @if ($materi->isi_materi)
                                        <p class="mt-0.5 text-xs text-gray-500 truncate max-w-md">
                                            {{ Str::limit(strip_tags($materi->isi_materi), 80) }}
                                        </p>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if ($materi->file_materi)
                                        <a href="{{ $materi->file_materi_url }}" target="_blank"
                                            class="inline-flex items-center gap-1.5 text-xs text-blue-600 hover:text-blue-800">
                                            <i class="ti ti-file text-sm"></i>
                                            Lihat File
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="text-sm text-gray-600">{{ $materi->urutan }}</span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('guru_mapel.bab.materi.edit', [$bab, $materi]) }}"
                                            class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-50">
                                            <i class="ti ti-edit text-sm"></i>
                                            Edit
                                        </a>
                                        <form method="POST" 
                                            action="{{ route('guru_mapel.bab.materi.destroy', [$bab, $materi]) }}"
                                            onsubmit="return confirm('Hapus materi ini?')" class="inline">
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
</div>
@endsection