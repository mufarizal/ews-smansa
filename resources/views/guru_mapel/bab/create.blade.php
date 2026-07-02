@extends('layouts.app')
@section('title', 'Tambah Bab')

@section('content')
<div class="mx-auto max-w-2xl">
   

    <div class="rounded-xl border border-gray-200 bg-white p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Tambah Bab Baru</h2>

        <form method="POST" action="{{ route('guru_mapel.bab.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="guru_mapel_kelas_id" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Mata Pelajaran <span class="text-red-500">*</span>
                </label>
                <select id="guru_mapel_kelas_id" name="guru_mapel_kelas_id" required
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                    <option value="">-- Pilih Mata Pelajaran --</option>
                    @foreach ($assignments as $mapelName => $kelasItems)
                        <option value="{{ $kelasItems->first()->id }}" {{ old('guru_mapel_kelas_id') == $kelasItems->first()->id ? 'selected' : '' }}>
                            {{ $mapelName }}
                        </option>
                    @endforeach
                </select>
                @error('guru_mapel_kelas_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="nama_bab" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Nama Bab <span class="text-red-500">*</span>
                </label>
                <input type="text" id="nama_bab" name="nama_bab" value="{{ old('nama_bab') }}" required
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                @error('nama_bab')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="urutan" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Urutan <span class="text-red-500">*</span>
                </label>
                <input type="number" id="urutan" name="urutan" value="{{ old('urutan', 1) }}" min="1" required
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
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">{{ old('deskripsi') }}</textarea>
                @error('deskripsi')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <a href="{{ route('guru_mapel.bab.index') }}"
                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Batal
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