@extends('layouts.app')
@section('title', 'Edit Ujian Harian')

@section('content')
<div class="mx-auto max-w-2xl">
    <div class="mb-6">
        <a href="{{ route('guru_mapel.ujian.show', $ujianHarian) }}"
            class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800">
            <i class="ti ti-arrow-left text-base"></i>
            Kembali ke detail ujian
        </a>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Edit Ujian Harian</h2>

        <form method="POST" action="{{ route('guru_mapel.ujian.update', $ujianHarian) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="bab_id" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Bab <span class="text-red-500">*</span>
                </label>
                <select id="bab_id" name="bab_id" required
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                    <option value="">-- Pilih Bab --</option>
                    @foreach ($guruMapelKelas as $gmpk)
                        @foreach ($gmpk->babs as $bab)
                            <option value="{{ $bab->id }}" {{ old('bab_id', $ujianHarian->bab_id) == $bab->id ? 'selected' : '' }}>
                                Bab {{ $bab->urutan }}: {{ $bab->nama_bab }}
                            </option>
                        @endforeach
                    @endforeach
                </select>
                @error('bab_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="judul" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Judul Ujian <span class="text-red-500">*</span>
                </label>
                <input type="text" id="judul" name="judul" value="{{ old('judul', $ujianHarian->judul) }}" required
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                @error('judul')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="tanggal_ujian" class="mb-1.5 block text-sm font-medium text-gray-700">
                        Tanggal Ujian <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="tanggal_ujian" name="tanggal_ujian" value="{{ old('tanggal_ujian', $ujianHarian->tanggal_ujian) }}" required
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                    @error('tanggal_ujian')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="durasi_menit" class="mb-1.5 block text-sm font-medium text-gray-700">
                        Durasi (menit) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="durasi_menit" name="durasi_menit" value="{{ old('durasi_menit', $ujianHarian->durasi_menit) }}" min="1" required
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                    @error('durasi_menit')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <a href="{{ route('guru_mapel.ujian.show', $ujianHarian) }}"
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