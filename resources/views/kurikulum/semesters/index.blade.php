@extends('layouts.app')
@section('title', 'Manajemen Semester')

@section('content')

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manajemen Semester</h1>
            <p class="mt-1 text-sm text-gray-600">Kelola daftar semester akademik</p>
        </div>
        <a href="{{ route('kurikulum.semesters.create') }}"
            class="inline-flex items-center justify-center rounded-lg bg-green-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-800">
            + Tambah Semester
        </a>
    </div>

    <form method="GET" action="{{ route('kurikulum.semesters.index') }}" class="mb-6 grid gap-3 md:grid-cols-2">
        <div class="md:col-span-2">
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari nama semester..."
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 placeholder-gray-400 focus:border-green-600 focus:ring-2 focus:ring-green-100">
        </div>
        <div class="flex gap-2 md:col-span-2">
            <button type="submit"
                class="inline-flex items-center justify-center rounded-lg bg-green-700 px-5 py-2 text-sm font-semibold text-white hover:bg-green-800">
                Terapkan Filter
            </button>
            <a href="{{ route('kurikulum.semesters.index') }}"
                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Reset
            </a>
        </div>
    </form>

    <!-- Table -->
    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-200 bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Nama Semester</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Tanggal Mulai</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Tanggal Selesai</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Status</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($semesters as $semester)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $semester->nama }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ \Carbon\Carbon::parse($semester->tanggal_mulai)->format('Y-m-d') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ \Carbon\Carbon::parse($semester->tanggal_selesai)->format('Y-m-d') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($semester->is_active)
                                <span
                                    class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                    <i class="ti ti-check mr-1"></i>Aktif
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">
                                    <i class="ti ti-x mr-1"></i>Tidak Aktif
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('kurikulum.semesters.edit', $semester->id) }}"
                                    class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-600 hover:bg-green-50 hover:border-green-300 hover:text-green-700 transition"
                                    title="Edit">
                                    <i class="ti ti-pencil"></i>
                                    <span class="hidden sm:inline">Edit</span>
                                </a>
                                <form action="{{ route('kurikulum.semesters.destroy', $semester->id) }}" method="POST"
                                    class="inline"
                                    onsubmit="return confirm('Yakin hapus semester {{ addslashes($semester->nama) }}?')">
                                    @csrf
                                    @method('DELETE')
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
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                            Belum ada semester
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection
