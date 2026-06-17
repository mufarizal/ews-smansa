@extends('layouts.app')
@section('title', 'Edit Mata Pelajaran')

@section('content')

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Edit Mata Pelajaran</h1>
        <p class="mt-2 text-sm text-gray-600">Perbarui nama mata pelajaran</p>
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
        <form action="{{ route('kurikulum.mapel.update', $mapel->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="p-6 space-y-4">
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700">Nama Mata Pelajaran</label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama', $mapel->nama) }}"
                        class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100" />
                    @error('nama')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Buttons -->
            <div class="border-t border-gray-200 flex justify-between gap-3 p-6">
                <a href="{{ route('kurikulum.mapel.index') }}"
                    class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-green-700 rounded-lg hover:bg-green-800">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

@endsection
