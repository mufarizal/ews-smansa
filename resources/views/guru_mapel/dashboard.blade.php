@extends('layouts.app')

@section('title', 'Dashboard Guru Mapel')

@section('content')
    <div class="grid gap-6 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)]">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Guru Mapel</p>
            <h3 class="mt-2 text-2xl font-bold text-slate-900">Absensi kelas hari ini</h3>
            <p class="mt-2 max-w-xl text-sm text-slate-600">
                Buka jadwal yang sedang berjalan, lalu checklist siswa yang hadir. Sistem hanya membuka akses saat jam
                mengajar aktif dan sesuai kelas yang diajar.
            </p>

            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('guru_mapel.absensi.index') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                    Buka Absensi Mapel
                </a>
            </div>
        </div>

        <div
            class="rounded-2xl border border-slate-200 bg-gradient-to-br from-slate-900 to-slate-700 p-6 text-white shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-300">Alur singkat</p>
            <ol class="mt-4 space-y-3 text-sm text-slate-100">
                <li class="flex gap-3"><span
                        class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white/10 font-semibold">1</span><span>Pilih
                        jadwal yang sedang diajar.</span></li>
                <li class="flex gap-3"><span
                        class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white/10 font-semibold">2</span><span>Checklist
                        siswa yang hadir di kelas itu.</span></li>
                <li class="flex gap-3"><span
                        class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white/10 font-semibold">3</span><span>Simpan
                        hasil absensi langsung di jadwal tersebut.</span></li>
                <li class="flex gap-3"><span
                        class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white/10 font-semibold">4</span><span>Lihat
                        riwayat absensi dari menu guru piket bila perlu.</span></li>
            </ol>
        </div>
    </div>
@endsection
