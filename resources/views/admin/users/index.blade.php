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
                $allRoles = $users->flatMap(fn($u) => $u->roles->pluck('name'))->unique()->sort();

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
                        <p class="mt-1 text-xl font-bold text-gray-900">{{ $users->count() }}</p>
                    </div>
                    <div class="rounded-md bg-gray-100 px-2 py-1 text-base leading-none">👥</div>
                </div>
            </div>

            {{-- Role Cards --}}
            @foreach ($allRoles as $role)
                @php
                    $count = $users->filter(fn($u) => $u->roles->contains('name', $role))->count();
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
    </div>

    {{-- Search & Filter --}}
    <div class="mb-6 space-y-3">
        {{-- Search Input --}}
        <div class="relative">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none"
                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input id="search-input" type="text" placeholder="Cari nama atau email pengguna..."
                class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-800 placeholder-gray-400 outline-none focus:border-green-600 focus:ring-2 focus:ring-green-100 transition">
        </div>

        {{-- Filter Buttons --}}
        <div class="flex flex-wrap gap-2">
            <button onclick="filterRole('')"
                class="filter-btn active inline-flex items-center rounded-full border border-green-300 bg-green-50 px-3.5 py-1.5 text-xs font-medium text-green-700 transition"
                data-role="">
                ✓ Semua
            </button>
            @foreach ($allRoles as $role)
                <button onclick="filterRole('{{ $role }}')"
                    class="filter-btn inline-flex items-center rounded-full border border-gray-200 bg-white px-3.5 py-1.5 text-xs font-medium text-gray-600 hover:border-gray-300 hover:bg-gray-50 transition"
                    data-role="{{ $role }}">
                    {{ str_replace('_', ' ', $role) }}
                </button>
            @endforeach
        </div>
    </div>

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
                        <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-700">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="user-tbody">
                    @forelse ($users as $i => $user)
                        @php
                            $roleNames = $user->roles->pluck('name')->implode(',');
                        @endphp
                        <tr class="user-row hover:bg-gray-50 transition" data-name="{{ strtolower($user->name) }}"
                            data-email="{{ strtolower($user->email) }}" data-roles="{{ strtolower($roleNames) }}">
                            <td class="px-5 py-3.5 text-gray-400 text-xs font-mono">{{ $i + 1 }}</td>
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
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.users.edit', $user->id) }}"
                                        class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-600 hover:bg-green-50 hover:border-green-300 hover:text-green-700 transition"
                                        title="Edit">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        <span class="hidden sm:inline">Edit</span>
                                    </a>
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                        class="inline"
                                        onsubmit="return confirm('Yakin hapus user {{ addslashes($user->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 hover:border-red-300 transition"
                                            title="Hapus">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            <span class="hidden sm:inline">Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center">
                                <div class="text-5xl mb-3">📭</div>
                                <p class="text-gray-600 font-medium">Belum ada pengguna terdaftar</p>
                                <p class="text-sm text-gray-500 mt-1">Silahkan tambahkan user baru untuk memulai</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- No search results state --}}
            <div id="no-result" class="hidden px-5 py-12 text-center">
                <div class="text-5xl mb-3">🔍</div>
                <p class="text-gray-600 font-medium">Tidak ada pengguna yang ditemukan</p>
                <p class="text-sm text-gray-500 mt-1">Coba ubah pencarian atau filter Anda</p>
            </div>
        </div>
    </div>

    <script>
        let activeRole = '';

        function filterRole(role) {
            activeRole = role;

            document.querySelectorAll('.filter-btn').forEach(btn => {
                const isActive = btn.dataset.role === role;
                btn.classList.toggle('active', isActive);
                btn.classList.toggle('bg-green-50', isActive);
                btn.classList.toggle('border-green-300', isActive);
                btn.classList.toggle('text-green-700', isActive);
                btn.classList.toggle('bg-white', !isActive);
                btn.classList.toggle('border-gray-200', !isActive);
                btn.classList.toggle('text-gray-600', !isActive);
            });

            applyFilters();
        }

        document.getElementById('search-input').addEventListener('input', applyFilters);

        function applyFilters() {
            const query = document.getElementById('search-input').value.toLowerCase().trim();
            const rows = document.querySelectorAll('.user-row');
            let visible = 0;

            rows.forEach(row => {
                const matchSearch = !query ||
                    row.dataset.name.includes(query) ||
                    row.dataset.email.includes(query);

                const matchRole = !activeRole ||
                    row.dataset.roles.split(',').some(r => r.trim() === activeRole.toLowerCase());

                const show = matchSearch && matchRole;
                row.classList.toggle('hidden', !show);
                if (show) visible++;
            });

            document.getElementById('no-result').classList.toggle('hidden', visible > 0);
        }
    </script>

@endsection
