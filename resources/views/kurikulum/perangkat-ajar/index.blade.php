@extends('layouts.app')
@section('title', 'Perangkat Ajar')

@section('content')
    <div class="min-h-screen bg-slate-50/80 py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Header --}}
            <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Kurikulum</p>
                    <h1 class="mt-2 text-3xl font-bold text-slate-900">Perangkat Ajar</h1>
                    <p class="mt-1 text-sm text-slate-600">Daftar mata pelajaran yang sudah memiliki bab &amp; materi. Klik
                        salah satu untuk melihat detailnya.</p>
                </div>
                @if ($activeSemester)
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-green-200 bg-green-50 px-4 py-2 text-sm font-medium text-green-800">
                        <span class="relative flex h-2 w-2">
                            <span
                                class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-green-500"></span>
                        </span>
                        Semester Aktif: {{ $activeSemester->nama }}
                    </div>
                @endif
            </div>

            @if (session('success'))
                <div
                    class="mb-5 flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    <i class="ti ti-circle-check text-base"></i>
                    {{ session('success') }}
                </div>
            @endif

            {{-- Ringkasan --}}
            <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3.5">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-700">
                        <i class="ti ti-books text-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Mapel Terisi</p>
                        <p class="text-xl font-bold text-gray-900">{{ $mapelCards->count() }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3.5">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-green-50 text-green-700">
                        <i class="ti ti-book text-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total Bab</p>
                        <p class="text-xl font-bold text-gray-900">{{ $mapelCards->sum('babCount') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3.5">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-sky-50 text-sky-700">
                        <i class="ti ti-file-text text-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total Materi</p>
                        <p class="text-xl font-bold text-gray-900">{{ $mapelCards->sum('materiCount') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3.5">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-violet-50 text-violet-700">
                        <i class="ti ti-user-pause text-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Guru Pengampu</p>
                        <p class="text-xl font-bold text-gray-900">
                            {{ $mapelCards->pluck('guru_id')->filter()->unique()->count() }}</p>
                    </div>
                </div>
            </div>

            {{-- Filter --}}
            <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4">
                <form method="GET" action="{{ route('kurikulum.perangkat-ajar.index') }}">
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-gray-600">Semester</label>
                            <select name="semester_id"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-green-600 focus:ring-2 focus:ring-green-100">
                                <option value="">Semua Semester</option>
                                @foreach ($semesterList as $sem)
                                    <option value="{{ $sem->id }}"
                                        {{ (string) ($filters['semester_id'] ?? '') === (string) $sem->id ? 'selected' : '' }}>
                                        {{ $sem->nama }}{{ $sem->is_active ? ' ✓' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-semibold text-gray-600">Kelas</label>
                            <select name="kelas_id"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-green-600 focus:ring-2 focus:ring-green-100">
                                <option value="">Semua Kelas</option>
                                @foreach ($kelasList as $k)
                                    <option value="{{ $k->id }}"
                                        {{ (string) ($filters['kelas_id'] ?? '') === (string) $k->id ? 'selected' : '' }}>
                                        {{ $k->nama_kelas }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-semibold text-gray-600">Mata Pelajaran</label>
                            <select name="mapel_id"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-green-600 focus:ring-2 focus:ring-green-100">
                                <option value="">Semua Mapel</option>
                                @foreach ($mapelList as $mapel)
                                    <option value="{{ $mapel->id }}"
                                        {{ (string) ($filters['mapel_id'] ?? '') === (string) $mapel->id ? 'selected' : '' }}>
                                        {{ $mapel->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-semibold text-gray-600">Guru</label>
                            <select name="guru_id"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-green-600 focus:ring-2 focus:ring-green-100">
                                <option value="">Semua Guru</option>
                                @foreach ($guruList as $guru)
                                    <option value="{{ $guru->id }}"
                                        {{ (string) ($filters['guru_id'] ?? '') === (string) $guru->id ? 'selected' : '' }}>
                                        {{ $guru->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-semibold text-gray-600">Cari</label>
                            <div class="relative">
                                <i class="ti ti-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                                    placeholder="Nama bab atau materi..."
                                    class="w-full rounded-lg border border-gray-300 py-2 pl-9 pr-4 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 pt-3">
                        <div class="flex flex-wrap gap-1.5">
                            @php
                                $activeChips = [];
                                if (!empty($filters['semester_id'])) {
                                    $activeChips[] = [
                                        'label' => 'Semester',
                                        'value' => optional(
                                            $semesterList->firstWhere('id', (int) $filters['semester_id']),
                                        )->nama,
                                    ];
                                }
                                if (!empty($filters['kelas_id'])) {
                                    $activeChips[] = [
                                        'label' => 'Kelas',
                                        'value' => optional($kelasList->firstWhere('id', (int) $filters['kelas_id']))
                                            ->nama_kelas,
                                    ];
                                }
                                if (!empty($filters['mapel_id'])) {
                                    $activeChips[] = [
                                        'label' => 'Mapel',
                                        'value' => optional($mapelList->firstWhere('id', (int) $filters['mapel_id']))
                                            ->nama,
                                    ];
                                }
                                if (!empty($filters['guru_id'])) {
                                    $activeChips[] = [
                                        'label' => 'Guru',
                                        'value' => optional($guruList->firstWhere('id', (int) $filters['guru_id']))
                                            ->nama,
                                    ];
                                }
                                if (!empty($filters['search'])) {
                                    $activeChips[] = ['label' => 'Cari', 'value' => $filters['search']];
                                }
                            @endphp
                            @foreach ($activeChips as $chip)
                                <span
                                    class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                                    {{ $chip['label'] }}: {{ $chip['value'] ?? '-' }}
                                </span>
                            @endforeach
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-green-700 px-5 py-2 text-sm font-medium text-white hover:bg-green-800">
                                <i class="ti ti-filter text-sm"></i> Terapkan Filter
                            </button>
                            <a href="{{ route('kurikulum.perangkat-ajar.index') }}"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                <i class="ti ti-refresh text-sm"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Grid Mapel --}}
            @if ($mapelCards->isEmpty())
                <div class="rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100">
                        <i class="ti ti-book-off text-2xl text-gray-400"></i>
                    </div>
                    <h2 class="text-base font-semibold text-gray-900">Belum ada mapel dengan perangkat ajar</h2>
                    <p class="mt-1.5 text-sm text-gray-500">
                        @if (count($activeChips ?? []) > 0)
                            Tidak ada mapel yang cocok dengan filter yang dipilih.
                        @else
                            Belum ada Guru Mapel yang menginput bab atau materi.
                        @endif
                    </p>
                    @if (count($activeChips ?? []) > 0)
                        <a href="{{ route('kurikulum.perangkat-ajar.index') }}"
                            class="mt-4 inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            <i class="ti ti-refresh text-sm"></i> Hapus semua filter
                        </a>
                    @endif
                </div>
            @else
                <p class="mb-3 text-sm text-gray-500">Menampilkan <span
                        class="font-semibold text-gray-700">{{ $mapelCards->count() }}</span> mapel</p>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($mapelCards as $card)
                        <a href="{{ route('kurikulum.perangkat-ajar.show', ['mapel' => $card->mapel_id, 'guru' => $card->guru_id]) }}"
                            class="group flex flex-col rounded-xl border border-gray-200 bg-white p-5 transition hover:-translate-y-0.5 hover:border-green-300 hover:shadow-md">
                            <div class="flex items-start justify-between gap-2">
                                <div
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-700">
                                    <i class="ti ti-books text-xl"></i>
                                </div>
                                <div class="flex flex-wrap justify-end gap-1">
                                    @foreach ($card->semesterNames as $semNama)
                                        <span
                                            class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">
                                            {{ $semNama }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <h3 class="mt-3 truncate text-base font-semibold text-gray-900 group-hover:text-green-700">
                                {{ $card->mapel->nama ?? '-' }}
                            </h3>

                            <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                <span
                                    class="inline-flex items-center gap-1 rounded-full bg-violet-50 px-2 py-0.5 text-[11px] font-medium text-violet-700">
                                    <i class="ti ti-user-pause text-xs"></i> {{ $card->guru->nama ?? '-' }}
                                </span>
                                <span
                                    class="inline-flex items-center gap-1 rounded-full bg-sky-50 px-2 py-0.5 text-[11px] font-medium text-sky-700"
                                    title="{{ $card->kelasNames->implode(', ') }}">
                                    <i class="ti ti-list-tree text-xs"></i>
                                    {{ $card->kelasCount }} Kelas
                                </span>
                            </div>

                            @if ($card->kelasNames->isNotEmpty())
                                <p class="mt-1.5 truncate text-[11px] text-gray-400"
                                    title="{{ $card->kelasNames->implode(', ') }}">
                                    {{ $card->kelasNames->implode(', ') }}
                                </p>
                            @endif

                            <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3">
                                <div class="flex items-center gap-3 text-xs text-gray-500">
                                    <span class="inline-flex items-center gap-1">
                                        <i class="ti ti-book text-sm"></i> {{ $card->babCount }} Bab
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <i class="ti ti-file-text text-sm"></i> {{ $card->materiCount }} Materi
                                    </span>
                                </div>
                                <i
                                    class="ti ti-arrow-right text-base text-gray-300 transition group-hover:translate-x-0.5 group-hover:text-green-600"></i>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
    