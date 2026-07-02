@extends('layouts.app')
@section('title', 'Edit Guru')

@section('content')

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Edit Data Guru</h1>
        <p class="mt-2 text-sm text-gray-600">Perbarui data pribadi guru</p>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <form method="POST" action="{{ route('kurikulum.guru.update', $guru->id) }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="page" value="{{ $page ?? 1 }}">

            <div class="border-b border-gray-200 p-6">
                <p class="text-sm text-gray-600">Jenis Guru</p>
                <div class="mt-2">
                    @php
                        $roleItems = [];
                        if ($guru->guruMapelKelas->isNotEmpty()) {
                            $roleItems[] = ['label' => 'Guru Mapel', 'class' => 'bg-green-100 text-green-800'];
                        }
                        if ($guru->guruPikets->isNotEmpty()) {
                            $roleItems[] = ['label' => 'Guru Piket', 'class' => 'bg-amber-100 text-amber-800'];
                        }
                        if ($guru->guruBkKelas->isNotEmpty()) {
                            $roleItems[] = ['label' => 'Guru BK', 'class' => 'bg-blue-100 text-blue-800'];
                        }
                        if ($guru->kelasDiampu->isNotEmpty()) {
                            $roleItems[] = [
                                'label' => 'Wali Kelas ' . $guru->kelasDiampu->first()->nama_kelas,
                                'class' => 'bg-purple-100 text-purple-800',
                            ];
                        }
                    @endphp
                    <div class="border-b border-gray-200 p-6">
                        <p class="text-sm text-gray-500 mb-2">Peran Saat Ini</p>
                        @if ($roleItems)
                            <div class="flex flex-wrap gap-2">
                                @foreach ($roleItems as $role)
                                    <span
                                        class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $role['class'] }}">
                                        {{ $role['label'] }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-xs text-gray-400">Belum ada penugasan — atur di menu Penugasan Guru</span>
                        @endif
                        <p class="mt-2 text-xs text-gray-400">
                            Untuk mengubah penugasan, buka
                            <a href="{{ route('kurikulum.penugasan-guru.mapel.index') }}"
                                class="text-green-700 underline">halaman Penugasan Guru</a>
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-4">
                <div>
                    <label for="nip" class="block text-sm font-medium text-gray-700">NIP</label>
                    <input type="text" id="nip" name="nip"
                        value="{{ old('nip', strstr($guru->nip, '@', true) ?: $guru->nip) }}"
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                    @error('nip')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama', $guru->nama) }}"
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                    @error('nama')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="no_hp" class="block text-sm font-medium text-gray-700">Nomor HP</label>
                    <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp', $guru->no_hp) }}"
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                    @error('no_hp')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-between gap-3 border-t border-gray-200 p-6">
                <a href="{{ route('kurikulum.guru.index', ['page' => $page ?? 1]) }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali
                </a>
                <button type="submit"
                    class="rounded-lg bg-green-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-green-800">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

@endsection
