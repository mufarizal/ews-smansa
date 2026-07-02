@extends('layouts.app')
@section('title', 'Manajemen Mata Pelajaran')

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
            <h1 class="text-3xl font-bold text-gray-900">Manajemen Mata Pelajaran</h1>
            <p class="mt-1 text-sm text-gray-600">Kelola daftar mata pelajaran</p>
        </div>
        <a href="{{ route('kurikulum.mapel.create') }}"
            class="inline-flex items-center justify-center rounded-lg bg-green-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-800">
            + Tambah Mapel
        </a>
    </div>

    <form method="GET" action="{{ route('kurikulum.mapel.index') }}" class="mb-6 grid gap-3 md:grid-cols-2">
        <div class="md:col-span-2">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama mata pelajaran..."
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 placeholder-gray-400 focus:border-green-600 focus:ring-2 focus:ring-green-100">
        </div>
        <div class="flex gap-2 md:col-span-2">
            <button type="submit"
                class="inline-flex items-center justify-center rounded-lg bg-green-700 px-5 py-2 text-sm font-semibold text-white hover:bg-green-800">
                Terapkan Filter
            </button>
            <a href="{{ route('kurikulum.mapel.index') }}"
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
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Nama Mapel</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Dibuat</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($mapels as $mapel)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $mapel->nama }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $mapel->created_at->format('d M Y') }}</td>
                        <td class="px-5 py-3.5 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('kurikulum.mapel.edit', $mapel->id) }}"
                                    class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-600 hover:bg-green-50 hover:border-green-300 hover:text-green-700 transition"
                                    title="Edit">
                                    <i class="ti ti-pencil"></i>
                                    <span class="hidden sm:inline">Edit</span>
                                </a>
                                <form action="{{ route('kurikulum.mapel.destroy', $mapel->id) }}" method="POST"
                                    class="inline"
                                    onsubmit="return confirm('Yakin hapus mapel {{ addslashes($mapel->nama) }}?')">
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
                        <td colspan="3" class="px-4 py-6 text-center text-gray-500">
                            Belum ada mata pelajaran
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection
