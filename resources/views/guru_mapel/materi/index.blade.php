@extends('layouts.app')
@section('title', 'Materi: ' . $bab->nama_bab)

@section('content')
<div class="mx-auto max-w-7xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('guru_mapel.bab.show', $bab) }}"
                class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800">
                <i class="ti ti-arrow-left text-base"></i>
                Kembali ke bab
            </a>
        </div>
        <a href="{{ route('guru_mapel.bab.materi.create', $bab) }}"
            class="inline-flex items-center gap-1.5 rounded-lg bg-green-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-800">
            <i class="ti ti-plus text-sm"></i>
            Tambah Materi
        </a>
    </div>

    <div class="mb-4 rounded-xl border border-gray-200 bg-white p-4">
        <p class="text-xs text-gray-500">Bab: <span class="font-medium text-gray-900">{{ $bab->nama_bab }}</span></p>
        <p class="text-xs text-gray-500">Mata Pelajaran: <span class="font-medium text-gray-900">{{ $bab->guruMapelKelas->mapel->nama ?? '-' }}</span></p>
        <p class="text-xs text-gray-500">Kelas: <span class="font-medium text-gray-900">{{ $bab->guruMapelKelas->kelas->nama_kelas ?? '-' }}</span></p>
    </div>

    @if ($materis->isEmpty())
        <div class="overflow-hidden rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100">
                <i class="ti ti-file-text text-2xl text-gray-400"></i>
            </div>
            <h2 class="text-base font-semibold text-gray-900">Belum ada materi</h2>
            <p class="mt-1.5 text-sm text-gray-500">Tambahkan materi pertama untuk bab ini.</p>
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
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
                    @foreach ($materis as $materi)
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

            @if ($materis->hasPages())
                <div class="flex flex-col items-center justify-between gap-3 border-t border-gray-200 bg-gray-50 px-5 py-3.5 sm:flex-row">
                    <p class="text-xs text-gray-500">
                        Menampilkan <span class="font-semibold text-gray-700">{{ $materis->firstItem() }}–{{ $materis->lastItem() }}</span>
                        dari <span class="font-semibold text-gray-700">{{ $materis->total() }}</span> materi
                    </p>
                    <div class="flex items-center gap-1">
                        @if ($materis->onFirstPage())
                            <span class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-300 cursor-default">‹ Prev</span>
                        @else
                            <a href="{{ $materis->previousPageUrl() }}"
                                class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100">‹ Prev</a>
                        @endif
                        @php
                            $cp = $materis->currentPage();
                            $lp = $materis->lastPage();
                            $s = max(1, $cp - 2);
                            $e = min($lp, $cp + 2);
                        @endphp
                        @foreach ($materis->getUrlRange($s, $e) as $page => $url)
                            @if ($page === $cp)
                                <span class="rounded-md border border-green-600 bg-green-700 px-3 py-1.5 text-xs font-semibold text-white">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100">{{ $page }}</a>
                            @endif
                        @endforeach
                        @if ($materis->hasMorePages())
                            <a href="{{ $materis->nextPageUrl() }}"
                                class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100">Next ›</a>
                        @else
                            <span class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-300 cursor-default">Next ›</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection