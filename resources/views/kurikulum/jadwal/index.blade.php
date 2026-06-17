@extends('layouts.app')
@section('title', 'Manajemen Jadwal')

@section('content')

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manajemen Jadwal</h1>
            @if ($activeSemester)
                <p class="mt-1 text-sm text-gray-500">
                    Semester aktif:
                    <span class="font-medium text-green-700">{{ $activeSemester->nama }}</span>
                    <span class="text-gray-400">
                        ({{ \Carbon\Carbon::parse($activeSemester->tanggal_mulai)->format('d M Y') }}
                        – {{ \Carbon\Carbon::parse($activeSemester->tanggal_selesai)->format('d M Y') }})
                    </span>
                </p>
            @else
                <p class="mt-1 text-sm text-red-500">Belum ada semester aktif.</p>
            @endif
        </div>
        <a href="{{ route('kurikulum.jadwal.create', ['type' => $type]) }}"
            class="inline-flex items-center gap-1.5 rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-800">
            <i class="ti ti-plus text-base"></i>
            {{ $type === 'kegiatan' ? 'Tambah Kegiatan' : 'Tambah Jadwal' }}
        </a>
    </div>

    {{-- Tab --}}
    <div class="mb-5 flex gap-1 rounded-lg border border-gray-200 bg-gray-50 p-1 w-fit">
        <a href="{{ route('kurikulum.jadwal.index', ['type' => 'pelajaran']) }}"
            class="rounded-md px-4 py-2 text-sm font-medium transition
                   {{ $type === 'pelajaran' ? 'bg-white text-green-700 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
            <i class="ti ti-book-2 mr-1"></i> Jadwal Pelajaran
        </a>
        <a href="{{ route('kurikulum.jadwal.index', ['type' => 'kegiatan']) }}"
            class="rounded-md px-4 py-2 text-sm font-medium transition
                   {{ $type === 'kegiatan' ? 'bg-white text-green-700 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
            <i class="ti ti-calendar-event mr-1"></i> Kegiatan Mingguan
        </a>
    </div>

    {{-- Stats --}}
    <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
        @if ($type === 'pelajaran')
            <div class="rounded-lg bg-gray-50 px-4 py-3">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total (filter)</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ $jadwalPerHari->flatten(1)->count() }}</p>
            </div>
            <div class="rounded-lg bg-gray-50 px-4 py-3">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Hari Ini</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ $todayHari }}</p>
            </div>
            <div class="rounded-lg bg-gray-50 px-4 py-3">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Kelas Hari Ini</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ $jadwalHariIni->pluck('kelas_id')->unique()->count() }}
                </p>
            </div>
            <div class="rounded-lg bg-gray-50 px-4 py-3">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Mapel Hari Ini</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ $jadwalHariIni->pluck('mapel_id')->unique()->count() }}
                </p>
            </div>
        @else
            <div class="rounded-lg bg-gray-50 px-4 py-3">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total Kegiatan</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ $kegiatanPerHari->flatten(1)->count() }}</p>
            </div>
            <div class="rounded-lg bg-gray-50 px-4 py-3">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Hari Ini</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ $todayHari }}</p>
            </div>
            <div class="rounded-lg bg-gray-50 px-4 py-3 sm:col-span-2">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Kegiatan Hari Ini</p>
                <p class="mt-1 text-lg font-bold text-gray-900">
                    {{ $kegiatanHariIni?->nama_kegiatan ?? '—' }}
                </p>
            </div>
        @endif
    </div>
    {{-- Tab Hari --}}
    @php
        $hariList = $type === 'pelajaran' ? $jadwalPerHari->keys()->all() : $kegiatanPerHari->keys()->all();
        $activeHariTab = $selectedHari ?? ($todayHari);
    @endphp
    @if (count($hariList) > 0)
        <div class="mb-4 flex gap-1 rounded-lg border border-gray-200 bg-gray-50 p-1 w-fit overflow-x-auto">
            @foreach ($hariList as $hari)
                <a href="{{ route('kurikulum.jadwal.index', array_merge(request()->except('page'), ['hari' => $hari])) }}"
                    class="rounded-md px-4 py-2 text-sm font-medium transition whitespace-nowrap
                           {{ $activeHariTab === $hari ? 'bg-white text-green-700 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    {{ $hari }}
                </a>
            @endforeach
        </div>
    @endif
    {{-- Filter --}}
    <div class="mb-5 rounded-lg border border-gray-200 bg-white p-4">
        <form method="GET" action="{{ route('kurikulum.jadwal.index') }}">
            <input type="hidden" name="type" value="{{ $type }}">

            @if ($type === 'pelajaran')
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Semester</label>
                        <select name="semester_id"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-green-600 focus:ring-2 focus:ring-green-100">
                            <option value="">Semua Semester</option>
                            @foreach ($semesterList as $sem)
                                <option value="{{ $sem->id }}"
                                    {{ (string) $selectedSemester === (string) $sem->id ? 'selected' : '' }}>
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
                            @foreach ($kelasList as $kelas)
                                <option value="{{ $kelas->id }}"
                                    {{ (string) $selectedKelasId === (string) $kelas->id ? 'selected' : '' }}>
                                    {{ $kelas->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Hari</label>
                        <select name="hari"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-green-600 focus:ring-2 focus:ring-green-100">
                            <option value="">Semua Hari</option>
                            @foreach ($hariOptions as $hari)
                                <option value="{{ $hari }}" {{ $selectedHari === $hari ? 'selected' : '' }}>
                                    {{ $hari }}
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
                                    {{ (string) $selectedMapelId === (string) $mapel->id ? 'selected' : '' }}>
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
                                    {{ (string) $selectedGuruId === (string) $guru->id ? 'selected' : '' }}>
                                    {{ $guru->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Cari</label>
                        <div class="relative">
                            <i class="ti ti-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="search" value="{{ $search }}"
                                placeholder="Nama kelas, mapel, atau guru..."
                                class="w-full rounded-lg border border-gray-300 py-2 pl-9 pr-4 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                        </div>
                    </div>

                </div>
            @else
                {{-- Filter kegiatan: lebih sederhana --}}
                <div class="grid gap-3 sm:grid-cols-3">

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Semester</label>
                        <select name="semester_id"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-green-600 focus:ring-2 focus:ring-green-100">
                            <option value="">Semua Semester</option>
                            @foreach ($semesterList as $sem)
                                <option value="{{ $sem->id }}"
                                    {{ (string) $selectedSemester === (string) $sem->id ? 'selected' : '' }}>
                                    {{ $sem->nama }}{{ $sem->is_active ? ' ✓' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-600">Hari</label>
                        <select name="hari"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-green-600 focus:ring-2 focus:ring-green-100">
                            <option value="">Semua Hari</option>
                            @foreach ($hariOptions as $hari)
                                <option value="{{ $hari }}"
                                    {{ ($selectedHari ?? '') === $hari ? 'selected' : '' }}>
                                    {{ $hari }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>
            @endif

            <div class="mt-3 flex gap-2">
                <button type="submit"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-green-700 px-5 py-2 text-sm font-medium text-white hover:bg-green-800">
                    Terapkan Filter
                </button>
                <a href="{{ route('kurikulum.jadwal.index', ['type' => $type]) }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <i class="ti ti-refresh text-sm"></i> Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Tabel Jadwal Pelajaran --}}
    @if ($type === 'pelajaran')
        @php
            $jadwalsHariIni = $jadwalPerHari->get($activeHariTab, collect());
        @endphp
        @if ($jadwalsHariIni->isNotEmpty())
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead
                            class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Jam</th>
                                <th class="px-4 py-3 text-left">Kelas</th>
                                <th class="px-4 py-3 text-left">Mata Pelajaran</th>
                                <th class="px-4 py-3 text-left">Guru</th>
                                <th class="px-4 py-3 text-left">Semester</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($jadwalsHariIni as $item)
                                <tr class="hover:bg-gray-50 {{ !$item->is_active ? 'opacity-50' : '' }}">
                                    <td class="px-4 py-3 font-mono text-gray-700">
                                        {{ substr((string) $item->jam_mulai, 0, 5) }} –
                                        {{ substr((string) $item->jam_selesai, 0, 5) }}
                                    </td>

                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $item->kelas?->nama_kelas ?? '–' }}
                                    </td>

                                    <td class="px-4 py-3 text-gray-700">{{ $item->mapel?->nama ?? '–' }}</td>

                                    <td class="px-4 py-3 text-gray-700">{{ $item->guru?->nama ?? '–' }}</td>

                                    <td class="px-4 py-3">
                                        <span
                                            class="rounded-full px-2 py-0.5 text-xs
                                            {{ $item->semester?->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' }}">
                                            {{ $item->semester?->nama ?? '–' }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        <div class="inline-flex items-center gap-1">
                                            <a href="{{ route('kurikulum.jadwal.edit', [$item->id, 'type' => 'pelajaran']) }}"
                                                class="inline-flex items-center gap-1 rounded border border-gray-200 px-2.5 py-1.5 text-xs text-gray-600 hover:border-green-300 hover:bg-green-50 hover:text-green-700 transition">
                                                <i class="ti ti-pencil"></i>
                                                <span class="hidden sm:inline">Edit</span>
                                            </a>
                                            <form action="{{ route('kurikulum.jadwal.destroy', $item->id) }}" method="POST"
                                                class="inline" onsubmit="return confirm('Yakin hapus jadwal ini?')">
                                                @csrf @method('DELETE')
                                                <input type="hidden" name="type" value="pelajaran">
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1 rounded border border-gray-200 px-2.5 py-1.5 text-xs text-red-500 hover:border-red-300 hover:bg-red-50 transition">
                                                    <i class="ti ti-trash"></i>
                                                    <span class="hidden sm:inline">Hapus</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="rounded-lg border border-gray-200 bg-white p-6 text-center">
                <i class="ti ti-calendar-off text-3xl text-gray-300 block mb-2"></i>
                <p class="font-medium text-gray-500">Tidak ada jadwal untuk hari ini</p>
                <p class="mt-1 text-xs text-gray-400">Coba pilih hari lain atau tambah jadwal baru</p>
            </div>
        @endif

        {{-- Tabel Kegiatan Mingguan --}}
    @else
        @php
            $kegiatansHariIni = $kegiatanPerHari->get($activeHariTab, collect());
        @endphp
        @if ($kegiatansHariIni->isNotEmpty())
            <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead
                            class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Minggu ke</th>
                                <th class="px-4 py-3 text-left">Nama Kegiatan</th>
                                <th class="px-4 py-3 text-left">Jam</th>
                                <th class="px-4 py-3 text-left">Semester</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($kegiatansHariIni as $item)
                                <tr class="hover:bg-gray-50 {{ !$item->is_active ? 'opacity-50' : '' }}">
                                    <td class="px-4 py-3">
                                        <span
                                            class="rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-700">
                                            Minggu ke-{{ $item->minggu_ke }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $item->nama_kegiatan }}</td>

                                    <td class="px-4 py-3 font-mono text-gray-700">
                                        {{ substr((string) $item->jam_mulai, 0, 5) }} –
                                        {{ substr((string) $item->jam_selesai, 0, 5) }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <span
                                            class="rounded-full px-2 py-0.5 text-xs
                                            {{ $item->semester?->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' }}">
                                            {{ $item->semester?->nama ?? '–' }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        <div class="inline-flex items-center gap-1">
                                            <a href="{{ route('kurikulum.jadwal.edit', [$item->id, 'type' => 'kegiatan']) }}"
                                                class="inline-flex items-center gap-1 rounded border border-gray-200 px-2.5 py-1.5 text-xs text-gray-600 hover:border-green-300 hover:bg-green-50 hover:text-green-700 transition">
                                                <i class="ti ti-pencil"></i>
                                                <span class="hidden sm:inline">Edit</span>
                                            </a>
                                            <form action="{{ route('kurikulum.jadwal.destroy', $item->id) }}" method="POST"
                                                class="inline" onsubmit="return confirm('Yakin hapus kegiatan ini?')">
                                                @csrf @method('DELETE')
                                                <input type="hidden" name="type" value="kegiatan">
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1 rounded border border-gray-200 px-2.5 py-1.5 text-xs text-red-500 hover:border-red-300 hover:bg-red-50 transition">
                                                    <i class="ti ti-trash"></i>
                                                    <span class="hidden sm:inline">Hapus</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="rounded-lg border border-gray-200 bg-white p-6 text-center">
                <i class="ti ti-calendar-off text-3xl text-gray-300 block mb-2"></i>
                <p class="font-medium text-gray-500">Belum ada kegiatan mingguan untuk hari ini</p>
                <p class="mt-1 text-xs text-gray-400">Tambah kegiatan seperti kerohanian, upacara, dll</p>
            </div>
        @endif
    @endif

@endsection
