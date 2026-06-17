@extends('layouts.app')
@section('title', 'Manajemen User')

@section('content')

    {{-- Flash message --}}
    @if (session('success'))
        <div
            class="mb-6 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            <svg class="h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <svg class="h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                    clip-rule="evenodd" />
            </svg>
            {{ session('error') }}
        </div>
    @endif

    @if (session('info'))
        <div
            class="mb-6 flex items-center gap-3 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
            <svg class="h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                    clip-rule="evenodd" />
            </svg>
            {{ session('info') }}
        </div>
    @endif

    {{-- Header Section --}}
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manajemen User</h1>
            <p class="mt-2 text-gray-600">Kelola akun pengguna dan role dalam sistem</p>
        </div>
        <a href="{{ route('admin.users.create') }}"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-green-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-800 transition">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah User
        </a>
    </div>

    {{-- Role Statistics Section --}}
    <div class="mb-6">
        <h2 class="mb-4 text-sm font-semibold text-gray-700 uppercase tracking-wide">Statistik Pengguna</h2>
<div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @php
                    $allRoles = $roles->pluck('name')->unique()->sort();

                    $roleBgColors = [
                        'admin' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
                        'kurikulum' => 'bg-violet-50 border-violet-200 text-violet-800',
                        'guru_mapel' => 'bg-sky-50 border-sky-200 text-sky-800',
                        'wali_kelas' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
                        'guru_piket' => 'bg-orange-50 border-orange-200 text-orange-800',
                        'siswa' => 'bg-rose-50 border-rose-200 text-rose-800',
                        'bk' => 'bg-red-50 border-red-200 text-red-800',
                        'kesiswaan' => 'bg-purple-50 border-purple-200 text-purple-800',
                    ];
                @endphp

                {{-- Total Users Card --}}
                <div class="rounded-lg border border-gray-200 bg-white p-3.5 hover:shadow-sm transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total User</p>
                            <p class="mt-1 text-xl font-bold text-gray-900">{{ $users->total() }}</p>
                        </div>
                        <i></i>
                    </div>
                </div>

                {{-- Role Cards --}}
                @foreach ($allRoles as $role)
                    @php
                        $count = $roleCounts[$role] ?? 0;
                        $bgColor = $roleBgColors[$role] ?? 'bg-gray-50 border-gray-200 text-gray-700';
                    @endphp
                    <div class="rounded-lg border {{ $bgColor }} p-3.5 hover:shadow-sm transition">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wide">{{ str_replace('_', ' ', $role) }}</p>
                            <p class="mt-1 text-xl font-bold text-gray-900">{{ $count }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

    {{-- Search & Filter --}}
    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-6 grid gap-3 md:grid-cols-3">
        <div class="relative md:col-span-2">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none"
                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau email pengguna..."
                class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-800 placeholder-gray-400 outline-none focus:border-green-600 focus:ring-2 focus:ring-green-100 transition">
        </div>
        <div>
            <select name="role_id"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700">
                <option value="">Semua Role</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" {{ (int) $selectedRoleId === (int) $role->id ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2 md:col-span-3">
            <button type="submit"
                class="inline-flex items-center justify-center rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-800 transition">
                Terapkan Filter
            </button>
            <a href="{{ route('admin.users.index') }}"
                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                Reset
            </a>
        </div>
    </form>

    {{-- Users Table --}}
    <div class="rounded-lg border border-gray-200 bg-white overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">#</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">Nama</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700 hidden sm:table-cell">Email
                        </th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">Role</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">Penugasan Guru</th>
                        <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-700">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="user-tbody">
                    @forelse ($users as $i => $user)
                        @php
                            $displayIndex = ($users->currentPage() - 1) * $users->perPage() + $loop->iteration;
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3.5 text-gray-400 text-xs font-mono">{{ $displayIndex }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-green-100 text-xs font-bold text-green-700">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-500 sm:hidden">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-gray-600 hidden sm:table-cell text-xs">{{ $user->email }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse ($user->roles as $role)
                                        @php
                                            $roleTextColors = [
                                                'admin' => 'text-amber-700',
                                                'guru_mapel' => 'text-sky-700',
                                                'wali_kelas' => 'text-emerald-700',
                                                'bk' => 'text-red-700',
                                                'kesiswaan' => 'text-purple-700',
                                                'siswa' => 'text-rose-700',
                                                'kurikulum' => 'text-violet-700',
                                                'guru_piket' => 'text-orange-700',
                                            ];
                                            $roleBgText = $roleTextColors[$role->name] ?? 'text-gray-700';
                                        @endphp
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full border border-gray-200 bg-white px-2.5 py-1 text-xs font-semibold {{ $roleBgText }}">
                                            {{ str_replace('_', ' ', $role->name) }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-gray-400 italic">belum ada role</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                @if ($user->guru)
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        @if ($user->guru->guruMapelKelas->isNotEmpty())
                                            <span class="inline-flex items-center gap-1 rounded-full bg-sky-50 px-2 py-0.5 text-xs font-medium text-sky-800" title="Guru Mapel">
                                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                                Mapel
                                            </span>
                                        @endif
                                        @if ($user->guru->kelasDiampu->isNotEmpty())
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-800" title="Wali Kelas">
                                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                                Walas
                                            </span>
                                        @endif
                                        @if ($user->guru->guruBkKelas->isNotEmpty())
                                            <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-800" title="Guru BK">
                                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                                BK
                                            </span>
                                        @endif
                                        @if ($user->guru->guruPikets->isNotEmpty())
                                            <span class="inline-flex items-center gap-1 rounded-full bg-orange-50 px-2 py-0.5 text-xs font-medium text-orange-800" title="Guru Piket">
                                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                                Piket
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 italic">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.users.edit', $user->id) }}?page={{ $users->currentPage() }}"
                                        class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-600 hover:bg-green-50 hover:border-green-300 hover:text-green-700 transition"
                                        title="Edit">
                                        <i class="ti ti-pencil"></i>
                                        <span class="hidden sm:inline">Edit</span>
                                    </a>
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                        class="inline"
                                        onsubmit="return confirm('Yakin hapus user {{ addslashes($user->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="page" value="{{ $users->currentPage() }}">
                                        <button type="submit"
                                            class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 hover:border-red-300 transition"
                                            title="Hapus">
                                            <i class="ti ti-trash"></i>
                                            <span class="hidden sm:inline">Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center">
                                    <div class="text-5xl mb-3">📭</div>
                                    <p class="text-gray-600 font-medium">Belum ada pengguna terdaftar</p>
                                    <p class="text-sm text-gray-500 mt-1">Silahkan tambahkan user baru untuk memulai</p>
                                </td>
                            </tr>
                        @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if ($users->hasPages())
        <div class="flex flex-col items-center justify-between gap-3 border-t border-gray-200 bg-gray-50 px-5 py-3.5 sm:flex-row">
            <p class="text-xs text-gray-500">
                Menampilkan
                <span class="font-semibold text-gray-700">{{ $users->firstItem() }}–{{ $users->lastItem() }}</span>
                dari
                <span class="font-semibold text-gray-700">{{ $users->total() }}</span>
                user
            </p>

            <div class="flex items-center gap-1">
                {{-- Prev --}}
                @if ($users->onFirstPage())
                    <span class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-300 cursor-default select-none">
                        ‹ Prev
                    </span>
                @else
                    <a href="{{ $users->previousPageUrl() }}"
                        class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 transition">
                        ‹ Prev
                    </a>
                @endif

                {{-- Page Numbers --}}
                @php
                    $currentPage = $users->currentPage();
                    $lastPage = $users->lastPage();
                    $start = max(1, $currentPage - 2);
                    $end = min($lastPage, $currentPage + 2);
                @endphp

                @if ($start > 1)
                    <a href="{{ $users->url(1) }}"
                        class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 transition">
                        1
                    </a>
                    @if ($start > 2)
                        <span class="px-1 text-xs text-gray-400">…</span>
                    @endif
                @endif

                @foreach ($users->getUrlRange($start, $end) as $page => $url)
                    @if ($page === $currentPage)
                        <span
                            class="rounded-md border border-green-600 bg-green-700 px-3 py-1.5 text-xs font-semibold text-white select-none">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}"
                            class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 transition">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach

                @if ($end < $lastPage)
                    @if ($end < $lastPage - 1)
                        <span class="px-1 text-xs text-gray-400">…</span>
                    @endif
                    <a href="{{ $users->url($lastPage) }}"
                        class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 transition">
                        {{ $lastPage }}
                    </a>
                @endif

                {{-- Next --}}
                @if ($users->hasMorePages())
                    <a href="{{ $users->nextPageUrl() }}"
                        class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 transition">
                        Next ›
                    </a>
                @else
                    <span
                        class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-300 cursor-default select-none">
                        Next ›
                    </span>
                @endif
            </div>
        </div>
    @else
        {{-- Tetap tampilkan info total meski hanya 1 halaman --}}
        @if ($users->total() > 0)
            <div class="border-t border-gray-200 bg-gray-50 px-5 py-3">
                <p class="text-xs text-gray-500">
                    Menampilkan semua
                    <span class="font-semibold text-gray-700">{{ $users->total() }}</span>
                    user
                </p>
            </div>
        @endif
    @endif

@endsection
