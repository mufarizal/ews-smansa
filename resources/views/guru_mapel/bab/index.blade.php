@extends('layouts.app')
@section('title', 'Bab & Materi')

@section('content')
<div class="mx-auto max-w-7xl" x-data="babIndex()">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Manajemen</p>
            <h1 class="mt-1.5 text-2xl font-bold text-gray-900">Bab & Materi</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola bab dan materi pembelajaran untuk mata pelajaran Anda.</p>
        </div>
        <a href="{{ route('guru_mapel.bab.create') }}"
            class="inline-flex items-center gap-2 rounded-lg bg-green-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-800">
            <i class="ti ti-plus text-base"></i>
            Tambah Bab
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($babs->isEmpty())
        <div class="overflow-hidden rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100">
                <i class="ti ti-book text-2xl text-gray-400"></i>
            </div>
            <h2 class="text-base font-semibold text-gray-900">Belum ada bab</h2>
            <p class="mt-1.5 text-sm text-gray-500">Mulai dengan menambahkan bab pertama untuk mata pelajaran Anda.</p>
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-5 py-3.5 text-left">Bab</th>
                        <th class="px-5 py-3.5 text-left">Mata Pelajaran</th>
                        <th class="px-5 py-3.5 text-center">Materi</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($babs as $bab)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $bab->nama_bab }}</p>
                                    @if ($bab->deskripsi)
                                        <p class="mt-0.5 text-xs text-gray-500 truncate max-w-xs">{{ Str::limit($bab->deskripsi, 60) }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-gray-700">{{ $bab->guruMapelKelas->mapel->nama ?? '-' }}</span>
                            </td>
                           
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-gray-100 text-sm font-bold text-gray-700">
                                    {{ $bab->materi_count ?? $bab->materi->count() }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('guru_mapel.bab.show', $bab) }}"
                                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium text-green-700 hover:bg-green-50">
                                        <i class="ti ti-eye text-sm"></i>
                                        Lihat
                                    </a>
                                    <a href="{{ route('guru_mapel.bab.edit', $bab) }}"
                                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-50">
                                        <i class="ti ti-edit text-sm"></i>
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('guru_mapel.bab.destroy', $bab) }}"
                                        onsubmit="return confirm('Hapus bab ini?')" class="inline">
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

            @if ($babs->hasPages())
                <div
                    class="flex flex-col items-center justify-between gap-3 border-t border-gray-200 bg-gray-50 px-5 py-3.5 sm:flex-row">
                    <p class="text-xs text-gray-500">
                        Menampilkan <span class="font-semibold text-gray-700">{{ $babs->firstItem() }}–{{ $babs->lastItem() }}</span>
                        dari <span class="font-semibold text-gray-700">{{ $babs->total() }}</span> bab
                    </p>
                    <div class="flex items-center gap-1">
                        @if ($babs->onFirstPage())
                            <span
                                class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-300 cursor-default">‹
                                Prev</span>
                        @else
                            <a href="{{ $babs->previousPageUrl() }}"
                                class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100">‹
                                Prev</a>
                        @endif
                        @php
                            $cp = $babs->currentPage();
                            $lp = $babs->lastPage();
                            $s = max(1, $cp - 2);
                            $e = min($lp, $cp + 2);
                        @endphp
                        @foreach ($babs->getUrlRange($s, $e) as $page => $url)
                            @if ($page === $cp)
                                <span
                                    class="rounded-md border border-green-600 bg-green-700 px-3 py-1.5 text-xs font-semibold text-white">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}"
                                    class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100">{{ $page }}</a>
                            @endif
                        @endforeach
                        @if ($babs->hasMorePages())
                            <a href="{{ $babs->nextPageUrl() }}"
                                class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100">Next
                                ›</a>
                        @else
                            <span
                                class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-300 cursor-default">Next
                                ›</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection