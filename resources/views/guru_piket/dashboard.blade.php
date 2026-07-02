@extends('layouts.app')

@section('title', 'Dashboard Guru Piket')

@section('content')
    <div class="min-h-screen bg-slate-50/80 py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Dashboard Guru Piket</p>
                <h1 class="mt-2 text-3xl font-bold text-slate-900">Dashboard</h1>
            </div>

            <div class="space-y-6">
                <!-- A. Status Bertugas Hari Ini -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Status Bertugas Hari Ini</h2>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ \Carbon\Carbon::parse(now())->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</p>
                        </div>
                        @if ($isOnDutyToday)
                            <span
                                class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-4 py-2 text-sm font-semibold text-emerald-700">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                Sedang Bertugas
                            </span>
                        @else
                            <span
                                class="inline-flex items-center gap-2 rounded-full bg-rose-100 px-4 py-2 text-sm font-semibold text-rose-700">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10 7.293 11.293a1 1 0 001.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
                                </svg>
                                Tidak Bertugas
                            </span>
                        @endif
                    </div>
                </div>

                <!-- B. Jadwal Piket Saya -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-lg font-semibold text-slate-900">Jadwal Piket Saya</h2>

                    @if ($piketDays && count($piketDays) > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach ($piketDays as $hari)
                                <span
                                    class="inline-flex items-center rounded-full px-3 py-1.5 text-sm font-medium
                                    {{ $hari === $todayHari ? 'bg-orange-100 text-orange-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $hari }}

                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-500">Anda belum memiliki jadwal piket.</p>
                    @endif
                </div>

                <!-- C. Statistik Ringkas -->
                <div>
                    <h2 class="mb-4 text-lg font-semibold text-slate-900">Statistik Hari Ini</h2>

                    @if ($isOnDutyToday)
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                <p class="text-sm text-slate-500">Sesi QR Hari Ini</p>
                                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $sessionsToday }}</p>
                            </div>
                            <div class="rounded-2xl border border-emerald-200 bg-white p-5 shadow-sm">
                                <p class="text-sm text-slate-500">Siswa Hadir</p>
                                <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $hadirToday }}</p>
                            </div>
                            <div class="rounded-2xl border border-amber-200 bg-white p-5 shadow-sm">
                                <p class="text-sm text-slate-500">Siswa Terlambat</p>
                                <p class="mt-2 text-3xl font-bold text-amber-600">{{ $terlambatToday }}</p>
                            </div>
                        </div>
                    @else
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center">
                            <p class="text-slate-500">Anda tidak bertugas hari ini sehingga statistik tidak tersedia.</p>
                        </div>
                    @endif
                </div>

                <!-- Quick Actions -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-lg font-semibold text-slate-900">Menu Cepat</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <a href="{{ route('guru_piket.qr') }}"
                            class="flex items-center gap-4 rounded-xl border border-slate-200 p-4 transition hover:bg-slate-50
                                {{ $isOnDutyToday ? '' : 'opacity-60 pointer-events-none' }}">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-slate-900">Buat QR Absensi</p>
                                <p class="text-xs text-slate-500">Generate QR untuk absensi siswa</p>
                            </div>
                        </a>

                        <a href="{{ route('guru_piket.attendance.history') }}"
                            class="flex items-center gap-4 rounded-xl border border-slate-200 p-4 transition hover:bg-slate-50">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-100">
                                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-slate-900">Riwayat Absensi</p>
                                <p class="text-xs text-slate-500">Lihat history absensi</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
