@extends('layouts.app')
@section('title', 'Edit Tugas')

@section('content')
<div class="mx-auto max-w-2xl">
    <div class="mb-6">
        <a href="{{ route('guru_mapel.tugas.show', $tugas) }}"
            class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800">
            <i class="ti ti-arrow-left text-base"></i>
            Kembali ke detail tugas
        </a>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Edit Tugas</h2>

        <form method="POST" action="{{ route('guru_mapel.tugas.update', $tugas) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="materi_id" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Materi <span class="text-red-500">*</span>
                </label>
                <select id="materi_id" name="materi_id" required
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                    <option value="">-- Pilih Materi --</option>
                    @foreach ($guruMapelKelas as $gmpk)
                        @foreach ($gmpk->babs as $bab)
                            @foreach ($bab->materi as $materi)
                                <option value="{{ $materi->id }}" {{ old('materi_id', $tugas->materi_id) == $materi->id ? 'selected' : '' }}>
                                    {{ $bab->nama_bab }}: {{ $materi->judul }}
                                </option>
                            @endforeach
                        @endforeach
                    @endforeach
                </select>
                @error('materi_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="jenis" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Jenis Tugas <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-4">
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" id="jenis_online" name="jenis" value="online" {{ (old('jenis', $tugas->jenis) == 'online' ? 'checked' : '') }} required
                            class="h-4 w-4 border-gray-300 text-green-600 focus:ring-green-500">
                        <span class="text-sm">Online (dengan soal)</span>
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" id="jenis_offline" name="jenis" value="offline" {{ (old('jenis', $tugas->jenis) == 'offline' || !$tugas->jenis ? 'checked' : '') }} required
                            class="h-4 w-4 border-gray-300 text-green-600 focus:ring-green-500">
                        <span class="text-sm">Offline (nilai manual)</span>
                    </label>
                </div>
                @error('jenis')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div id="link-meeting-group" class="{{ old('jenis', $tugas->jenis) == 'online' ? '' : 'hidden' }}">
                <label for="link_meeting" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Link Meeting (Google Meet/Zoom)
                </label>
                <input type="url" id="link_meeting" name="link_meeting" value="{{ old('link_meeting', $tugas->link_meeting) }}"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100"
                    placeholder="https://meet.google.com/xxx">
                @error('link_meeting')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="judul" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Judul Tugas <span class="text-red-500">*</span>
                </label>
                <input type="text" id="judul" name="judul" value="{{ old('judul', $tugas->judul) }}" required
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                @error('judul')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="deskripsi" class="mb-1.5 block text-sm font-medium text-gray-700">
                    Deskripsi
                </label>
                <textarea id="deskripsi" name="deskripsi" rows="4"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">{{ old('deskripsi', $tugas->deskripsi) }}</textarea>
                @error('deskripsi')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="tanggal_tugas" class="mb-1.5 block text-sm font-medium text-gray-700">
                        Tanggal Tugas <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="tanggal_tugas" name="tanggal_tugas" value="{{ old('tanggal_tugas', $tugas->tanggal_tugas) }}" required
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                    @error('tanggal_tugas')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="tanggal_deadline" class="mb-1.5 block text-sm font-medium text-gray-700">
                        Tanggal Deadline
                    </label>
                    <input type="date" id="tanggal_deadline" name="tanggal_deadline" value="{{ old('tanggal_deadline', $tugas->tanggal_deadline) }}"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                    @error('tanggal_deadline')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <a href="{{ route('guru_mapel.tugas.show', $tugas) }}"
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const jenisRadios = document.querySelectorAll('input[name="jenis"]');
    const linkMeetingGroup = document.getElementById('link-meeting-group');
    
    function toggleLinkMeeting() {
        const jenis = document.querySelector('input[name="jenis"]:checked')?.value;
        if (jenis === 'online') {
            linkMeetingGroup.classList.remove('hidden');
        } else {
            linkMeetingGroup.classList.add('hidden');
        }
    }

    jenisRadios.forEach(radio => radio.addEventListener('change', toggleLinkMeeting));
});
</script>
@endsection