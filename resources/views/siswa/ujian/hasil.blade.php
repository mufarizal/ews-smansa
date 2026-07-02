@extends('layouts.app')
@section('title', 'Hasil Ujian: ' . $ujianHarian->judul)

@section('content')
<div class="mx-auto max-w-4xl">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Hasil Ujian</p>
            <h1 class="mt-1.5 text-2xl font-bold text-gray-900">{{ $ujianHarian->judul }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $ujianHarian->guruMapelKelas->mapel->nama ?? '-' }}</p>
        </div>
        <a href="{{ route('siswa.ujian.index') }}"
            class="inline-flex items-center gap-1.5 justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
            <i class="ti ti-arrow-left text-base"></i>
            Kembali
        </a>
    </div>

    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">Nilai Anda</p>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-5xl font-bold text-emerald-700">{{ $hasil->nilai }}</span>
                    <span class="text-lg text-emerald-600">/ 100</span>
                </div>
            </div>
            <div class="text-right">
                <p class="text-xs text-emerald-600">Jawaban Benar</p>
                <p class="text-2xl font-bold text-emerald-700">{{ $hasil->jumlah_benar }}</p>
                <p class="text-xs text-emerald-600">Jawaban Salah</p>
                <p class="text-2xl font-bold text-rose-700">{{ $hasil->jumlah_salah }}</p>
            </div>
        </div>
    </div>

    @if ($jawabanSiswa->isNotEmpty())
        <div class="space-y-4">
            @foreach ($jawabanSiswa as $jawaban)
                @php 
                    $soal = $jawaban->soalUjian;
                    $isBenar = $jawaban->is_benar;
                @endphp
                <article class="rounded-2xl border {{ $isBenar ? 'border-emerald-200 bg-emerald-50' : 'border-rose-200 bg-rose-50' }} p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1">
                            <p class="text-sm font-semibold {{ $isBenar ? 'text-emerald-800' : 'text-rose-800' }}">
                                {{ $soal->soal }}
                            </p>
                            <div class="mt-3 grid gap-1 text-xs">
                                <p class="{{ $soal->jawaban_benar === 'a' ? ($isBenar ? 'text-emerald-700 font-semibold' : 'text-gray-600') : 'text-gray-600' }}">
                                    <span class="font-semibold">A.</span> {{ $soal->opsi_a }}
                                    @if ($soal->jawaban_benar === 'a') <i class="ti ti-check ml-1"></i> @endif
                                </p>
                                <p class="{{ $soal->jawaban_benar === 'b' ? ($isBenar ? 'text-emerald-700 font-semibold' : 'text-gray-600') : 'text-gray-600' }}">
                                    <span class="font-semibold">B.</span> {{ $soal->opsi_b }}
                                    @if ($soal->jawaban_benar === 'b') <i class="ti ti-check ml-1"></i> @endif
                                </p>
                                <p class="{{ $soal->jawaban_benar === 'c' ? ($isBenar ? 'text-emerald-700 font-semibold' : 'text-gray-600') : 'text-gray-600' }}">
                                    <span class="font-semibold">C.</span> {{ $soal->opsi_c }}
                                    @if ($soal->jawaban_benar === 'c') <i class="ti ti-check ml-1"></i> @endif
                                </p>
                                <p class="{{ $soal->jawaban_benar === 'd' ? ($isBenar ? 'text-emerald-700 font-semibold' : 'text-gray-600') : 'text-gray-600' }}">
                                    <span class="font-semibold">D.</span> {{ $soal->opsi_d }}
                                    @if ($soal->jawaban_benar === 'd') <i class="ti ti-check ml-1"></i> @endif
                                </p>
                            </div>
                            <p class="mt-2 text-xs">
                                Jawaban Anda: <span class="font-semibold">{{ strtoupper($jawaban->jawaban) }}</span>
                                <span class="ml-2 {{ $isBenar ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $isBenar ? '(Benar)' : '(Salah)' }}
                                </span>
                            </p>
                        </div>
                        <div class="shrink-0">
                            @if ($isBenar)
                                <i class="ti ti-circle-check text-2xl text-emerald-600"></i>
                            @else
                                <i class="ti ti-circle-x text-2xl text-rose-600"></i>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection