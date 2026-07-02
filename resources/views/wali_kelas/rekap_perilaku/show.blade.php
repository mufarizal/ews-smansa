@extends('layouts.app')

@section('title', 'Detail Rekap Perilaku')

@section('content')
<div class="mx-auto max-w-5xl">
    <div class="mb-6">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Rekap Perkembangan</p>
        <h1 class="mt-1.5 text-2xl font-bold text-gray-900">Detail Siswa — {{ $siswa->nama }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ $siswa->kelas->nama_kelas ?? '-' }} • NIS: {{ $siswa->nis ?? '-' }}</p>
    </div>

    <div class="mb-6 rounded-xl border border-gray-200 bg-white">
        <div class="border-b border-gray-200">
            <nav class="flex space-x-4 px-4" aria-label="Tabs">
                <button type="button" onclick="showTab('perilaku')" id="tab-perilaku-btn"
                    class="border-b-2 border-green-900 py-3 text-sm font-medium text-green-900">Perilaku</button>
                <button type="button" onclick="showTab('absensi')" id="tab-absensi-btn"
                    class="border-b-2 border-transparent py-3 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">Absensi</button>
                <button type="button" onclick="showTab('akademik')" id="tab-akademik-btn"
                    class="border-b-2 border-transparent py-3 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">Nilai Akademik</button>
            </nav>
        </div>

        <div id="tab-perilaku" class="p-4">
            <div class="grid grid-cols-3 gap-4 mb-4">
                <div class="rounded-lg bg-emerald-50 p-3 text-center">
                    <p class="text-xs text-gray-500">Positif</p>
                    <p class="mt-1 text-xl font-bold text-emerald-700">{{ $riwayat->where('perilaku.jenis', 'positif')->count() }}</p>
                </div>
                <div class="rounded-lg bg-rose-50 p-3 text-center">
                    <p class="text-xs text-gray-500">Negatif</p>
                    <p class="mt-1 text-xl font-bold text-rose-700">{{ $riwayat->where('perilaku.jenis', 'negatif')->count() }}</p>
                </div>
                <div class="rounded-lg bg-gray-50 p-3 text-center">
                    <p class="text-xs text-gray-500">Total Poin Perilaku</p>
                    <p class="mt-1 text-xl font-bold text-gray-900">{{ $totalPoinPerilaku >= 0 ? '+' : '' }}{{ $totalPoinPerilaku }}</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-5 py-3.5 text-left">Tanggal</th>
                            <th class="px-5 py-3.5 text-left">Perilaku</th>
                            <th class="px-5 py-3.5 text-center">Jenis</th>
                            <th class="px-5 py-3.5 text-center">Poin</th>
                            <th class="px-5 py-3.5 text-left">Dicatat Oleh</th>
                            <th class="px-5 py-3.5 text-left">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($riwayat as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}
                                </td>
                                <td class="px-5 py-4 font-medium text-gray-900">
                                    {{ $item->perilaku->nama_perilaku ?? '-' }}
                                </td>
                                <td class="px-5 py-4 text-center">
                                    @if ($item->perilaku->jenis === 'positif')
                                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">
                                            Positif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-medium text-rose-800">
                                            Negatif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="font-mono {{ ($item->perilaku->poin ?? 0) > 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                        {{ ($item->perilaku->poin ?? 0) > 0 ? '+' : '' }}{{ $item->perilaku->poin ?? 0 }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    {{ $item->guru->nama ?? '-' }}
                                </td>
                                <td class="px-5 py-4 text-gray-600">
                                    {{ $item->catatan ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-sm text-gray-500">
                                    Belum ada riwayat perilaku untuk siswa ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div id="tab-absensi" class="hidden p-4">
            <div class="grid grid-cols-4 gap-4 text-center">
                <div class="rounded-lg bg-emerald-50 p-4">
                    <p class="text-xs text-gray-500">Hadir</p>
                    <p class="mt-1 text-xl font-bold text-emerald-700">{{ $hadir }}</p>
                </div>
                <div class="rounded-lg bg-gray-50 p-4">
                    <p class="text-xs text-gray-500">Izin</p>
                    <p class="mt-1 text-xl font-bold text-gray-700">{{ $izin }}</p>
                </div>
                <div class="rounded-lg bg-amber-50 p-4">
                    <p class="text-xs text-gray-500">Sakit</p>
                    <p class="mt-1 text-xl font-bold text-amber-700">{{ $sakit }}</p>
                </div>
                <div class="rounded-lg bg-rose-50 p-4">
                    <p class="text-xs text-gray-500">Alpha / Terlambat</p>
                    <p class="mt-1 text-xl font-bold text-rose-700">{{ $alpha }} α / {{ $terlambat }} T</p>
                </div>
            </div>
        </div>

        <div id="tab-akademik" class="hidden p-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-xs font-medium text-gray-500">Rata-rata Nilai Tugas</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">{{ $rataRataTugas }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-xs font-medium text-gray-500">Rata-rata Nilai Ujian</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">{{ $rataRataUjian }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('wali_kelas.rekap-perilaku.index') }}"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            <i class="ti ti-arrow-left text-base"></i>
            Kembali ke Rekap
        </a>
    </div>
</div>

<script>
function showTab(tab) {
    ['perilaku', 'absensi', 'akademik'].forEach(function(t) {
        document.getElementById('tab-' + t).classList.toggle('hidden', t !== tab);
        document.getElementById('tab-' + t + '-btn').classList.toggle('border-green-900', t === tab);
        document.getElementById('tab-' + t + '-btn').classList.toggle('text-green-900', t === tab);
        document.getElementById('tab-' + t + '-btn').classList.toggle('border-transparent', t !== tab);
        document.getElementById('tab-' + t + '-btn').classList.toggle('text-gray-500', t !== tab);
    });
}
</script>
@endsection
