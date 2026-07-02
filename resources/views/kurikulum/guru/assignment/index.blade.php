@extends('layouts.app')

@section('title', 'Penugasan Guru')

@section('content')

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Penugasan Guru</h1>
        <p class="mt-1 text-sm text-gray-500">Atur penugasan mapel, piket, wali kelas, dan BK</p>
    </div>

    {{-- Alert Success --}}
    @if (session('success'))
        <div
            class="mb-4 flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Alert Error --}}
    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Banner Semester Aktif --}}
    @if ($activeSemester)
        <div class="mb-6 flex items-center gap-3 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3">
            <span class="inline-block h-2 w-2 shrink-0 rounded-full bg-blue-500"></span>
            <span class="text-sm text-blue-800">
                Semester aktif:
                <strong>{{ $activeSemester->nama }}</strong>
                <span class="text-blue-600">
                    ({{ \Carbon\Carbon::parse($activeSemester->tanggal_mulai)->translatedFormat('d M Y') }}
                    – {{ \Carbon\Carbon::parse($activeSemester->tanggal_selesai)->translatedFormat('d M Y') }})
                </span>
            </span>
            <span class="ml-auto text-xs text-blue-500">Semua penugasan otomatis masuk ke semester ini</span>
        </div>
    @else
        <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-4">
            <p class="text-sm font-medium text-amber-800">Belum ada semester yang aktif</p>
            <p class="mt-1 text-xs text-amber-700">
                Aktifkan semester di
                <a href="{{ route('kurikulum.semesters.index') }}" class="underline font-medium">Manajemen Semester</a>
                sebelum membuat penugasan.
            </p>
        </div>
    @endif

    {{-- Tab Navigation --}}
    <div class="mb-6 border-b border-gray-200">
        <nav class="-mb-px flex gap-1">
            @foreach ([
            'mapel' => ['label' => 'Mapel', 'route' => 'kurikulum.penugasan-guru.mapel.index'],
            'piket' => ['label' => 'Piket', 'route' => 'kurikulum.penugasan-guru.piket.index'],
            'wali' => ['label' => 'Wali Kelas', 'route' => 'kurikulum.penugasan-guru.wali.index'],
            'bk' => ['label' => 'Guru BK', 'route' => 'kurikulum.penugasan-guru.bk.index'],
        ] as $tab => $cfg)
                <a href="{{ route($cfg['route']) }}"
                    class="px-4 py-2.5 text-sm font-medium border-b-2 transition
                        {{ $activeTab === $tab
                            ? 'border-green-600 text-green-700'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    {{ $cfg['label'] }}
                </a>
            @endforeach
        </nav>
    </div>

    {{-- Wrapper disabled kalau tidak ada semester aktif --}}
    <div class="{{ $activeSemester ? '' : 'pointer-events-none select-none opacity-40' }}">

        {{-- ===================== TAB MAPEL ===================== --}}
        @if ($activeTab === 'mapel')
            <div class="space-y-6">
                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <form method="POST" action="{{ route('kurikulum.penugasan-guru.mapel.store') }}" class="space-y-6">
                        @csrf

                        {{-- Pilih Guru --}}
                        <div class="max-w-lg">
                            <label class="block text-sm font-medium text-gray-700">
                                Pilih Guru <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="guru_search_mapel" placeholder="Cari nama atau NIP..."
                                class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-600 focus:outline-none focus:ring-2 focus:ring-green-100">
                            <select name="guru_id" id="guru_id_mapel" required
                                class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-600 focus:outline-none focus:ring-2 focus:ring-green-100">
                                <option value="">-- Pilih Guru --</option>
                                @foreach ($gurus as $guru)
                                    <option value="{{ $guru->id }}"
                                        data-search="{{ strtolower($guru->nama . ' ' . $guru->nip) }}"
                                        {{ old('guru_id') == $guru->id ? 'selected' : '' }}>
                                        {{ $guru->nama }} ({{ $guru->nip }})
                                    </option>
                                @endforeach
                            </select>
                            @error('guru_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Rows Mapel–Kelas (checklist) --}}
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <div class="mb-3 flex items-center justify-between">
                                <label class="text-sm font-medium text-gray-700">
                                    Penugasan Mapel per Kelas <span class="text-red-500">*</span>
                                </label>
                                <button type="button" id="btn-add-mapel"
                                    class="rounded-lg border border-green-300 bg-white px-4 py-2 text-sm font-semibold text-green-700 hover:bg-green-50">
                                    + Tambah Mapel
                                </button>
                            </div>

                            <div id="mapel-wrapper" class="space-y-3">
                                @php
                                    $oldRows =
                                        old('mapel_kelas') && is_array(old('mapel_kelas'))
                                            ? old('mapel_kelas')
                                            : [['mapel_id' => '', 'kelas_ids' => []]];
                                @endphp
                                @foreach ($oldRows as $i => $row)
                                    <div class="mapel-row rounded-lg border border-gray-200 bg-white p-4">
                                        <div class="mb-3 flex items-center justify-between">
                                            <div class="w-64">
                                                <label class="mb-1 block text-xs font-medium text-gray-500">Mata
                                                    Pelajaran</label>
                                                <select name="mapel_kelas[{{ $i }}][mapel_id]" required
                                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-600 focus:outline-none focus:ring-2 focus:ring-green-100">
                                                    <option value="">-- Pilih Mapel --</option>
                                                    @foreach ($mapels as $mapel)
                                                        <option value="{{ $mapel->id }}"
                                                            {{ ($row['mapel_id'] ?? '') == $mapel->id ? 'selected' : '' }}>
                                                            {{ $mapel->nama }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <button type="button"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-100">
                                            Hapus
                                            </button>
                                        </div>
                                        <div>
                                            <label class="mb-2 block text-xs font-medium text-gray-500">Kelas yang Diajar
                                                <span class="text-red-500">*</span></label>
                                            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                                @foreach ($kelas as $k)
                                                    <label
                                                        class="flex cursor-pointer items-center gap-2 rounded-lg border-2 border-gray-200 bg-gray-50 px-3 py-2 transition hover:border-green-400 hover:bg-green-50 has-[:checked]:border-green-500 has-[:checked]:bg-green-50">
                                                        <input type="checkbox"
                                                            name="mapel_kelas[{{ $i }}][kelas_ids][]"
                                                            value="{{ $k->id }}"
                                                            class="h-4 w-4 rounded border-gray-300 text-green-600"
                                                            {{ in_array($k->id, $row['kelas_ids'] ?? []) ? 'checked' : '' }}>
                                                        <span
                                                            class="text-sm font-medium text-gray-700">{{ $k->nama_kelas }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('mapel_kelas')
                                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                            Penugasan mapel sebelumnya di semester aktif akan diganti seluruhnya dengan pilihan baru.
                        </div>

                        <button type="submit"
                            class="rounded-lg bg-green-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-800 disabled:opacity-50">
                            Simpan Penugasan Mapel
                        </button>
                    </form>
                </div>

                {{-- Tabel Penugasan Mapel --}}
                @php
                    $guruMapelAktif = $gurus->filter(fn($g) => $g->guruMapelKelas->isNotEmpty());
                @endphp
                @if ($guruMapelAktif->isNotEmpty())
                    <div class="rounded-lg border border-gray-200 bg-white p-6">
                        <h3 class="mb-4 text-sm font-semibold text-gray-700">
                            Penugasan Mapel Semester Aktif
                            @if ($activeSemester)
                                <span class="ml-1 font-normal text-gray-500">— {{ $activeSemester->nama }}</span>
                            @endif
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                                    <tr>
                                        <th class="px-4 py-3">Guru</th>
                                        <th class="px-4 py-3">Mapel</th>
                                        <th class="px-4 py-3">Kelas</th>
                                        <th class="px-4 py-3 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($guruMapelAktif as $g)
                                        @php
                                            $byMapel = $g->guruMapelKelas->groupBy('mapel_id');
                                        @endphp
                                        @foreach ($byMapel as $mapelId => $assignments)
                                            <tr>
                                                @if ($loop->first)
                                                    <td class="px-4 py-3" rowspan="{{ $byMapel->count() }}">
                                                        <p class="font-medium text-gray-900">{{ $g->nama }}</p>
                                                        <p class="text-xs text-gray-500">{{ $g->nip }}</p>
                                                    </td>
                                                @endif
                                                <td class="px-4 py-3 text-gray-700">
                                                    {{ $assignments->first()->mapel->nama ?? '-' }}
                                                </td>
                                                <td class="px-4 py-3">
                                                    <div class="flex flex-wrap gap-1">
                                                        @foreach ($assignments as $a)
                                                            <span
                                                                class="inline-block rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                                                {{ $a->kelas->nama_kelas ?? '-' }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </td>
                                                @if ($loop->first)
                                                    <td class="px-4 py-3 text-right" rowspan="{{ $byMapel->count() }}">
                                                        <form method="POST"
                                                            action="{{ route('kurikulum.penugasan-guru.mapel.destroy', $g) }}"
                                                            onsubmit="return confirm('Hapus semua penugasan mapel {{ $g->nama }} di semester aktif?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">
                                                                Hapus
                                                            </button>
                                                        </form>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Template untuk tambah baris mapel baru via JS --}}
            <template id="mapel-row-template">
                <div class="mapel-row rounded-lg border border-gray-200 bg-white p-4">
                    <div class="mb-3 flex items-center justify-between">
                        <div class="w-64">
                            <label class="mb-1 block text-xs font-medium text-gray-500">Mata Pelajaran</label>
                            <select
                                class="mapel-select w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-600 focus:outline-none focus:ring-2 focus:ring-green-100"
                                required>
                                <option value="">-- Pilih Mapel --</option>
                                @foreach ($mapels as $mapel)
                                    <option value="{{ $mapel->id }}">{{ $mapel->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button"
                            class="btn-remove-mapel-row rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">
                            Hapus
                        </button>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-medium text-gray-500">Kelas yang Diajar <span
                                class="text-red-500">*</span></label>
                        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($kelas as $k)
                                <label
                                    class="flex cursor-pointer items-center gap-2 rounded-lg border-2 border-gray-200 bg-gray-50 px-3 py-2 transition hover:border-green-400 hover:bg-green-50 has-[:checked]:border-green-500 has-[:checked]:bg-green-50">
                                    <input type="checkbox"
                                        class="kelas-checkbox h-4 w-4 rounded border-gray-300 text-green-600"
                                        value="{{ $k->id }}">
                                    <span class="text-sm font-medium text-gray-700">{{ $k->nama_kelas }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </template>
        @endif

        {{-- ===================== TAB PIKET ===================== --}}
        @if ($activeTab === 'piket')
            <div class="rounded-lg border border-gray-200 bg-white p-6">
                <form method="POST" action="{{ route('kurikulum.penugasan-guru.piket.store') }}" class="space-y-6">
                    @csrf

                    {{-- Pilih Guru --}}
                    <div class="max-w-lg">
                        <label class="block text-sm font-medium text-gray-700">
                            Pilih Guru <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="guru_search_piket" placeholder="Cari nama atau NIP..."
                            class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-600 focus:outline-none focus:ring-2 focus:ring-green-100">
                        <select name="guru_id" id="guru_id_piket" required
                            class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-600 focus:outline-none focus:ring-2 focus:ring-green-100">
                            <option value="">-- Pilih Guru --</option>
                            @foreach ($gurus as $guru)
                                <option value="{{ $guru->id }}"
                                    data-search="{{ strtolower($guru->nama . ' ' . $guru->nip) }}"
                                    {{ old('guru_id') == $guru->id ? 'selected' : '' }}>
                                    {{ $guru->nama }} ({{ $guru->nip }})
                                </option>
                            @endforeach
                        </select>
                        @error('guru_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Hari Piket --}}
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <label class="mb-3 block text-sm font-medium text-gray-700">
                            Pilih Hari Piket <span class="text-red-500">*</span>
                        </label>
                        <div class="flex flex-wrap gap-2">
                            @php $selectedDays = old('piket_days', []); @endphp
                            @foreach ($piketDays as $day)
                                <label
                                    class="flex cursor-pointer items-center gap-2 rounded-lg border-2 border-gray-200 bg-white px-4 py-2 transition hover:border-green-400 hover:bg-green-50 has-[:checked]:border-green-500 has-[:checked]:bg-green-50">
                                    <input type="checkbox" name="piket_days[]" value="{{ $day }}"
                                        class="h-4 w-4 rounded border-gray-300 text-green-600"
                                        {{ in_array($day, $selectedDays) ? 'checked' : '' }}>
                                    <span class="text-sm font-medium text-gray-700">{{ $day }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('piket_days')
                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                        class="rounded-lg bg-green-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-800">
                        Simpan Penugasan Piket
                    </button>
                </form>
            </div>
        @endif

        {{-- ===================== TAB WALI ===================== --}}
        @if ($activeTab === 'wali')
            <div class="rounded-lg border border-gray-200 bg-white p-6">
                <form method="POST" action="{{ route('kurikulum.penugasan-guru.wali.store') }}" class="space-y-6">
                    @csrf

                    <div class="grid gap-6 lg:grid-cols-2">
                        {{-- Pilih Guru --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Pilih Guru <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="guru_search_wali" placeholder="Cari nama atau NIP..."
                                class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-600 focus:outline-none focus:ring-2 focus:ring-green-100">
                            <select name="guru_id" id="guru_id_wali" required
                                class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-600 focus:outline-none focus:ring-2 focus:ring-green-100">
                                <option value="">-- Pilih Guru --</option>
                                @foreach ($gurus as $guru)
                                    <option value="{{ $guru->id }}"
                                        data-search="{{ strtolower($guru->nama . ' ' . $guru->nip) }}"
                                        {{ old('guru_id') == $guru->id ? 'selected' : '' }}>
                                        {{ $guru->nama }} ({{ $guru->nip }})
                                    </option>
                                @endforeach
                            </select>
                            @error('guru_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Pilih Kelas --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Pilih Kelas <span class="text-red-500">*</span>
                            </label>
                            <select name="kelas_id" required
                                class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-600 focus:outline-none focus:ring-2 focus:ring-green-100">
                                <option value="">-- Pilih Kelas --</option>
                                @foreach ($kelas as $k)
                                    <option value="{{ $k->id }}"
                                        {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama_kelas }}
                                        @if ($k->waliKelas)
                                            — Wali: {{ $k->waliKelas->nama }}
                                        @else
                                            — Belum ada wali
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('kelas_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                        Satu kelas hanya bisa memiliki satu wali kelas. Jika guru sudah menjadi wali di kelas lain,
                        penugasan lamanya akan otomatis dilepas.
                    </div>

                    <button type="submit"
                        class="rounded-lg bg-green-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-800">
                        Simpan Wali Kelas
                    </button>
                </form>
            </div>
        @endif

        {{-- ===================== TAB BK ===================== --}}
        @if ($activeTab === 'bk')
            <div class="space-y-6">
                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <form method="POST" action="{{ route('kurikulum.penugasan-guru.bk.store') }}" class="space-y-6">
                        @csrf

                        {{-- Pilih Guru BK --}}
                        <div class="max-w-lg">
                            <label class="block text-sm font-medium text-gray-700">
                                Pilih Guru BK <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="guru_search_bk" placeholder="Cari nama atau NIP guru BK..."
                                class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-600 focus:outline-none focus:ring-2 focus:ring-green-100">
                            <select name="guru_id" id="guru_id_bk" required
                                class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-600 focus:outline-none focus:ring-2 focus:ring-green-100">
                                <option value="">-- Pilih Guru BK --</option>
                                @foreach ($guruBkList as $guru)
                                    <option value="{{ $guru->id }}"
                                        data-search="{{ strtolower($guru->nama . ' ' . $guru->nip) }}"
                                        {{ old('guru_id') == $guru->id ? 'selected' : '' }}>
                                        {{ $guru->nama }} ({{ $guru->nip }})
                                    </option>
                                @endforeach
                            </select>
                            @error('guru_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Pilih Kelas --}}
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <label class="mb-3 block text-sm font-medium text-gray-700">
                                Kelas yang Diampu <span class="text-red-500">*</span>
                            </label>
                            @php $selectedKelasIds = old('kelas_ids', []); @endphp
                            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($kelas as $k)
                                    <label
                                        class="flex cursor-pointer items-center gap-2 rounded-lg border-2 border-gray-200 bg-white px-3 py-2 transition hover:border-green-400 hover:bg-green-50 has-[:checked]:border-green-500 has-[:checked]:bg-green-50">
                                        <input type="checkbox" name="kelas_ids[]" value="{{ $k->id }}"
                                            class="h-4 w-4 rounded border-gray-300 text-green-600"
                                            {{ in_array($k->id, $selectedKelasIds) ? 'checked' : '' }}>
                                        <span class="text-sm font-medium text-gray-700">{{ $k->nama_kelas }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('kelas_ids')
                                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                            Guru BK dapat menangani lebih dari satu kelas.
                            Penugasan sebelumnya di semester aktif akan diganti dengan pilihan baru.
                        </div>

                        <button type="submit"
                            class="rounded-lg bg-green-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-800">
                            Simpan Penugasan Guru BK
                        </button>
                    </form>
                </div>

                {{-- Tabel Penugasan BK --}}
                @php $guruBkAktif = $guruBkList->filter(fn($g) => $g->guruBkKelas->isNotEmpty()); @endphp
                @if ($guruBkAktif->isNotEmpty())
                    <div class="rounded-lg border border-gray-200 bg-white p-6">
                        <h3 class="mb-4 text-sm font-semibold text-gray-700">
                            Penugasan BK Semester Aktif
                            @if ($activeSemester)
                                <span class="ml-1 font-normal text-gray-500">— {{ $activeSemester->nama }}</span>
                            @endif
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                                    <tr>
                                        <th class="px-4 py-3">Guru BK</th>
                                        <th class="px-4 py-3">Kelas Diampu</th>
                                        <th class="px-4 py-3 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($guruBkAktif as $guruBk)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <p class="font-medium text-gray-900">{{ $guruBk->nama }}</p>
                                                <p class="text-xs text-gray-500">{{ $guruBk->nip }}</p>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach ($guruBk->guruBkKelas as $bk)
                                                        <span
                                                            class="inline-block rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                                            {{ $bk->kelas->nama_kelas ?? '-' }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <form method="POST"
                                                    action="{{ route('kurikulum.penugasan-guru.bk.destroy', $guruBk) }}"
                                                    onsubmit="return confirm('Hapus penugasan BK {{ $guruBk->nama }} di semester aktif?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        @endif

    </div>{{-- end wrapper disabled --}}

    @push('scripts')
        <script>
            // ── Live search pada semua select guru ─────────────────────────────
            document.querySelectorAll('[id^="guru_search_"]').forEach(input => {
                const suffix = input.id.replace('guru_search_', '');
                const select = document.getElementById('guru_id_' + suffix);
                if (!select) return;
                const options = [...select.querySelectorAll('option[data-search]')];

                input.addEventListener('input', () => {
                    const q = input.value.toLowerCase();
                    options.forEach(opt => {
                        opt.hidden = q && !opt.dataset.search.includes(q);
                    });
                    if (select.selectedOptions[0]?.hidden) select.value = '';
                });
            });

            // ── Dynamic rows Mapel–Kelas (checklist) ───────────────────────────
            const wrapper = document.getElementById('mapel-wrapper');
            const template = document.getElementById('mapel-row-template');
            const btnAdd = document.getElementById('btn-add-mapel');

            function reindexRows() {
                wrapper?.querySelectorAll('.mapel-row').forEach((row, i) => {
                    // mapel select
                    const mapelSel = row.querySelector('select');
                    if (mapelSel) mapelSel.name = `mapel_kelas[${i}][mapel_id]`;

                    // kelas checkboxes
                    row.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                        cb.name = `mapel_kelas[${i}][kelas_ids][]`;
                    });
                });
            }

            btnAdd?.addEventListener('click', () => {
                const clone = template.content.cloneNode(true);
                wrapper.appendChild(clone);
                reindexRows();
            });

            wrapper?.addEventListener('click', e => {
                if (e.target.closest('.btn-remove-mapel-row')) {
                    const row = e.target.closest('.mapel-row');
                    if (wrapper.querySelectorAll('.mapel-row').length > 1) {
                        row.remove();
                        reindexRows();
                    }
                }
            });
        </script>
    @endpush

@endsection
