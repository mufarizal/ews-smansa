@extends('layouts.app')
@section('title', 'Tambah Guru')

@section('content')

    {{-- Breadcrumb + Header --}}
    <div class="mb-6">

        <h1 class="mt-2 text-2xl font-bold text-gray-900">Tambah Guru Baru</h1>
        <p class="mt-1 text-sm text-gray-500">Data dasar guru — penugasan diatur setelah guru tersimpan</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-lg border border-gray-200 bg-white p-6">

        {{-- Info akun --}}
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            <p class="font-semibold mb-1">
                <i class="ti ti-info-circle align-text-bottom"></i> Info akun login otomatis
            </p>
            <p>Email: <code class="rounded bg-green-100 px-1 py-0.5 text-xs">NIP@sma.com</code>
                &nbsp;·&nbsp;
                Password: <code class="rounded bg-green-100 px-1 py-0.5 text-xs">default$123</code>
            </p>
            <p class="mt-1 text-green-700 text-xs">Sampaikan info ini ke guru yang bersangkutan setelah tersimpan.</p>
        </div>

        <form method="POST" action="{{ route('kurikulum.guru.store') }}" class="space-y-5">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                {{-- NIP --}}
                <div>
                    <label for="nip" class="block text-sm font-medium text-gray-700">
                        NIP <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="nip" name="nip" value="{{ old('nip') }}"
                        placeholder="cth. 198801012010"
                        class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                  focus:border-green-600 focus:ring-2 focus:ring-green-100">
                    <p class="mt-1 text-xs text-gray-400">Hanya angka — akan menjadi username login</p>
                    @error('nip')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Nama --}}
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama') }}"
                        placeholder="cth. Ahmad Fauzan, S.Pd"
                        class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                  focus:border-green-600 focus:ring-2 focus:ring-green-100">
                    @error('nama')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- No HP --}}
            <div class="sm:w-1/2">
                <label for="no_hp" class="block text-sm font-medium text-gray-700">
                    Nomor HP <span class="text-gray-400 font-normal">(opsional)</span>
                </label>
                <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp') }}"
                    placeholder="cth. 08123456789"
                    class="mt-1.5 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                              focus:border-green-600 focus:ring-2 focus:ring-green-100">
                @error('no_hp')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Catatan lanjutan --}}
            <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600">
                <i class="ti ti-arrow-right align-text-bottom"></i>
                Setelah tersimpan, atur penugasan mapel, piket, BK, atau wali kelas
                di menu <a href="{{ route('kurikulum.penugasan-guru.mapel.index') }}"
                    class="font-medium text-green-700 underline underline-offset-2">Penugasan Guru</a>.
            </div>

            {{-- Tombol --}}
            <div class="flex justify-between pt-1">
                <a href="{{ route('kurikulum.guru.index') }}"
                    class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-green-700 px-5 py-2
                               text-sm font-medium text-white hover:bg-green-800">
                    <i class="ti ti-check"></i> Simpan Guru
                </button>
            </div>
        </form>
    </div>

@endsection
