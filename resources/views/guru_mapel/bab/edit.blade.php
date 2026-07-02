@extends('layouts.app')
@section('title', 'Edit Bab')

@section('content')
<div class="mx-auto max-w-2xl">
   

    <div class="rounded-xl border border-gray-200 bg-white p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Edit Bab</h2>

        <form method="POST" action="{{ route('guru_mapel.bab.update', $bab) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="mb-4 rounded-lg bg-gray-50 p-4">
                <p class="text-xs text-gray-500">Mata Pelajaran: <span class="font-medium text-gray-900">{{ $bab->guruMapelKelas->mapel->nama ?? '-' }}</span></p>
                <p class="mt-1 text-xs text-gray-500">Kelas: <span class="font-medium text-gray-900">{{ $bab->guruMapelKelas->kelas->nama_kelas ?? '-' }}</span></p>
            </div>

            <div>
                <label for="nama_bab" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Nama Bab <span class="text-red-500">*</span>
                </label>
                <input type="text" id="nama_bab" name="nama_bab" value="{{ old('nama_bab', $bab->nama_bab) }}" required
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                @error('nama_bab')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="urutan" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Urutan <span class="text-red-500">*</span>
                </label>
                <input type="number" id="urutan" name="urutan" value="{{ old('urutan', $bab->urutan) }}" min="1" required
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                @error('urutan')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="deskripsi" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Deskripsi
                </label>
                <textarea id="deskripsi" name="deskripsi" rows="4"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">{{ old('deskripsi', $bab->deskripsi) }}</textarea>
                @error('deskripsi')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <a href="{{ route('guru_mapel.bab.index') }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali
                </a>
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-800">
                    <i class="ti ti-save text-base"></i>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection