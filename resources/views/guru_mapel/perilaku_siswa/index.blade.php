@extends('layouts.app')

@section('title', 'Pencatatan Perilaku Siswa')

@section('content')
<div class="mx-auto max-w-7xl">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Perilaku Siswa</p>
            <h1 class="mt-1.5 text-2xl font-bold text-gray-900">Daftar Pencatatan</h1>
            <p class="mt-1 text-sm text-gray-500">Catat dan kelola perilaku siswa.</p>
        </div>
        <a href="{{ route('guru_mapel.perilaku-siswa.create') }}"
            class="inline-flex items-center gap-2 rounded-lg bg-green-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-800">
            <i class="ti ti-plus text-base"></i>
            Catat Perilaku
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($perilakuSiswas->isEmpty())
        <div class="overflow-hidden rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100">
                <i class="ti ti-alert-triangle text-2xl text-gray-400"></i>
            </div>
            <h2 class="text-base font-semibold text-gray-900">Belum ada pencatatan</h2>
            <p class="mt-1.5 text-sm text-gray-500">Mulai dengan mencatat perilaku siswa pertama.</p>
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-5 py-3.5 text-left">Tanggal</th>
                        <th class="px-5 py-3.5 text-left">Siswa</th>
                        <th class="px-5 py-3.5 text-left">Perilaku</th>
                        <th class="px-5 py-3.5 text-center">Jenis</th>
                        <th class="px-5 py-3.5 text-center">Poin</th>
                        <th class="px-5 py-3.5 text-center">Dicatat Oleh</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($perilakuSiswas as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-medium text-gray-900">{{ $item->siswa->nama ?? '-' }}</span>
                                <span class="block text-xs text-gray-500">{{ $item->siswa->kelas->nama_kelas ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-4">
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
                            <td class="px-5 py-4 text-center">
                                {{ $item->guru->nama ?? '-' }}
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('guru_mapel.perilaku-siswa.show', $item) }}"
                                        class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700">
                                        <i class="ti ti-eye text-base"></i>
                                    </a>
                                    <a href="{{ route('guru_mapel.perilaku-siswa.edit', $item) }}"
                                        class="rounded-lg p-2 text-blue-600 hover:bg-blue-50">
                                        <i class="ti ti-pencil text-base"></i>
                                    </a>
                                    <form method="POST" action="{{ route('guru_mapel.perilaku-siswa.destroy', $item) }}"
                                        onsubmit="return confirm('Hapus pencatatan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="rounded-lg p-2 text-rose-600 hover:bg-rose-50">
                                            <i class="ti ti-trash text-base"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $perilakuSiswas->links() }}
        </div>
    @endif
</div>
@endsection
