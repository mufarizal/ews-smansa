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
            <p class="mt-2 text-gray-600">Kelola daftar kelas, jurusan, dan angkatan</p>
        </div>
        <a href="{{ route('admin.kelas.create') }}"
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
        <div class="rounded-lg border border-gray-200 bg-white p-3.5">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total Jurusan</p>
            <p class="mt-1 text-xl font-bold text-gray-900">{{ $kelas->pluck('jurusan')->unique()->count() }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-3.5">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Rentang Angkatan</p>
            @if ($kelas->count())
                <p class="mt-1 text-xl font-bold text-gray-900">{{ $kelas->min('angkatan') }} -
                    {{ $kelas->max('angkatan') }}</p>
            @else
                <p class="mt-1 text-xl font-bold text-gray-900">-</p>
            @endif
        </div>
    </div>

    <div class="mb-6">
        <div class="relative">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none"
                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input id="search-input" type="text" placeholder="Cari nama kelas, jurusan, atau angkatan..."
                class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-800 placeholder-gray-400 outline-none transition focus:border-green-600 focus:ring-2 focus:ring-green-100">
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">#</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">Nama Kelas</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">Jurusan</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">Angkatan</th>
                        <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-700">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="kelas-tbody">
                    @forelse ($kelas as $i => $item)
                        <tr class="kelas-row hover:bg-gray-50" data-nama="{{ strtolower($item->nama_kelas) }}"
                            data-jurusan="{{ strtolower($item->jurusan) }}"
                            data-angkatan="{{ strtolower($item->angkatan) }}">
                            <td class="px-5 py-3.5 text-xs font-mono text-gray-400">{{ $i + 1 }}</td>
                            <td class="px-5 py-3.5 font-medium text-gray-900">{{ $item->nama_kelas }}</td>
                            <td class="px-5 py-3.5 text-gray-700">{{ $item->jurusan }}</td>
                            <td class="px-5 py-3.5 text-gray-700">{{ $item->angkatan }}</td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.kelas.edit', $item->id) }}"
                                        class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-600 transition hover:border-green-300 hover:bg-green-50 hover:text-green-700"
                                        title="Edit">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        <span class="hidden sm:inline">Edit</span>
                                    </a>
                                    <form action="{{ route('admin.kelas.destroy', $item->id) }}" method="POST"
                                        class="inline"
                                        onsubmit="return confirm('Yakin hapus kelas {{ addslashes($item->nama_kelas) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-red-600 transition hover:border-red-300 hover:bg-red-50"
                                            title="Hapus">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            <span class="hidden sm:inline">Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center">
                                <div class="mb-3 text-5xl">🏫</div>
                                <p class="font-medium text-gray-600">Belum ada data kelas</p>
                                <p class="mt-1 text-sm text-gray-500">Tambahkan kelas pertama untuk memulai</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div id="no-result" class="hidden px-5 py-12 text-center">
                <div class="mb-3 text-5xl">🔍</div>
                <p class="font-medium text-gray-600">Tidak ada kelas yang ditemukan</p>
                <p class="mt-1 text-sm text-gray-500">Coba kata kunci lain</p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('search-input').addEventListener('input', applyFilters);

        function applyFilters() {
            const query = document.getElementById('search-input').value.toLowerCase().trim();
            const rows = document.querySelectorAll('.kelas-row');
            let visible = 0;

            rows.forEach(row => {
                const match = !query ||
                    row.dataset.nama.includes(query) ||
                    row.dataset.jurusan.includes(query) ||
                    row.dataset.angkatan.includes(query);

                row.classList.toggle('hidden', !match);
                if (match) visible++;
            });

            document.getElementById('no-result').classList.toggle('hidden', visible > 0);
        }
    </script>

@endsection
