@extends('layouts.app')
@section('title', 'Edit Materi')

@section('content')
<div class="mx-auto max-w-2xl">
    <div class="mb-6">
        <a href="{{ route('guru_mapel.bab.show', $bab) }}"
            class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800">
            <i class="ti ti-arrow-left text-base"></i>
            Kembali ke bab
        </a>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Edit Materi</h2>

        <form method="POST" action="{{ route('guru_mapel.bab.materi.update', [$bab, $materi]) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="mb-4 rounded-lg bg-gray-50 p-4">
                <p class="text-xs text-gray-500">Bab: <span class="font-medium text-gray-900">{{ $bab->nama_bab }}</span></p>
                <p class="mt-1 text-xs text-gray-500">Mata Pelajaran: <span class="font-medium text-gray-900">{{ $bab->guruMapelKelas->mapel->nama ?? '-' }}</span></p>
                <p class="mt-1 text-xs text-gray-500">Kelas: <span class="font-medium text-gray-900">{{ $bab->guruMapelKelas->kelas->nama_kelas ?? '-' }}</span></p>
                @if ($materi->file_materi)
                    <p class="mt-2 text-xs text-gray-500">File saat ini: 
                        <a href="{{ $materi->file_materi_url }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                            <i class="ti ti-file text-xs"></i> Lihat File
                        </a>
                    </p>
                @endif
            </div>

            <div>
                <label for="judul" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Judul Materi <span class="text-red-500">*</span>
                </label>
                <input type="text" id="judul" name="judul" value="{{ old('judul', $materi->judul) }}" required
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                @error('judul')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="isi_materi" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Isi Materi
                </label>
                <textarea id="isi_materi" name="isi_materi" rows="5"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">{{ old('isi_materi', $materi->isi_materi) }}</textarea>
                @error('isi_materi')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="file_materi" class="mb-1.5 block text-sm font-medium text-gray-700">
                    File Materi (PDF, DOC, DOCX - maks 5MB)
                </label>
                <input type="file" id="file_materi" name="file_materi" accept=".pdf,.doc,.docx"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                @error('file_materi')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="urutan" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Urutan <span class="text-red-500">*</span>
                </label>
                <input type="number" id="urutan" name="urutan" value="{{ old('urutan', $materi->urutan) }}" min="1" required
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                @error('urutan')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <a href="{{ route('guru_mapel.bab.show', $bab) }}"
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