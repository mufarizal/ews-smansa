@extends('layouts.app')
@section('title', 'Edit Siswa')

@section('content')

    @php
        $selectedAngkatan = old('angkatan', $siswa->kelas?->angkatan);
    @endphp

    <div class="mb-6">
        <a href="{{ route('kurikulum.siswa.index', ['page' => $page ?? 1]) }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali
        </a>
    </div>

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Edit Data Siswa</h1>
        <p class="mt-2 text-gray-600">Perbarui identitas siswa dan sinkronkan akun login otomatis</p>
    </div>

    <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        Jika NIS diubah, email login juga akan otomatis berubah mengikuti format <span
            class="font-mono">NIS@siswa.com</span>.
    </div>

    <form method="POST" action="{{ route('kurikulum.siswa.update', $siswa->id) }}" class="max-w-3xl">
        @csrf
        @method('PUT')
        <input type="hidden" name="page" value="{{ $page ?? 1 }}">

        <div class="mb-6 rounded-lg border border-gray-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">Data Siswa</h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        Angkatan <span class="text-red-500">*</span>
                    </label>
                    <select id="angkatan" name="angkatan" required
                        class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-800 outline-none transition focus:border-green-600 focus:ring-2 focus:ring-green-100">
                        <option value="">— Pilih Angkatan —</option>
                        @foreach ($angkatanOptions as $angkatan)
                            <option value="{{ $angkatan }}"
                                {{ (string) $selectedAngkatan === (string) $angkatan ? 'selected' : '' }}>
                                {{ $angkatan }}
                            </option>
                        @endforeach
                    </select>
                    @error('angkatan')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        Kelas <span class="text-red-500">*</span>
                    </label>
                    <select id="kelas_id" name="kelas_id" required
                        class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-800 outline-none transition focus:border-green-600 focus:ring-2 focus:ring-green-100">
                        <option value="">— Pilih Kelas —</option>
                        @foreach ($kelas as $item)
                            <option value="{{ $item->id }}" data-angkatan="{{ $item->angkatan }}"
                                {{ old('kelas_id', $siswa->kelas_id) == $item->id ? 'selected' : '' }}>
                                {{ $item->nama_kelas }} - {{ $item->jurusan }} ({{ $item->angkatan }})
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
                    <input type="text" name="nis" value="{{ old('nis', $siswa->nis) }}" required
                        class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 font-mono text-sm text-gray-800 outline-none transition focus:border-green-600 focus:ring-2 focus:ring-green-100">
                    @error('nis')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        Nama Siswa <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama" value="{{ old('nama', $siswa->nama) }}" required
                        class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-800 outline-none transition focus:border-green-600 focus:ring-2 focus:ring-green-100">
                    @error('nama')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">Alamat</label>
                    <textarea name="alamat" rows="3"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-800 outline-none transition focus:border-green-600 focus:ring-2 focus:ring-green-100">{{ old('alamat', $siswa->alamat) }}</textarea>
                    @error('alamat')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mb-6 rounded-lg border border-gray-200 bg-white p-6">
            <h2 class="mb-3 text-lg font-semibold text-gray-900">Akun Login Saat Ini</h2>
            <div class="space-y-2 text-sm text-gray-700">
                <p><span class="font-medium">Email:</span> {{ optional($siswa->user)->email ?? '-' }}</p>
                <p><span class="font-medium">Role default:</span> {{ optional($siswa->user)->default_role ?? '-' }}</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-lg bg-green-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-800 hover:shadow-md">
                Simpan Perubahan
            </button>
            <a href="{{ route('kurikulum.siswa.index', ['page' => $page ?? 1]) }}"
                class="rounded-lg border border-gray-300 px-6 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                Batal
            </a>
        </div>
    </form>

    {{--  <script>
        const angkatanSelect = document.getElementById('angkatan');
        const kelasSelect = document.getElementById('kelas_id');

        function filterKelasByAngkatan() {
            const angkatan = angkatanSelect.value || '';

            Array.from(kelasSelect.options).forEach((option, index) => {
                if (index === 0) {
                    option.hidden = false;
                    option.disabled = false;
                    return;
                }

                const optionAngkatan = option.dataset.angkatan || '';
                const matched = angkatan !== '' && optionAngkatan === angkatan;

                option.hidden = !matched;
                option.disabled = !matched;
            });

            const selectedKelasOption = kelasSelect.options[kelasSelect.selectedIndex];
            if (selectedKelasOption && selectedKelasOption.disabled) {
                kelasSelect.value = '';
            }
        }

        angkatanSelect.addEventListener('change', filterKelasByAngkatan);
        filterKelasByAngkatan();
    </script>  --}}

@endsection
