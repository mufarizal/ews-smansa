@extends('layouts.app')

@section('title', 'Edit Perilaku')

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Point Perilaku</p>
            <h1 class="mt-1.5 text-2xl font-bold text-gray-900">Edit Perilaku</h1>
            <p class="mt-1 text-sm text-gray-500">Ubah data perilaku beserta poinnya.</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-xl border border-gray-200 bg-white p-6">
            <form method="POST" action="{{ route('guru_bk.point-perilaku.update', $perilaku->id) }}">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label for="nama_perilaku" class="mb-1.5 block text-sm font-medium text-gray-700">Nama Perilaku</label>
                    <input type="text" name="nama_perilaku" id="nama_perilaku"
                        value="{{ old('nama_perilaku', $perilaku->nama_perilaku) }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500"
                        required>
                    @error('nama_perilaku')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="jenis" class="mb-1.5 block text-sm font-medium text-gray-700">Jenis</label>
                    <select name="jenis" id="jenis"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500"
                        required>
                        <option value="">-- Pilih Jenis --</option>
                        <option value="positif" {{ old('jenis', $perilaku->jenis) == 'positif' ? 'selected' : '' }}>Positif
                        </option>
                        <option value="negatif" {{ old('jenis', $perilaku->jenis) == 'negatif' ? 'selected' : '' }}>Negatif
                        </option>
                    </select>
                    @error('jenis')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="poin" class="mb-1.5 block text-sm font-medium text-gray-700">Poin</label>
                    <input type="number" name="poin" id="poin" value="{{ old('poin', $perilaku->poin) }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500"
                        required>
                    @error('poin')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="status_aktif" id="status_aktif" value="1"
                            {{ old('status_aktif', $perilaku->status_aktif) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                        <span class="text-sm text-gray-700">Aktif</span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('guru_bk.point-perilaku.index') }}"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        Kembali
                    </a>
                    <button type="submit"
                        class="rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-800">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
