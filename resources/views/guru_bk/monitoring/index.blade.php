@extends('layouts.app')
@section('title', 'Monitoring SAW')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">

        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Guru BK</p>
                <h1 class="mt-1.5 text-2xl font-bold text-gray-900">Monitoring SAW</h1>
                <p class="mt-1 text-sm text-gray-500">Pilih kelas untuk melihat detail status siswa.</p>
            </div>
            @if ($semester)
                <div class="shrink-0">
                    <span class="rounded-full border border-pink-200 bg-pink-50 px-3 py-1 text-xs font-medium text-pink-700">
                        {{ $semester->nama }}
                    </span>
                </div>
            @endif
        </div>

        {{-- Grid Kelas --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($ringkasanPerKelas as $item)
                <a href="{{ route('guru_bk.monitoring.show', $item['kelas']->id) }}" class="block overflow-hidden rounded-xl border border-gray-200 bg-white hover:border-pink-300 hover:shadow-sm transition-all">
                    <div class="border-b border-gray-100 bg-gray-50 px-5 py-3.5">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-pink-100 text-pink-700 font-bold text-xs">
                                {{ strtoupper(substr($item['kelas']->nama_kelas ?? '?', 0, 2)) }}
                            </div>
                            <h3 class="font-semibold text-gray-900">{{ $item['kelas']->nama_kelas ?? '-' }}</h3>
                        </div>
                    </div>
                    <div class="px-5 py-4">
                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            <span class="rounded-full bg-rose-100 px-2 py-0.5 font-semibold text-rose-700">{{ $item['binaan'] }} Binaan</span>
                            <span class="rounded-full bg-amber-100 px-2 py-0.5 font-semibold text-amber-700">{{ $item['perhatian'] }} Perhatian</span>
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 font-semibold text-emerald-700">{{ $item['aman'] }} Aman</span>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">Total {{ $item['total'] }} siswa</p>
                    </div>
                </a>
            @empty
                <div class="sm:col-span-2 lg:col-span-3 rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center">
                    <p class="font-medium text-gray-600">Belum ada data kelas</p>
                    <p class="mt-1 text-sm text-gray-400">Belum ada penugasan kelas untuk Anda di semester ini.</p>
                </div>
            @endforelse
        </div>

    </div>
@endsection
