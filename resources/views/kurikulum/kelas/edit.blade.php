@extends('layouts.app')
@section('title', 'Edit Kelas')

@section('content')

    <div class="mb-6">
        <a href="{{ route('kurikulum.kelas.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali
        </a>
    </div>

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Edit Kelas</h1>
        <p class="mt-2 text-gray-600">Perbarui data kelas {{ $kelas->nama_kelas }}</p>
    </div>

    <form method="POST" action="{{ route('kurikulum.kelas.update', $kelas->id) }}" class="max-w-3xl">
        @csrf
        @method('PUT')

        <div class="mb-6 rounded-lg border border-gray-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">Data Kelas</h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        Nama Kelas <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_kelas" value="{{ old('nama_kelas', $kelas->nama_kelas) }}" required
                        @class([
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
                
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        Pilih Data Guru (Wali Kelas) <span class="text-xs text-gray-500">(Opsional)</span>
                    </label>
                    <select name="wali_kelas_id" @class([
                        'w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-gray-800 outline-none transition focus:ring-2',
                        'border-red-500 focus:border-red-500 focus:ring-red-100' => $errors->has(
                            'wali_kelas_id'),
                        'border-gray-300 focus:border-green-600 focus:ring-green-100' => !$errors->has(
                            'wali_kelas_id'),
                    ])>
                        <option value="">-- Tidak dipilih --</option>
                        @foreach ($gurus as $guru)
                            <option value="{{ $guru->id }}"
                                {{ old('wali_kelas_id', $kelas->wali_kelas_id) == $guru->id ? 'selected' : '' }}>
                                {{ $guru->nama }} ({{ $guru->nip }})
                            </option>
                        @endforeach
                    </select>
                    @error('wali_kelas_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-lg bg-green-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-800 hover:shadow-md">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                Update Kelas
            </button>
            <a href="{{ route('kurikulum.kelas.index') }}"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali
            </a>
        </div>
    </form>

@endsection
