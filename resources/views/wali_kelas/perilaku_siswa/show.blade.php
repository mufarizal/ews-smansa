@extends('layouts.app')

@section('title', 'Detail Pencatatan Perilaku')

@section('content')
<div class="mx-auto max-w-2xl">
    <div class="mb-6">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Perilaku Siswa</p>
        <h1 class="mt-1.5 text-2xl font-bold text-gray-900">Detail Pencatatan</h1>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6">
        <dl class="divide-y divide-gray-100">
            <div class="flex justify-between py-3">
                <dt class="text-sm font-medium text-gray-500">Tanggal</dt>
                <dd class="text-sm font-semibold text-gray-900">{{ \Carbon\Carbon::parse($perilakuSiswa->tanggal)->format('d/m/Y') }}</dd>
            </div>
            <div class="flex justify-between py-3">
                <dt class="text-sm font-medium text-gray-500">Siswa</dt>
                <dd class="text-sm font-semibold text-gray-900">{{ $perilakuSiswa->siswa->nama ?? '-' }}</dd>
            </div>
            <div class="flex justify-between py-3">
                <dt class="text-sm font-medium text-gray-500">Kelas</dt>
                <dd class="text-sm text-gray-900">{{ $perilakuSiswa->siswa->kelas->nama_kelas ?? '-' }}</dd>
            </div>
            <div class="flex justify-between py-3">
                <dt class="text-sm font-medium text-gray-500">Perilaku</dt>
                <dd class="text-sm font-semibold text-gray-900">{{ $perilakuSiswa->perilaku->nama_perilaku ?? '-' }}</dd>
            </div>
            <div class="flex justify-between py-3">
                <dt class="text-sm font-medium text-gray-500">Jenis</dt>
                <dd class="text-sm">
                    @if ($perilakuSiswa->perilaku->jenis === 'positif')
                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">
                            Positif
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-medium text-rose-800">
                            Negatif
                        </span>
                    @endif
                </dd>
            </div>
            <div class="flex justify-between py-3">
                <dt class="text-sm font-medium text-gray-500">Poin</dt>
                <dd class="text-sm font-mono {{ ($perilakuSiswa->perilaku->poin ?? 0) > 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                    {{ ($perilakuSiswa->perilaku->poin ?? 0) > 0 ? '+' : '' }}{{ $perilakuSiswa->perilaku->poin ?? 0 }}
                </dd>
            </div>
            <div class="flex justify-between py-3">
                <dt class="text-sm font-medium text-gray-500">Dicatat Oleh</dt>
                <dd class="text-sm text-gray-900">{{ $perilakuSiswa->guru->nama ?? '-' }}</dd>
            </div>
            <div class="flex justify-between py-3">
                <dt class="text-sm font-medium text-gray-500">Catatan</dt>
                <dd class="text-sm text-gray-900">{{ $perilakuSiswa->catatan ?? '-' }}</dd>
            </div>
        </dl>

        <div class="mt-6 flex items-center justify-end gap-3">
            <a href="{{ route('wali_kelas.perilaku-siswa.index') }}"
                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Kembali
            </a>
            <a href="{{ route('wali_kelas.perilaku-siswa.edit', $perilakuSiswa) }}"
                class="rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-800">
                Edit
            </a>
        </div>
    </div>
</div>
@endsection
