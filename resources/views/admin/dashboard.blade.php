@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <div class="min-h-screen bg-slate-50/80 py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Dashboard Admin</p>
                <h1 class="mt-2 text-3xl font-bold text-slate-900">Ringkasan Pengguna</h1>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @php
                    $roleSlugs = [
                        'admin' => 'Admin',
                        'guru_bk' => 'Guru BK',
                        'guru_mapel' => 'Guru Mapel',
                        'wali_kelas' => 'Wali Kelas',
                        'guru_piket' => 'Guru Piket',
                        'kurikulum' => 'Kurikulum',
                        'siswa' => 'Siswa',
                    ];

                    $roleBgColors = [
                        'admin' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
                        'kurikulum' => 'bg-violet-50 border-violet-200 text-violet-800',
                        'guru_mapel' => 'bg-sky-50 border-sky-200 text-sky-800',
                        'wali_kelas' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
                        'guru_piket' => 'bg-orange-50 border-orange-200 text-orange-800',
                        'siswa' => 'bg-rose-50 border-rose-200 text-rose-800',
                        'guru_bk' => 'bg-red-50 border-red-200 text-red-800',
                    ];
                @endphp

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Total Semua Pengguna</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $totalUsers }}</p>
                </div>

                @foreach ($roleSlugs as $slug => $label)
                    @php
                        $count = $roleCounts[$slug]['count'] ?? 0;
                        $bgColor = $roleBgColors[$slug] ?? 'bg-gray-50 border-gray-200 text-gray-700';
                    @endphp
                    <div class="rounded-xl border {{ $bgColor }} p-5 shadow-sm transition hover:shadow-md">
                        <p class="text-sm font-medium opacity-80">{{ $label }}</p>
                        <p class="mt-2 text-3xl font-bold">{{ $count }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
