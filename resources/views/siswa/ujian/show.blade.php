@extends('layouts.app')
@section('title', 'Kerjakan Ujian: ' . $ujianHarian->judul)

@section('content')
<div class="mx-auto max-w-4xl">
    <div class="mb-6">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Ujian Harian</p>
        <h1 class="mt-1.5 text-2xl font-bold text-gray-900">{{ $ujianHarian->judul }}</h1>
        <p class="mt-1 text-sm text-gray-500">
            {{ $ujianHarian->guruMapelKelas->mapel->nama ?? '-' }} — Durasi: {{ $ujianHarian->durasi_menit }} menit
        </p>
    </div>

    <form method="POST" action="{{ route('siswa.ujian.submit', $ujianHarian) }}" id="ujian-form">
        @csrf

        <div class="space-y-6">
            @foreach ($soals as $index => $soal)
                <article class="rounded-2xl border border-gray-200 bg-white p-6">
                    <p class="text-sm font-semibold text-gray-900 mb-3">Soal {{ $index + 1 }}</p>
                    <p class="text-gray-800 mb-4">{{ $soal->soal }}</p>

                    <div class="space-y-2">
                        <label class="flex items-center gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50 cursor-pointer">
                            <input type="radio" name="jawaban[{{ $index }}][jawaban]" value="a" required
                                class="h-4 w-4 border-gray-300 text-green-600 focus:ring-green-500">
                            <span class="text-sm"><span class="font-semibold text-emerald-600">A.</span> {{ $soal->opsi_a }}</span>
                        </label>
                        <label class="flex items-center gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50 cursor-pointer">
                            <input type="radio" name="jawaban[{{ $index }}][jawaban]" value="b" required
                                class="h-4 w-4 border-gray-300 text-green-600 focus:ring-green-500">
                            <span class="text-sm"><span class="font-semibold text-blue-600">B.</span> {{ $soal->opsi_b }}</span>
                        </label>
                        <label class="flex items-center gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50 cursor-pointer">
                            <input type="radio" name="jawaban[{{ $index }}][jawaban]" value="c" required
                                class="h-4 w-4 border-gray-300 text-green-600 focus:ring-green-500">
                            <span class="text-sm"><span class="font-semibold text-amber-600">C.</span> {{ $soal->opsi_c }}</span>
                        </label>
                        <label class="flex items-center gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50 cursor-pointer">
                            <input type="radio" name="jawaban[{{ $index }}][jawaban]" value="d" required
                                class="h-4 w-4 border-gray-300 text-green-600 focus:ring-green-500">
                            <span class="text-sm"><span class="font-semibold text-rose-600">D.</span> {{ $soal->opsi_d }}</span>
                        </label>
                        <input type="hidden" name="jawaban[{{ $index }}][soal_id]" value="{{ $soal->id }}">
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-lg bg-green-700 px-6 py-3 text-sm font-semibold text-white hover:bg-green-800">
                <i class="ti ti-send"></i>
                Kumpulkan Jawaban
            </button>
        </div>
    </form>
</div>
@endsection