@extends('layouts.app')
@section('title', 'Kelas Saya')

@section('content')
    @if (session('success'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Kelas Saya</h1>
            <p class="mt-2 text-gray-600">Informasi kelas dan daftar siswa yang Anda ampu sebagai wali kelas</p>
        </div>
    </div>

    @if (!$kelas)
        <div class="overflow-hidden rounded-lg border border-dashed border-gray-300 bg-white p-10 text-center">
            <h2 class="text-lg font-semibold text-gray-900">Belum ada kelas yang diampu</h2>
            <p class="mt-2 text-sm text-gray-500">Data kelas akan muncul setelah Anda ditetapkan sebagai wali kelas.</p>
        </div>
    @else
        <div class="mb-6 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            <div class="rounded-lg border border-gray-200 bg-white p-3.5">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Nama Kelas</p>
                <p class="mt-1 text-xl font-bold text-gray-900">{{ $kelas->nama_kelas }}</p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-3.5">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Semester Aktif</p>
                <p class="mt-1 text-xl font-bold text-gray-900">{{ $kelas->semester?->nama ?? '-' }}</p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-3.5">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Jumlah Siswa</p>
                <p class="mt-1 text-xl font-bold text-gray-900">{{ $kelas->siswa_count ?? $siswaCount ?? 0 }}</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">No</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">NIS</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">Nama Siswa</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">Alamat</th>
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
                                <td class="px-5 py-3.5 text-gray-700">{{ $item->alamat ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center">
                                    <p class="font-medium text-gray-600">Belum ada data siswa</p>
                                    <p class="mt-1 text-sm text-gray-500">Siswa pada kelas ini belum ditambahkan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($siswa->hasPages())
                <div class="flex flex-col items-center justify-between gap-3 border-t border-gray-200 bg-gray-50 px-5 py-3.5 sm:flex-row">
                    <p class="text-xs text-gray-500">
                        Menampilkan
                        <span class="font-semibold text-gray-700">{{ $siswa->firstItem() }}–{{ $siswa->lastItem() }}</span>
                        dari
                        <span class="font-semibold text-gray-700">{{ $siswa->total() }}</span>
                        siswa
                    </p>

                    <div class="flex items-center gap-1">
                        @if ($siswa->onFirstPage())
                            <span class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-300 cursor-default select-none">
                                ‹ Prev
                            </span>
                        @else
                            <a href="{{ $siswa->previousPageUrl() }}"
                                class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 transition">
                                ‹ Prev
                            </a>
                        @endif

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
                                <span class="rounded-md border border-green-600 bg-green-700 px-3 py-1.5 text-xs font-semibold text-white select-none">
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

                        @if ($siswa->hasMorePages())
                            <a href="{{ $siswa->nextPageUrl() }}"
                                class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 transition">
                                Next ›
                            </a>
                        @else
                            <span class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-300 cursor-default select-none">
                                Next ›
                            </span>
                        @endif
                    </div>
                </div>
            @else
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
    @endif
@endsection
