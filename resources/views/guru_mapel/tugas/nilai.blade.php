@extends('layouts.app')
@section('title', 'Penilaian Tugas: ' . $tugas->judul)

@section('content')
<div class="mx-auto max-w-7xl">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Penilaian Tugas</p>
            <h1 class="mt-1.5 text-2xl font-bold text-gray-900">{{ $tugas->judul }}</h1>
            <p class="mt-1 text-sm text-gray-500">
                {{ $tugas->guruMapelKelas->mapel->nama ?? '-' }} — {{ $tugas->guruMapelKelas->kelas->nama_kelas ?? '-' }}
            </p>
        </div>
        <a href="{{ route('guru_mapel.tugas.show', $tugas) }}"
            class="inline-flex items-center gap-1.5 justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
            <i class="ti ti-arrow-left text-base"></i>
            Kembali ke Detail
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($nilaiTugas->isEmpty())
        <div class="overflow-hidden rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100">
                <i class="ti ti-users text-2xl text-gray-400"></i>
            </div>
            <h2 class="text-base font-semibold text-gray-900">Belum ada data nilai</h2>
            <p class="mt-1.5 text-sm text-gray-500">Tidak ada siswa yang terdaftar pada tugas ini.</p>
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-5 py-3.5 text-left">Nama Siswa</th>
                        <th class="px-5 py-3.5 text-left">NIS</th>
                        <th class="px-5 py-3.5 text-center">Nilai</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-left">Catatan</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($nilaiTugas as $nilai)
                        <tr class="hover:bg-gray-50" id="nilai-{{ $nilai->siswa_id }}">
                            <td class="px-5 py-4">
                                <p class="font-medium text-gray-900">{{ $nilai->siswa->nama ?? '-' }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-gray-600">{{ $nilai->siswa->nis ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if ($nilai->nilai !== null)
                                    <span class="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800">
                                        {{ $nilai->nilai }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                @php 
                                    $statusClass = match($nilai->status ?? 'mengerjakan') {
                                        'mengerjakan' => 'bg-amber-100 text-amber-700',
                                        'selesai' => 'bg-emerald-100 text-emerald-700',
                                        'tidak_mengerjakan' => 'bg-rose-100 text-rose-700',
                                        default => 'bg-slate-100 text-slate-600',
                                    };
                                @endphp
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusClass }}">
                                    {{ ucfirst(str_replace('_', ' ', $nilai->status ?? 'mengerjakan')) }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-xs text-gray-600">{{ $nilai->catatan ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <form method="POST" action="{{ route('guru_mapel.tugas.nilai.store', $tugas) }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="siswa_id" value="{{ $nilai->siswa_id }}">
                                    <input type="hidden" name="nilai" value="0">
                                    <input type="hidden" name="status" value="tidak_mengerjakan">
                                    <button type="button" 
                                        onclick="openNilaiModal({{ $nilai->siswa_id }}, '{{ $nilai->siswa->nama ?? '' }}', {{ $nilai->nilai ?? 'null' }}, '{{ $nilai->status ?? 'mengerjakan' }}', '{{ $nilai->catatan ?? '' }}')"
                                        class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-medium text-blue-700 hover:bg-blue-50">
                                        <i class="ti ti-star text-sm"></i>
                                        Nilai
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- Modal for grading --}}
@if ($nilaiTugas->isNotEmpty())
<div id="nilai-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4 backdrop-blur-sm">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
        <h3 class="text-lg font-bold text-slate-900">Input Nilai</h3>
        <p id="modal-siswa-name" class="mt-1 text-sm text-slate-600"></p>
        
        <form method="POST" action="{{ route('guru_mapel.tugas.nilai.store', $tugas) }}" class="mt-4 space-y-4">
            @csrf
            <input type="hidden" name="siswa_id" id="modal-siswa-id">
            
            <div>
                <label for="modal-nilai" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Nilai (0-100)
                </label>
                <input type="number" id="modal-nilai" name="nilai" min="0" max="100"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
            </div>
            
            <div>
                <label for="modal-status" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Status
                </label>
                <select id="modal-status" name="status"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                    <option value="mengerjakan">Mengerjakan</option>
                    <option value="selesai">Selesai</option>
                    <option value="tidak_mengerjakan">Tidak Mengerjakan</option>
                </select>
            </div>
            
            <div>
                <label for="modal-catatan" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Catatan
                </label>
                <textarea id="modal-catatan" name="catatan" rows="2"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100"></textarea>
            </div>
            
            <div class="mt-6 flex gap-3">
                <button type="button" id="modal-cancel"
                    class="flex-1 rounded-lg border border-gray-300 bg-white py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 rounded-lg bg-green-700 py-2.5 text-sm font-semibold text-white transition hover:bg-green-800">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('nilai-modal');
    const cancelBtn = document.getElementById('modal-cancel');
    
    window.openNilaiModal = function(siswaId, nama, nilai, status, catatan) {
        document.getElementById('modal-siswa-id').value = siswaId;
        document.getElementById('modal-siswa-name').textContent = nama;
        document.getElementById('modal-nilai').value = nilai ?? '';
        document.getElementById('modal-status').value = status;
        document.getElementById('modal-catatan').value = catatan ?? '';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };
    
    window.closeNilaiModal = function() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };
    
    cancelBtn.addEventListener('click', closeNilaiModal);
    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeNilaiModal();
    });
});
</script>
@endif
@endsection