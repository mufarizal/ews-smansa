@extends('layouts.app')
@section('title', 'Tambah Siswa')

@section('content')
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Tambah Siswa Baru</h1>
        <p class="mt-2 text-gray-600">Sistem otomatis membuat akun login siswa (email dari NIS + password default)</p>
    </div>

    <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
        <p class="font-semibold">Info akun login:</p>
        <p class="mt-1">Email akan dibuat otomatis dengan format <span class="font-mono">NIS@siswa.com</span>.</p>
        <p>Password default untuk semua akun siswa adalah <span class="font-mono">default$123</span>.</p>
    </div>

    <form method="POST" action="{{ route('kurikulum.siswa.store') }}" class="max-w-3xl">
        @csrf

        <div class="mb-6 rounded-lg border border-gray-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">Data Siswa</h2>

            <div class="grid gap-4 sm:grid-cols-2">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        Kelas <span class="text-red-500">*</span>
                    </label>
                    <select id="kelas_id" name="kelas_id" required
                        class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-800 outline-none transition focus:border-green-600 focus:ring-2 focus:ring-green-100">
                        <option value="">— Pilih Kelas —</option>
                        @foreach ($kelas as $item)
                            <option value="{{ $item->id }}" data-angkatan="{{ $item->angkatan }}"
                                {{ old('kelas_id') == $item->id ? 'selected' : '' }}>
                                {{ $item->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                    @error('kelas_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        NIS <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nis" value="{{ old('nis') }}" required
                        placeholder="Contoh: 1234567890"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 font-mono text-sm text-gray-800 outline-none transition focus:border-green-600 focus:ring-2 focus:ring-green-100">
                    @error('nis')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        Nama Siswa <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama" value="{{ old('nama') }}" required
                        placeholder="Masukkan nama lengkap siswa"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-800 outline-none transition focus:border-green-600 focus:ring-2 focus:ring-green-100">
                    @error('nama')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Alamat</label>
                    <textarea name="alamat" rows="3" placeholder="Alamat siswa (opsional)"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-800 outline-none transition focus:border-green-600 focus:ring-2 focus:ring-green-100">{{ old('alamat') }}</textarea>
                    @error('alamat')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-lg bg-green-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-800 hover:shadow-md">
                Simpan Siswa
            </button>
            <a href="{{ route('kurikulum.siswa.index') }}"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-6 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali
            </a>
        </div>
    </form>

    {{--  <script>
        const kelasSelect = document.getElementById('kelas_id');

        function filterKelasByAngkatan() {
            Array.from(kelasSelect.options).forEach((option, index) => {
                if (index === 0) {
                    option.hidden = false;
                    option.disabled = false;
                    return;
                }

                const optionAngkatan = option.dataset.angkatan || '';
                const matched = optionAngkatan !== '';

                option.hidden = !matched;
                option.disabled = !matched;
            });

            const selectedKelasOption = kelasSelect.options[kelasSelect.selectedIndex];
            if (selectedKelasOption && selectedKelasOption.disabled) {
                kelasSelect.value = '';
            }
        }

        filterKelasByAngkatan();
    </script>  --}}

@endsection
