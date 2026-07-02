@extends('layouts.app')
@section('title', 'Manajemen Siswa')

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

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-semibold">Terjadi kesalahan:</p>
            <ul class="mt-1 list-disc pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $skippedWarnings = session('siswa_last_import_skipped', []);
    @endphp

    @if (!empty($skippedWarnings))
        <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <p class="font-semibold">Sebagian baris import dilewati:</p>
            <ul class="mt-1 list-disc pl-4">
                @foreach (array_slice($skippedWarnings, 0, 8) as $warning)
                    <li>{{ $warning }}</li>
                @endforeach
            </ul>
            @if (count($skippedWarnings) > 8)
                <p class="mt-2">Dan {{ count($skippedWarnings) - 8 }} baris lainnya.</p>
            @endif
        </div>
        @php session()->forget('siswa_last_import_skipped'); @endphp
    @endif

    {{-- Header --}}
    <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manajemen Siswa</h1>
            <p class="mt-2 text-gray-600">Kelola data siswa, akun login, serta import data massal</p>
        </div>
        <a href="{{ route('kurikulum.siswa.create') }}"
            class="inline-flex items-center gap-2 rounded-lg bg-green-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-green-800">
            Tambah Siswa
        </a>
    </div>

    {{-- Stat Cards --}}
    <div class="mb-6 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white p-3.5">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total Siswa</p>
            <p class="mt-1 text-xl font-bold text-gray-900">{{ $totalSiswa }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-3.5">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Kelas Terisi</p>
            <p class="mt-1 text-xl font-bold text-gray-900">{{ $kelasCount }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-3.5">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Akun Siap Login</p>
            <p class="mt-1 text-xl font-bold text-gray-900">{{ $akunSiap }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-3.5">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Email Domain</p>
            <p class="mt-1 text-xl font-bold text-gray-900">@siswa.com</p>
        </div>
    </div>

    {{-- Import & Generate --}}
    <div class="mb-6 grid gap-4 xl:grid-cols-2">
        <div class="rounded-lg border border-gray-200 bg-white p-5">
            <h2 class="text-base font-semibold text-gray-900">Import Siswa via Excel</h2>
            <p class="mt-1 text-sm text-gray-600">Gunakan template resmi agar format kolom valid. Maksimal file 5MB.</p>

            <div class="mt-3 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2.5 text-xs text-blue-900">
                <p class="font-semibold">Sebelum import:</p>
                <ul class="mt-1 list-disc pl-4">
                    <li>Isi data hanya pada sheet <span class="font-semibold">Template Import</span>.</li>
                    <li>Kolom wajib: <span class="font-semibold">kelas_id, nis, nama</span>.</li>
                    <li>Daftar kelas tersedia ada di sheet <span class="font-semibold">Panduan &amp; Referensi</span>.</li>
                    <li>Jangan ubah nama header kolom.</li>
                </ul>
            </div>

            <form action="{{ route('kurikulum.siswa.template') }}" method="GET"
                class="mt-4 rounded-lg border border-gray-200 bg-gray-50 p-3">
                <label class="mb-1.5 block text-xs font-semibold text-gray-700">Download template berdasarkan kelas
                    (opsional)</label>
                <div class="space-y-2">
                    <select name="kelas_id"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700">
                        <option value="">Semua kelas (daftar referensi lengkap)</option>
                        @foreach ($kelas as $kelasItem)
                            <option value="{{ $kelasItem->id }}">{{ $kelasItem->nama_kelas }}</option>
                        @endforeach
                    </select>
                    <button type="submit"
                        class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">
                        Download Template
                    </button>
                </div>
            </form>

            <form action="{{ route('kurikulum.siswa.import') }}" method="POST" enctype="multipart/form-data"
                class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                @csrf
                <input type="file" name="excel_file" accept=".xlsx,.xls,.csv"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 file:mr-3 file:rounded-md file:border-0 file:bg-green-50 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-green-700 hover:file:bg-green-100"
                    required>
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-green-700 px-5 py-2 text-sm font-semibold text-white transition hover:bg-green-800">
                    Import Sekarang
                </button>
            </form>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-5">
            <h2 class="text-base font-semibold text-gray-900">Generate Akun Login Massal</h2>
            <p class="mt-1 text-sm text-gray-600">Pilih kelas agar reset password dan file Excel hanya untuk siswa di kelas
                tersebut.</p>

            <div class="mt-4 space-y-3">
                <form action="{{ route('kurikulum.siswa.export-credentials') }}" method="POST"
                    class="rounded-lg border border-amber-200 bg-amber-50/50 p-3"
                    onsubmit="return confirm('Password login siswa pada kelas yang dipilih akan direset. Lanjutkan?')">
                    @csrf
                    <label class="mb-1.5 block text-xs font-semibold text-amber-900">Reset password + download akun per
                        kelas</label>
                    <div class="space-y-2">
                        <select name="kelas_id" required
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700">
                            <option value="">Pilih kelas yang akan direset</option>
                            @foreach ($kelas as $kelasItem)
                                <option value="{{ $kelasItem->id }}">{{ $kelasItem->nama_kelas }}</option>
                            @endforeach
                        </select>
                        <button type="submit"
                            class="inline-flex w-full items-center justify-center rounded-lg border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-800 transition hover:bg-amber-100">
                            Reset + Download
                        </button>
                    </div>
                </form>

                @if (session()->has('siswa_generated_credentials'))
                    <form action="{{ route('kurikulum.siswa.download-last-credentials') }}" method="GET"
                        class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                        <label class="mb-1.5 block text-xs font-semibold text-gray-700">Download akun hasil import terakhir
                            per kelas</label>
                        <div class="space-y-2">
                            <select name="kelas_id" required
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700">
                                <option value="">Pilih kelas hasil import terakhir</option>
                                @foreach ($kelas as $kelasItem)
                                    <option value="{{ $kelasItem->id }}">{{ $kelasItem->nama_kelas }}</option>
                                @endforeach
                            </select>
                            <button type="submit"
                                class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                                Download Akun Import
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('kurikulum.siswa.index') }}" class="mb-6 grid gap-3 md:grid-cols-3">
        <div class="relative md:col-span-2">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none"
                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input type="text" name="search" value="{{ $search }}"
                placeholder="Cari NIS, nama, kelas, alamat, atau email login..."
                class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-800 placeholder-gray-400 outline-none transition focus:border-green-600 focus:ring-2 focus:ring-green-100">
        </div>
        <div>
            <select name="kelas_id"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700">
                <option value="">Semua Kelas</option>
                @foreach ($kelas as $kelasItem)
                    <option value="{{ $kelasItem->id }}"
                        {{ (int) $selectedKelasId === (int) $kelasItem->id ? 'selected' : '' }}>
                        {{ $kelasItem->nama_kelas }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2 md:col-span-3">
            <button type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-green-700 px-5 py-2 text-sm font-semibold text-white transition hover:bg-green-800">
                    Terapkan Filter
            </button>
            <a href="{{ route('kurikulum.siswa.index') }}"
                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                Reset
            </a>
        </div>
    </form>

    {{-- Tabel --}}
    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">#</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">NIS</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">Nama</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">Kelas</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700 hidden lg:table-cell">Email
                            Login</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-700">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($siswa as $i => $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3.5 text-xs font-mono text-gray-400">
                                {{ $siswa->firstItem() + $i }}
                            </td>
                            <td class="px-5 py-3.5 font-mono text-gray-700">{{ $item->nis }}</td>
                            <td class="px-5 py-3.5 font-medium text-gray-900">{{ $item->nama }}</td>
                            <td class="px-5 py-3.5 text-gray-700">
                                {{ optional($item->kelas)->nama_kelas ?? '-' }}
                            </td>
                            <td class="px-5 py-3.5 text-gray-600 hidden lg:table-cell">
                                {{ optional($item->user)->email ?? '-' }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('kurikulum.siswa.edit', $item->id) }}?page={{ $siswa->currentPage() }}"
                                        class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-600 hover:bg-green-50 hover:border-green-300 hover:text-green-700 transition"
                                        title="Edit">
                                        <i class="ti ti-pencil"></i>
                                        <span class="hidden sm:inline">Edit</span>
                                    </a>
                                    <form action="{{ route('kurikulum.siswa.destroy', $item->id) }}" method="POST"
                                        class="inline"
                                        onsubmit="return confirm('Yakin hapus siswa {{ addslashes($item->nama) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="page" value="{{ $siswa->currentPage() }}">
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
                            <td colspan="6" class="px-5 py-12 text-center">
                                <div class="mb-3 text-5xl">🎓</div>
                                <p class="font-medium text-gray-600">Belum ada data siswa</p>
                                <p class="mt-1 text-sm text-gray-500">Silakan tambah siswa manual atau import dari Excel
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($siswa->hasPages())
            <div
                class="flex flex-col items-center justify-between gap-3 border-t border-gray-200 bg-gray-50 px-5 py-3.5 sm:flex-row">
                <p class="text-xs text-gray-500">
                    Menampilkan
                    <span class="font-semibold text-gray-700">{{ $siswa->firstItem() }}–{{ $siswa->lastItem() }}</span>
                    dari
                    <span class="font-semibold text-gray-700">{{ $siswa->total() }}</span>
                    siswa
                </p>

                <div class="flex items-center gap-1">
                    {{-- Prev --}}
                    @if ($siswa->onFirstPage())
                        <span
                            class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-300 cursor-default select-none">
                            ‹ Prev
                        </span>
                    @else
                        <a href="{{ $siswa->previousPageUrl() }}"
                            class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 transition">
                            ‹ Prev
                        </a>
                    @endif

                    {{-- Page Numbers --}}
                    @php
                        $currentPage = $siswa->currentPage();
                        $lastPage = $siswa->lastPage();
                        $start = max(1, $currentPage - 2);
                        $end = min($lastPage, $currentPage + 2);
                    @endphp

                    @if ($start > 1)
                        <a href="{{ $siswa->url(1) }}"
                            class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 transition">
                            1
                        </a>
                        @if ($start > 2)
                            <span class="px-1 text-xs text-gray-400">…</span>
                        @endif
                    @endif

                    @foreach ($siswa->getUrlRange($start, $end) as $page => $url)
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
                        <a href="{{ $siswa->url($lastPage) }}"
                            class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 transition">
                            {{ $lastPage }}
                        </a>
                    @endif

                    {{-- Next --}}
                    @if ($siswa->hasMorePages())
                        <a href="{{ $siswa->nextPageUrl() }}"
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
            @if ($siswa->total() > 0)
                <div class="border-t border-gray-200 bg-gray-50 px-5 py-3">
                    <p class="text-xs text-gray-500">
                        Menampilkan semua
                        <span class="font-semibold text-gray-700">{{ $siswa->total() }}</span>
                        siswa
                    </p>
                </div>
            @endif
        @endif
    </div>

@endsection
