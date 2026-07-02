@extends('layouts.app')
@section('title', 'Edit Soal Ujian')

@section('content')
<div class="mx-auto max-w-2xl">
    <div class="mb-6">
        <a href="{{ route('guru_mapel.ujian.show', $ujianHarian) }}"
            class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800">
            <i class="ti ti-arrow-left text-base"></i>
            Kembali ke detail ujian
        </a>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Edit Soal</h2>

        <form method="POST" action="{{ route('guru_mapel.ujian.soal.update', [$ujianHarian, $soal]) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="soal" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Soal <span class="text-red-500">*</span>
                </label>
                <textarea id="soal" name="soal" rows="3" required
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">{{ old('soal', $soal->soal) }}</textarea>
                @error('soal')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label for="opsi_a" class="mb-1.5 block text-sm font-medium text-gray-700">
                        Opsi A <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="opsi_a" name="opsi_a" value="{{ old('opsi_a', $soal->opsi_a) }}" required
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                    @error('opsi_a')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="opsi_b" class="mb-1.5 block text-sm font-medium text-gray-700">
                        Opsi B <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="opsi_b" name="opsi_b" value="{{ old('opsi_b', $soal->opsi_b) }}" required
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                    @error('opsi_b')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="opsi_c" class="mb-1.5 block text-sm font-medium text-gray-700">
                        Opsi C <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="opsi_c" name="opsi_c" value="{{ old('opsi_c', $soal->opsi_c) }}" required
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                    @error('opsi_c')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="opsi_d" class="mb-1.5 block text-sm font-medium text-gray-700">
                        Opsi D <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="opsi_d" name="opsi_d" value="{{ old('opsi_d', $soal->opsi_d) }}" required
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                    @error('opsi_d')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label for="jawaban_benar" class="mb-1.5 block text-sm font-medium text-gray-700">
                        Jawaban Benar <span class="text-red-500">*</span>
                    </label>
                    <select id="jawaban_benar" name="jawaban_benar" required
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                        <option value="">-- Pilih Jawaban --</option>
                        <option value="a" {{ old('jawaban_benar', $soal->jawaban_benar) == 'a' ? 'selected' : '' }}>A</option>
                        <option value="b" {{ old('jawaban_benar', $soal->jawaban_benar) == 'b' ? 'selected' : '' }}>B</option>
                        <option value="c" {{ old('jawaban_benar', $soal->jawaban_benar) == 'c' ? 'selected' : '' }}>C</option>
                        <option value="d" {{ old('jawaban_benar', $soal->jawaban_benar) == 'd' ? 'selected' : '' }}>D</option>
                    </select>
                    @error('jawaban_benar')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="bobot" class="mb-1.5 block text-sm font-medium text-gray-700">
                        Bobot <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="bobot" name="bobot" min="1" value="{{ old('bobot', $soal->bobot) }}" required
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                    @error('bobot')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <a href="{{ route('guru_mapel.ujian.show', $ujianHarian) }}"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-green-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-800">
                    <i class="ti ti-save text-base"></i>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection