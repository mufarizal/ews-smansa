@extends('layouts.app')
@section('title', 'Detail Tugas: ' . $tugas->judul)

@section('content')
<div class="mx-auto max-w-7xl">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Detail Tugas</p>
            <h1 class="mt-1.5 text-2xl font-bold text-gray-900">{{ $tugas->judul }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $tugas->guruMapelKelas->mapel->nama ?? '-' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('siswa.tugas.index') }}"
                class="inline-flex items-center gap-1.5 justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
                <i class="ti ti-arrow-left text-base"></i>
                Kembali
            </a>
            @if (($tugas->jenis ?? 'offline') === 'online' && $tugas->soalTugas->count() > 0 && !($nilai && $nilai->status === 'selesai'))
                <a href="{{ route('siswa.tugas.kerjakan', $tugas) }}"
                    class="inline-flex items-center gap-1.5 justify-center rounded-lg bg-green-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-800">
                    <i class="ti ti-pencil text-base"></i>
                    Kerjakan
                </a>
            @endif
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,0.7fr)] lg:items-start">
        <section class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Detail Tugas</h2>
                
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Mata Pelajaran</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $tugas->guruMapelKelas->mapel->nama ?? '-' }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Guru</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $tugas->guruMapelKelas->guru->nama ?? '-' }}</p>
                    </div>
                </div>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Jenis Tugas</p>
                        <span class="mt-1 inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ ($tugas->jenis ?? 'offline') === 'online' ? 'bg-blue-100 text-blue-700' : 'bg-stone-100 text-stone-700' }}">
                            {{ ucfirst($tugas->jenis ?? 'Offline') }}
                        </span>
                    </div>
                    @if (($tugas->jenis ?? 'offline') === 'online' && $tugas->link_meeting)
                        <div class="rounded-xl bg-slate-50 px-4 py-3">
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Link Meeting</p>
                            <a href="{{ $tugas->link_meeting }}" target="_blank" class="mt-1 text-sm font-semibold text-blue-600 hover:underline break-all">
                                {{ $tugas->link_meeting }}
                            </a>
                        </div>
                    @endif
                </div>

                @if ($tugas->materi)
                    <div class="mt-4 rounded-xl bg-slate-50 px-4 py-3">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Materi</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $tugas->materi->judul ?? '-' }}</p>
                    </div>
                @endif

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Tanggal Diberikan</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ \Carbon\Carbon::parse($tugas->tanggal_tugas)->format('d F Y') }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Tanggal Deadline</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">
                            {{ $tugas->tanggal_deadline ? \Carbon\Carbon::parse($tugas->tanggal_deadline)->format('d F Y') : '-' }}
                        </p>
                    </div>
                </div>

                @if ($tugas->deskripsi)
                    <div class="mt-4 rounded-xl bg-slate-50 px-4 py-3">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Deskripsi</p>
                        <p class="mt-1 text-sm text-slate-900">{{ $tugas->deskripsi }}</p>
                    </div>
                @endif
            </div>
        </section>

        <aside class="space-y-4 lg:sticky lg:top-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 mb-3">Nilai Anda</h2>
                @if ($nilai && $nilai->nilai !== null)
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center h-20 w-20 rounded-full bg-emerald-100">
                            <span class="text-3xl font-bold text-emerald-700">{{ $nilai->nilai }}</span>
                        </div>
                        <p class="mt-2 text-sm text-slate-600">Nilai Tugas</p>
                        @if ($nilai->is_late)
                            <p class="mt-1 text-xs text-rose-600">Terlambat dikumpulkan</p>
                        @endif
                        @if ($nilai->catatan)
                            <p class="mt-3 text-xs text-slate-500">{{ $nilai->catatan }}</p>
                        @endif
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="ti ti-clock text-2xl text-amber-500"></i>
                        <p class="mt-2 text-sm text-slate-600">Nilai belum tersedia</p>
                    </div>
                @endif
            </div>
        </aside>
    </div>
</div>
@endsection