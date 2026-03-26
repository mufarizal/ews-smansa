@extends('layouts.app')
@section('title', 'Tambah Kelas')

@section('content')

    <div class="mb-6">
        <a href="{{ route('admin.kelas.index') }}" class="text-sm text-gray-600 transition hover:text-gray-900">
            ← Kembali ke Manajemen Kelas
        </a>
    </div>

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Tambah Kelas Baru</h1>
        <p class="mt-2 text-gray-600">Isi data kelas untuk menambahkan ke sistem</p>
    </div>

    <form method="POST" action="{{ route('admin.kelas.store') }}" class="max-w-3xl">
        @csrf

        <div class="mb-6 rounded-lg border border-gray-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">Data Kelas</h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        Nama Kelas <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_kelas" value="{{ old('nama_kelas') }}" required
                        placeholder="Contoh: X IPA 1" @class([
                            'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-gray-800 placeholder-gray-400 outline-none transition focus:ring-2',
                            'border-red-500 focus:border-red-500 focus:ring-red-100' => $errors->has(
                                'nama_kelas'),
                            'border-gray-300 focus:border-green-600 focus:ring-green-100' => !$errors->has(
                                'nama_kelas'),
                        ])>
                    @error('nama_kelas')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        Jurusan <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="jurusan" value="{{ old('jurusan') }}" required placeholder="Contoh: IPA"
                        @class([
                            'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-gray-800 placeholder-gray-400 outline-none transition focus:ring-2',
                            'border-red-500 focus:border-red-500 focus:ring-red-100' => $errors->has(
                                'jurusan'),
                            'border-gray-300 focus:border-green-600 focus:ring-green-100' => !$errors->has(
                                'jurusan'),
                        ])>
                    @error('jurusan')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        Angkatan <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="angkatan" value="{{ old('angkatan') }}" required min="1900"
                        max="2100" placeholder="Contoh: 2026" @class([
                            'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-gray-800 placeholder-gray-400 outline-none transition focus:ring-2',
                            'border-red-500 focus:border-red-500 focus:ring-red-100' => $errors->has(
                                'angkatan'),
                            'border-gray-300 focus:border-green-600 focus:ring-green-100' => !$errors->has(
                                'angkatan'),
                        ])>
                    @error('angkatan')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-lg bg-green-700 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-800 hover:shadow-md">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                Simpan Kelas
            </button>
            <a href="{{ route('admin.kelas.index') }}"
                class="rounded-lg border border-gray-300 px-6 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                Batal
            </a>
        </div>
    </form>

@endsection
