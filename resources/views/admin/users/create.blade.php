@extends('layouts.app')
@section('title', 'Tambah User')

@section('content')

    {{-- Breadcrumb --}}
    <div class="mb-6">
        <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-600 hover:text-gray-900 transition">
            ← Kembali ke Manajemen User
        </a>
    </div>

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Tambah User Baru</h1>
        <p class="mt-2 text-gray-600">Isi data lengkap untuk mendaftarkan pengguna ke sistem</p>
    </div>

    <form method="POST" action="{{ route('admin.users.store') }}" id="create-form" class="max-w-3xl">
        @csrf

        {{-- Basic Information Section --}}
        <div class="mb-6 rounded-lg border border-gray-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">Informasi Dasar</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                {{-- Name --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        placeholder="Masukkan nama lengkap"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-800 placeholder-gray-400 outline-none focus:border-green-600 focus:ring-2 focus:ring-green-100 transition @error('name') border-red-500 focus:border-red-500 focus:ring-red-100 @enderror">
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="nama@email.com"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-800 placeholder-gray-400 outline-none focus:border-green-600 focus:ring-2 focus:ring-green-100 transition @error('email') border-red-500 focus:border-red-500 focus:ring-red-100 @enderror">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password" required placeholder="Minimal 8 karakter"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-800 placeholder-gray-400 outline-none focus:border-green-600 focus:ring-2 focus:ring-green-100 transition @error('password') border-red-500 focus:border-red-500 focus:ring-red-100 @enderror">
                    @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Role Section --}}
        <div class="mb-6 rounded-lg border border-gray-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">Role & Akses</h2>

            <label class="mb-3 block text-sm font-medium text-gray-700">
                Pilih Role <span class="text-red-500">*</span>
            </label>
            <p class="mb-4 text-xs text-gray-600">User dapat memiliki lebih dari satu role</p>

            @php
                $roleColors = [
                    'admin' => 'peer-checked:bg-amber-50 peer-checked:border-amber-300 peer-checked:text-amber-700',
                    'kurikulum' =>
                        'peer-checked:bg-indigo-50 peer-checked:border-indigo-300 peer-checked:text-indigo-700',
                    'guru_mapel' => 'peer-checked:bg-blue-50 peer-checked:border-blue-300 peer-checked:text-blue-700',
                    'wali_kelas' =>
                        'peer-checked:bg-emerald-50 peer-checked:border-emerald-300 peer-checked:text-emerald-700',
                    'guru_piket' =>
                        'peer-checked:bg-orange-50 peer-checked:border-orange-300 peer-checked:text-orange-700',
                    'siswa' => 'peer-checked:bg-pink-50 peer-checked:border-pink-300 peer-checked:text-pink-700',
                    'bk' => 'peer-checked:bg-red-50 peer-checked:border-red-300 peer-checked:text-red-700',
                    'kesiswaan' =>
                        'peer-checked:bg-purple-50 peer-checked:border-purple-300 peer-checked:text-purple-700',
                ];
            @endphp

            <div class="flex flex-wrap gap-2" id="role-checkboxes">
                @foreach ($roles as $role)
                    @php
                        $color =
                            $roleColors[$role->slug] ??
                            'peer-checked:bg-gray-100 peer-checked:border-gray-300 peer-checked:text-gray-700';
                    @endphp
                    <label class="relative cursor-pointer">
                        <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                            data-role-key="{{ $role->slug }}" data-role-label="{{ $role->name }}"
                            {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }} class="role-check peer sr-only"
                            onchange="syncDefaultOptions()">
                        <span
                            class="block rounded-full border border-gray-200 bg-white px-3.5 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-50 {{ $color }}">
                            {{ $role->name }}
                        </span>
                    </label>
                @endforeach
            </div>
            @error('roles')
                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Default Role Section --}}
        <div class="mb-8 rounded-lg border border-gray-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold text-gray-900">Role Default</h2>

            <label class="mb-2 block text-sm font-medium text-gray-700">
                Role Default saat Login <span class="text-red-500">*</span>
            </label>
            <p class="mb-3 text-xs text-gray-600">User akan login dengan role ini sebagai default</p>
            <select name="default_role" id="default-role-select" required
                class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-800 outline-none focus:border-green-600 focus:ring-2 focus:ring-green-100 transition @error('default_role') border-red-500 focus:border-red-500 focus:ring-red-100 @enderror">
                <option value="">— Pilih role terlebih dahulu —</option>
            </select>
            @error('default_role')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-wrap items-center gap-3">
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-lg bg-green-700 px-6 py-2.5 text-sm font-semibold text-white hover:bg-green-800 transition shadow-sm hover:shadow-md">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                Simpan User
            </button>
            <a href="{{ route('admin.users.index') }}"
                class="rounded-lg border border-gray-300 px-6 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                Batal
            </a>
        </div>
    </form>

    <script>
        function syncDefaultOptions() {
            const checked = [...document.querySelectorAll('.role-check:checked')];
            const select = document.getElementById('default-role-select');
            const current = select.value;

            select.innerHTML = checked.length ?
                checked.map(cb => `<option value="${cb.dataset.roleKey}">${cb.dataset.roleLabel}</option>`).join('') :
                '<option value="">— Pilih role dulu di atas —</option>';

            if (current && [...select.options].some(o => o.value === current)) {
                select.value = current;
            }
        }

        syncDefaultOptions();
        @if (old('default_role'))
            document.getElementById('default-role-select').value = "{{ old('default_role') }}";
        @endif
    </script>

@endsection
