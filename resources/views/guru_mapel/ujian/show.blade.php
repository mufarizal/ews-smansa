@extends('layouts.app')
@section('title', 'Detail Ujian: ' . $ujianHarian->judul)

@section('content')
<div class="mx-auto max-w-7xl" x-data="{ tab: 'daftar' }">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Ujian Harian</p>
            <h1 class="mt-1.5 text-2xl font-bold text-gray-900">{{ $ujianHarian->judul }}</h1>
            <p class="mt-1 text-sm text-gray-500">
                {{ $ujianHarian->guruMapelKelas->mapel->nama ?? '-' }} — {{ $ujianHarian->guruMapelKelas->kelas->nama_kelas ?? '-' }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('guru_mapel.ujian.index') }}"
                class="inline-flex items-center gap-1.5 justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
                <i class="ti ti-arrow-left text-base"></i>
                Kembali
            </a>
            @if ($ujianHarian->status === 'draft')
                <a href="{{ route('guru_mapel.ujian.edit', $ujianHarian) }}"
                    class="inline-flex items-center gap-1.5 justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    <i class="ti ti-edit text-base"></i>
                    Edit
                </a>
            @endif
            <a href="{{ route('guru_mapel.ujian.hasil.index', $ujianHarian) }}"
                class="inline-flex items-center gap-1.5 justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                <i class="ti ti-chart-bar text-base"></i>
                Hasil
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,0.7fr)] lg:items-start">
        <section class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Informasi Ujian</h2>
                
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Mata Pelajaran</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $ujianHarian->guruMapelKelas->mapel->nama ?? '-' }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Kelas</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $ujianHarian->guruMapelKelas->kelas->nama_kelas ?? '-' }}</p>
                    </div>
                </div>

                @if ($ujianHarian->bab)
                    <div class="mt-4 rounded-xl bg-slate-50 px-4 py-3">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Bab</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">Bab {{ $ujianHarian->bab->urutan }}: {{ $ujianHarian->bab->nama_bab }}</p>
                    </div>
                @endif

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Tanggal Ujian</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ \Carbon\Carbon::parse($ujianHarian->tanggal_ujian)->format('d F Y') }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Durasi</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $ujianHarian->durasi_menit }} menit</p>
                    </div>
                </div>

                <div class="mt-4 rounded-xl bg-slate-50 px-4 py-3">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Status</p>
                    @if ($ujianHarian->status === 'draft')
                        <span class="mt-1 inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700">
                            Draft
                        </span>
                    @else
                        <span class="mt-1 inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                            Publish
                        </span>
                    @endif
                </div>
            </div>

            @if ($ujianHarian->status === 'draft')
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="border-b border-gray-200 mb-4">
                        <nav class="-mb-px flex space-x-6">
                            <button type="button" @click="tab = 'daftar'"
                                :class="tab === 'daftar' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap border-b-2 py-2 text-sm font-medium transition-colors">
                                Daftar Soal
                            </button>
                            <button type="button" @click="tab = 'tambah'"
                                :class="tab === 'tambah' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap border-b-2 py-2 text-sm font-medium transition-colors">
                                Tambah Soal
                            </button>
                        </nav>
                    </div>

                    <div x-show="tab === 'daftar'">
                        @if ($ujianHarian->soalUjians->isEmpty())
                            <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-8 text-center">
                                <i class="ti ti-file-text text-2xl text-gray-400 mb-2"></i>
                                <p class="text-sm text-gray-500">Belum ada soal. Tambahkan soal untuk ujian ini.</p>
                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach ($ujianHarian->soalUjians as $index => $soal)
                                    <article class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="flex-1">
                                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Soal {{ $index + 1 }}</p>
                                                <p class="mt-1 text-sm font-medium text-gray-900">{{ $soal->soal }}</p>
                                                <div class="mt-2 grid gap-1 text-xs">
                                                    <p><span class="text-emerald-600 font-semibold">A.</span> {{ $soal->opsi_a }}</p>
                                                    <p><span class="text-blue-600 font-semibold">B.</span> {{ $soal->opsi_b }}</p>
                                                    <p><span class="text-amber-600 font-semibold">C.</span> {{ $soal->opsi_c }}</p>
                                                    <p><span class="text-rose-600 font-semibold">D.</span> {{ $soal->opsi_d }}</p>
                                                </div>
                                                <p class="mt-2 text-xs">
                                                    <span class="text-gray-500">Jawaban benar:</span>
                                                    <span class="ml-1 font-bold text-gray-900">{{ strtoupper($soal->jawaban_benar) }}</span>
                                                    <span class="ml-2 text-gray-500">Bobot: {{ $soal->bobot }}</span>
                                                </p>
                                            </div>
                                            <div class="flex shrink-0 gap-2">
                                                <a href="{{ route('guru_mapel.ujian.soal.edit', [$ujianHarian, $soal]) }}"
                                                    class="inline-flex items-center justify-center h-7 w-7 rounded-md bg-white text-blue-600 hover:bg-blue-50">
                                                    <i class="ti ti-edit text-sm"></i>
                                                </a>
                                                <form method="POST" action="{{ route('guru_mapel.ujian.soal.destroy', [$ujianHarian, $soal]) }}"
                                                    onsubmit="return confirm('Hapus soal ini?')" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="inline-flex items-center justify-center h-7 w-7 rounded-md bg-white text-red-600 hover:bg-red-50">
                                                        <i class="ti ti-trash text-sm"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div x-show="tab === 'tambah'" x-cloak>
                        <form method="POST" action="{{ route('guru_mapel.ujian.soal.store', $ujianHarian) }}" class="space-y-4">
                            @csrf

                            <div>
                                <label for="soal" class="mb-1.5 block text-sm font-medium text-gray-700">
                                    Soal <span class="text-red-500">*</span>
                                </label>
                                <textarea id="soal" name="soal" rows="3" required
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">{{ old('soal') }}</textarea>
                                @error('soal')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label for="opsi_a" class="mb-1.5 block text-sm font-medium text-gray-700">
                                        Opsi A <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="opsi_a" name="opsi_a" value="{{ old('opsi_a') }}" required
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                                    @error('opsi_a')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="opsi_b" class="mb-1.5 block text-sm font-medium text-gray-700">
                                        Opsi B <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="opsi_b" name="opsi_b" value="{{ old('opsi_b') }}" required
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                                    @error('opsi_b')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="opsi_c" class="mb-1.5 block text-sm font-medium text-gray-700">
                                        Opsi C <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="opsi_c" name="opsi_c" value="{{ old('opsi_c') }}" required
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                                    @error('opsi_c')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="opsi_d" class="mb-1.5 block text-sm font-medium text-gray-700">
                                        Opsi D <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="opsi_d" name="opsi_d" value="{{ old('opsi_d') }}" required
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                                    @error('opsi_d')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label for="jawaban_benar" class="mb-1.5 block text-sm font-medium text-gray-700">
                                        Jawaban Benar <span class="text-red-500">*</span>
                                    </label>
                                    <select id="jawaban_benar" name="jawaban_benar" required
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                                        <option value="">-- Pilih Jawaban --</option>
                                        <option value="a" {{ old('jawaban_benar') == 'a' ? 'selected' : '' }}>A</option>
                                        <option value="b" {{ old('jawaban_benar') == 'b' ? 'selected' : '' }}>B</option>
                                        <option value="c" {{ old('jawaban_benar') == 'c' ? 'selected' : '' }}>C</option>
                                        <option value="d" {{ old('jawaban_benar') == 'd' ? 'selected' : '' }}>D</option>
                                    </select>
                                    @error('jawaban_benar')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="bobot" class="mb-1.5 block text-sm font-medium text-gray-700">
                                        Bobot <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" id="bobot" name="bobot" min="1" value="{{ old('bobot', 1) }}" required
                                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                                    @error('bobot')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="flex justify-end pt-2">
                                <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-lg bg-green-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-800">
                                    <i class="ti ti-plus text-base"></i>
                                    Tambah Soal
                                </button>
                            </div>
                        </form>
                    </div>

                    @if ($ujianHarian->soalUjians->count() > 0)
                        <div class="mt-6 pt-4 border-t border-gray-200" x-show="tab === 'daftar'">
                            <form method="POST" action="{{ route('guru_mapel.ujian.publish', $ujianHarian) }}"
                                onsubmit="return confirm('Publish ujian ini? Pastikan semua soal sudah benar.')" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                                    <i class="ti ti-send text-base"></i>
                                    Publish Ujian
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @elseif ($ujianHarian->soalUjians->isNotEmpty())
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">Daftar Soal</h2>
                    <div class="space-y-4">
                        @foreach ($ujianHarian->soalUjians as $index => $soal)
                            <article class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                                <div class="flex items-start gap-3">
                                    <div class="flex-1">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Soal {{ $index + 1 }}</p>
                                        <p class="mt-1 text-sm font-medium text-gray-900">{{ $soal->soal }}</p>
                                        <div class="mt-2 grid gap-1 text-xs">
                                            <p><span class="text-emerald-600 font-semibold">A.</span> {{ $soal->opsi_a }}</p>
                                            <p><span class="text-blue-600 font-semibold">B.</span> {{ $soal->opsi_b }}</p>
                                            <p><span class="text-amber-600 font-semibold">C.</span> {{ $soal->opsi_c }}</p>
                                            <p><span class="text-rose-600 font-semibold">D.</span> {{ $soal->opsi_d }}</p>
                                        </div>
                                        <p class="mt-2 text-xs">
                                            <span class="text-gray-500">Jawaban:</span>
                                            <span class="ml-1 font-bold text-gray-900">{{ strtoupper($soal->jawaban_benar) }}</span>
                                        </p>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>

        <aside class="space-y-4 lg:sticky lg:top-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 mb-3">Ringkasan</h2>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Jumlah Soal</span>
                        <span class="font-semibold text-slate-900">{{ $ujianHarian->soalUjians->count() }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Total Bobot</span>
                        <span class="font-semibold text-slate-900">{{ $ujianHarian->soalUjians->sum('bobot') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Status</span>
                        @if ($ujianHarian->status === 'draft')
                            <span class="font-semibold text-amber-700">Draft</span>
                        @else
                            <span class="font-semibold text-emerald-700">Publish</span>
                        @endif
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection