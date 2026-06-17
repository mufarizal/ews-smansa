@extends('layouts.app')
@section('title', 'Manajemen Guru')

@section('content')

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session('import_failures') && count(session('import_failures')) > 0)
        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <p class="font-semibold mb-2">Detail baris yang gagal diimpor:</p>
            <ul class="list-inside list-disc space-y-1 text-xs">
                @foreach (session('import_failures') as $failure)
                    <li>Baris {{ $failure['row'] }}: {{ $failure['error'] }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manajemen Data Guru</h1>
            <p class="mt-1 text-sm text-gray-500">
                Penugasan & role diatur di menu
                <a href="{{ route('kurikulum.penugasan-guru.mapel.index') }}"
                    class="text-green-700 underline underline-offset-2">Penugasan Guru</a>
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('kurikulum.guru.download-template') }}"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                <i class="ti ti-download text-base"></i> Template
            </a>
            <a href="{{ route('kurikulum.guru.create') }}"
                class="inline-flex items-center gap-1.5 rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-800">
                <i class="ti ti-plus text-base"></i> Tambah Guru
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="mb-5 flex gap-3 overflow-x-auto pb-1">
        @php
            $statItems = [
                ['label' => 'Total Guru', 'value' => $stats['total'], 'color' => 'text-gray-900'],
                ['label' => 'Mengajar Mapel', 'value' => $stats['mapel'], 'color' => 'text-green-800'],
                ['label' => 'Menjadi Wali Kelas', 'value' => $stats['wali'], 'color' => 'text-purple-800'],
                ['label' => 'Belum Ditugaskan', 'value' => $stats['none'], 'color' => 'text-gray-400'],
            ];
        @endphp
        @foreach ($statItems as $s)
            <div class="rounded-lg bg-gray-50 px-4 py-3">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ $s['label'] }}</p>
                <p class="mt-1 text-2xl font-bold {{ $s['color'] }}">{{ $s['value'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Filter --}}
    <div class="mb-4 rounded-lg border border-gray-200 bg-white p-4">
        <form method="GET" action="{{ route('kurikulum.guru.index') }}" id="filter-form">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">

                {{-- Search --}}
                <div class="relative flex-1">
                    <i class="ti ti-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" id="search" value="{{ $search }}"
                        placeholder=" Cari nama atau NIP..."
                        class="w-full rounded-lg border border-gray-300 py-2 pl-10 pr-4 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                </div>

                {{-- Filter Chips --}}
                <div class="flex flex-wrap gap-2">
                    @php
                        $chips = [
                            'all' => 'Semua',
                            'mapel' => 'Mapel',
                            'piket' => 'Piket',
                            'bk' => 'BK',
                            'wali' => 'Wali Kelas',
                            'none' => 'Belum Ditugaskan',
                        ];
                        $chipColor = [
                            'all' => 'bg-gray-900 text-white border-gray-900',
                            'mapel' => 'bg-green-700 text-white border-green-700',
                            'piket' => 'bg-amber-600 text-white border-amber-600',
                            'bk' => 'bg-blue-700 text-white border-blue-700',
                            'wali' => 'bg-purple-700 text-white border-purple-700',
                            'none' => 'bg-gray-500 text-white border-gray-500',
                        ];
                        $chipDefault = 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50';
                    @endphp

                    @foreach ($chips as $key => $label)
                        <button type="button" onclick="setFilter('{{ $key }}')" id="chip-{{ $key }}"
                            class="rounded-full border px-3 py-1 text-xs font-medium transition
                                   {{ $filter === $key ? $chipColor[$key] : $chipDefault }}">
                            {{ $label }}
                        </button>
                    @endforeach

                    <input type="hidden" name="filter" id="filter-input" value="{{ $filter }}">
                </div>

            </div>
        </form>
    </div>

    {{-- Import row --}}
    <form action="{{ route('kurikulum.guru.import') }}" method="POST" enctype="multipart/form-data"
        class="mb-5 flex items-center gap-3 rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm">
        @csrf
        <i class="ti ti-file-spreadsheet text-xl text-gray-400"></i>
        <span class="text-gray-500">Import dari Excel:</span>
        <input type="file" name="file" accept=".xlsx,.xls"
            class="flex-1 text-sm text-gray-600 file:mr-3 file:rounded file:border-0 file:bg-green-50 file:px-3 file:py-1 file:text-xs file:font-medium file:text-green-700">
        <button type="submit"
            class="rounded-lg bg-green-700 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-800">
            Upload
        </button>
    </form>

    {{-- Tabel --}}
    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-4 py-3 text-left">Guru</th>
                    <th class="px-4 py-3 text-left">Penugasan Aktif</th>
                    <th class="px-4 py-3 text-left">Wali Kelas</th>
                    <th class="px-4 py-3 text-left">Email Akun</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($gurus as $guru)
                    @php
                        $hasMapel = $guru->guruMapelKelas->isNotEmpty();
                        $hasPiket = $guru->guruPikets->isNotEmpty();
                        $hasBk = $guru->guruBkKelas->isNotEmpty();
                        $hasWali = $guru->kelasDiampu->isNotEmpty();
                        $noTask = !$hasMapel && !$hasPiket && !$hasBk && !$hasWali;
                    @endphp
                    <tr class="hover:bg-gray-50 {{ $noTask ? 'opacity-60' : '' }}">

                        {{-- Nama / NIP --}}
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">{{ $guru->nama }}</div>
                            <div class="mt-0.5 text-xs text-gray-400">
                                {{ strstr($guru->nip, '@', true) ?: $guru->nip }}
                            </div>
                        </td>

                        {{-- Penugasan --}}
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1.5">
                                @if ($hasMapel)
                                    @foreach ($guru->guruMapelKelas->unique('mapel_id') as $gmk)
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">
                                            <i class="ti ti-book-2 text-xs"></i>
                                            {{ $gmk->mapel?->nama ?? '–' }}
                                        </span>
                                    @endforeach
                                @endif

                                @if ($hasPiket)
                                    @foreach ($guru->guruPikets->unique('hari') as $gp)
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">
                                            <i class="ti ti-calendar-event text-xs"></i>
                                            {{ $gp->hari }}
                                        </span>
                                    @endforeach
                                @endif

                                @if ($hasBk)
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800">
                                        <i class="ti ti-heart-handshake text-xs"></i>
                                        Bimbingan Konseling
                                    </span>
                                @endif

                                @if ($noTask)
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-400">
                                        <i class="ti ti-clock text-xs"></i>
                                        Belum ditugaskan —
                                        <a href="{{ route('kurikulum.penugasan-guru.mapel.index') }}"
                                            class="text-green-700 underline underline-offset-1">Atur sekarang</a>
                                    </span>
                                @endif
                            </div>
                        </td>

                        {{-- Wali Kelas --}}
                        <td class="px-4 py-3">
                            @if ($hasWali)
                                <span
                                    class="inline-flex items-center gap-1 rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-800">
                                    <i class="ti ti-home text-xs"></i>
                                    {{ $guru->kelasDiampu->first()->nama_kelas }}
                                </span>
                            @else
                                <span class="text-xs text-gray-300">—</span>
                            @endif
                        </td>

                        {{-- Email --}}
                        <td class="px-4 py-3 text-xs text-gray-500">
                            {{ $guru->user?->email ?? '–' }}
                        </td>

                        {{-- Aksi --}}
                        <td class="px-4 py-3 text-center">
                            <div class="inline-flex items-center gap-1">
                                <a href="{{ route('kurikulum.guru.edit', $guru->id) }}?page={{ $gurus->currentPage() }}"
                                    class="inline-flex items-center gap-1 rounded border border-gray-200 px-2.5 py-1.5 text-xs text-gray-600 hover:border-green-300 hover:bg-green-50 hover:text-green-700">
                                    <i class="ti ti-pencil"></i>
                                    <span class="hidden sm:inline">Edit</span>
                                </a>
                                <form action="{{ route('kurikulum.guru.destroy', $guru->id) }}" method="POST"
                                    onsubmit="return confirm('Hapus guru {{ addslashes($guru->nama) }}? Semua penugasan akan ikut terhapus.')">
                                    @csrf @method('DELETE')
                                    <input type="hidden" name="page" value="{{ $gurus->currentPage() }}">
                                    <button type="submit"
                                        class="inline-flex items-center gap-1 rounded border border-gray-200 px-2.5 py-1.5 text-xs text-red-500 hover:border-red-300 hover:bg-red-50">
                                        <i class="ti ti-trash"></i>
                                        <span class="hidden sm:inline">Hapus</span>
                                    </button>
                                </form>
                            </div>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-400">
                            <i class="ti ti-mood-empty text-3xl block mb-2"></i>
                            Tidak ada guru yang cocok dengan filter
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if ($gurus->hasPages())
        <div
            class="flex flex-col items-center justify-between gap-3 border-t border-gray-200 bg-gray-50 px-5 py-3.5 sm:flex-row">
            <p class="text-xs text-gray-500">
                Menampilkan
                <span class="font-semibold text-gray-700">{{ $gurus->firstItem() }}–{{ $gurus->lastItem() }}</span>
                dari
                <span class="font-semibold text-gray-700">{{ $gurus->total() }}</span>
                siswa
            </p>

            <div class="flex items-center gap-1">
                {{-- Prev --}}
                @if ($gurus->onFirstPage())
                    <span
                        class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-300 cursor-default select-none">
                        ‹ Prev
                    </span>
                @else
                    <a href="{{ $gurus->previousPageUrl() }}"
                        class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 transition">
                        ‹ Prev
                    </a>
                @endif

                {{-- Page Numbers --}}
                @php
                    $currentPage = $gurus->currentPage();
                    $lastPage = $gurus->lastPage();
                    $start = max(1, $currentPage - 2);
                    $end = min($lastPage, $currentPage + 2);
                @endphp

                @if ($start > 1)
                    <a href="{{ $gurus->url(1) }}"
                        class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 transition">
                        1
                    </a>
                    @if ($start > 2)
                        <span class="px-1 text-xs text-gray-400">…</span>
                    @endif
                @endif

                @foreach ($gurus->getUrlRange($start, $end) as $page => $url)
                    @if ($page === $currentPage)
                        <span
                            class="rounded-md border border-green-600 bg-green-700 px-3 py-1.5 text-xs font-semibold text-white select-none">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}"
                            class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 transition">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach

                @if ($end < $lastPage)
                    @if ($end < $lastPage - 1)
                        <span class="px-1 text-xs text-gray-400">…</span>
                    @endif
                    <a href="{{ $gurus->url($lastPage) }}"
                        class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 transition">
                        {{ $lastPage }}
                    </a>
                @endif

                {{-- Next --}}
                @if ($gurus->hasMorePages())
                    <a href="{{ $gurus->nextPageUrl() }}"
                        class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 transition">
                        Next ›
                    </a>
                @else
                    <span
                        class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-300 cursor-default select-none">
                        Next ›
                    </span>
                @endif
            </div>
        </div>
    @else
        {{-- Tetap tampilkan info total meski hanya 1 halaman --}}
        @if ($gurus->total() > 0)
            <div class="border-t border-gray-200 bg-gray-50 px-5 py-3">
                <p class="text-xs text-gray-500">
                    Menampilkan semua
                    <span class="font-semibold text-gray-700">{{ $gurus->total() }}</span>
                    siswa
                </p>
            </div>
        @endif
    @endif

    <script>
        function setFilter(key) {
            document.getElementById('filter-input').value = key;
            document.getElementById('filter-form').submit();
        }
        // Auto-submit search saat ketik berhenti
        document.getElementById('search').addEventListener('input', function() {
            clearTimeout(this._timer);
            this._timer = setTimeout(() => document.getElementById('filter-form').submit(), 500);
        });
    </script>

@endsection
