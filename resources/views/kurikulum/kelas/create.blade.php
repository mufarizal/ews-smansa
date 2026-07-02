@extends('layouts.app')
@section('title', 'Tambah Kelas')

@section('content')

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Tambah Kelas</h1>
        <p class="mt-2 text-sm text-gray-600">Buat kelas baru dan tentukan wali kelasnya</p>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <strong>Validasi gagal:</strong>
            <ul class="mt-2 list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('kurikulum.kelas.store') }}" class="space-y-6">
        @csrf

        <!-- SECTION 1: INFORMASI DASAR -->
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                <h2 class="text-lg font-semibold text-gray-900">1. Identitas Kelas</h2>
                <p class="mt-1 text-sm text-gray-600">Isi nama kelas.</p>
            </div>

            <div class="p-6 space-y-4">
                <div>
                    <label for="nama_kelas" class="block text-sm font-medium text-gray-700">
                        Nama Kelas <span class="text-red-600">*</span>
                    </label>
                    <input type="text" id="nama_kelas" name="nama_kelas" value="{{ old('nama_kelas') }}" required
                        placeholder="Contoh: X-1, X-2, X-3"
                        class="mt-2 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                    @error('nama_kelas')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- SECTION 2: WALI KELAS -->
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                <h2 class="text-lg font-semibold text-gray-900">2. Wali Kelas (Opsional)</h2>
                <p class="mt-1 text-sm text-gray-600">Pilih guru mapel pengajar yang menjadi wali kelas</p>
            </div>

            <div class="p-6">
                <select id="wali_kelas_id" name="wali_kelas_id"
                    class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                    <option value="">-- Belum ada wali kelas --</option>
                    @forelse ($gurus as $guru)
                        <option value="{{ $guru->id }}" {{ old('wali_kelas_id') == $guru->id ? 'selected' : '' }}>
                            {{ $guru->nama }} ({{ $guru->nip }})
                        </option>
                    @empty
                        <option value="" disabled>Tidak ada guru mapel yang terdaftar. <a
                                href="{{ route('kurikulum.guru.create') }}">Buat guru terlebih dahulu</a></option>
                    @endforelse
                </select>
                @error('wali_kelas_id')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-xs text-gray-500">Hanya guru pengajar mapel yang dapat menjadi wali kelas</p>
            </div>
        </div>

        <!-- BUTTONS -->
        <div class="flex justify-between gap-3">
            <a href="{{ route('kurikulum.kelas.index') }}"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali
            </a>
            <button type="submit"
                class="rounded-lg bg-green-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-green-800">
                Simpan Kelas
            </button>
        </div>
    </form>

@endsection
