@extends('layouts.app')

@section('title', 'Edit Pencatatan Perilaku')

@section('content')
<div class="mx-auto max-w-2xl">
    <div class="mb-6">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Perilaku Siswa</p>
        <h1 class="mt-1.5 text-2xl font-bold text-gray-900">Edit Pencatatan Perilaku</h1>
        <p class="mt-1 text-sm text-gray-500">Ubah data pencatatan yang sudah ada.</p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6">
        <form method="POST" action="{{ route('wali_kelas.perilaku-siswa.update', $perilakuSiswa) }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="siswa_id" class="mb-1.5 block text-sm font-medium text-gray-700">Siswa</label>
                <select name="siswa_id" id="siswa_id"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500"
                    required>
                    <option value="">-- Pilih Siswa --</option>
                    @foreach ($siswaList as $siswa)
                        <option value="{{ $siswa->id }}" {{ old('siswa_id', $perilakuSiswa->siswa_id) == $siswa->id ? 'selected' : '' }}>
                            {{ $siswa->nama }} ({{ $siswa->kelas->nama_kelas ?? '-' }})
                        </option>
                    @endforeach
                </select>
                @error('siswa_id')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="perilaku_id" class="mb-1.5 block text-sm font-medium text-gray-700">Perilaku</label>
                <select name="perilaku_id" id="perilaku_id"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500"
                    required>
                    <option value="">-- Pilih Perilaku --</option>
                    @foreach ($perilakuList as $perilaku)
                        <option value="{{ $perilaku->id }}" {{ old('perilaku_id', $perilakuSiswa->perilaku_id) == $perilaku->id ? 'selected' : '' }}>
                            {{ $perilaku->nama_perilaku }} ({{ $perilaku->jenis }}, {{ $perilaku->poin > 0 ? '+' : '' }}{{ $perilaku->poin }} poin)
                        </option>
                    @endforeach
                </select>
                @error('perilaku_id')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="tanggal" class="mb-1.5 block text-sm font-medium text-gray-700">Tanggal</label>
                <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', $perilakuSiswa->tanggal->format('Y-m-d')) }}"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500"
                    required>
                @error('tanggal')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="catatan" class="mb-1.5 block text-sm font-medium text-gray-700">Catatan (Opsional)</label>
                <textarea name="catatan" id="catatan" rows="3"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500">{{ old('catatan', $perilakuSiswa->catatan) }}</textarea>
                @error('catatan')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('wali_kelas.perilaku-siswa.index') }}"
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
