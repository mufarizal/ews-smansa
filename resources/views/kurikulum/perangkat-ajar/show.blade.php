@extends('layouts.app')
@section('title', 'Perangkat Ajar - ' . ($mapel->nama ?? 'Detail'))

@section('content')
    <div class="min-h-screen bg-slate-50/80 py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

            {{-- Breadcrumb --}}
            <a href="{{ route('kurikulum.perangkat-ajar.index') }}"
                class="mb-4 inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-green-700">
                <i class="ti ti-arrow-left text-base"></i> Kembali ke Perangkat Ajar
            </a>

            @php
                $totalBab = $kelasGroups->sum(fn($gmk) => $gmk->babs->count());
                $totalMateri = $kelasGroups->sum(fn($gmk) => $gmk->babs->sum(fn($b) => $b->materi->count()));
            @endphp

            {{-- Header --}}
            <div class="mb-6 rounded-xl border border-gray-200 bg-white p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-700">
                            <i class="ti ti-books text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-slate-900">{{ $mapel->nama ?? '-' }}</h1>
                            <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                <span
                                    class="inline-flex items-center gap-1 rounded-full bg-violet-50 px-2.5 py-1 text-xs font-medium text-violet-700">
                                    <i class="ti ti-user-pause text-xs"></i> {{ $guru->nama ?? '-' }}
                                </span>
                                <span
                                    class="inline-flex items-center gap-1 rounded-full bg-sky-50 px-2.5 py-1 text-xs font-medium text-sky-700">
                                    <i class="ti ti-list-tree text-xs"></i> {{ $kelasGroups->count() }} Kelas
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-2 text-center">
                        <div class="rounded-lg bg-gray-50 px-4 py-2">
                            <p class="text-lg font-bold text-gray-900">{{ $totalBab }}</p>
                            <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500">Bab</p>
                        </div>
                        <div class="rounded-lg bg-gray-50 px-4 py-2">
                            <p class="text-lg font-bold text-gray-900">{{ $totalMateri }}</p>
                            <p class="text-[11px] font-medium uppercase tracking-wide text-gray-500">Materi</p>
                        </div>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div
                    class="mb-5 flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    <i class="ti ti-circle-check text-base"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if ($kelasGroups->isEmpty())
                <div class="rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100">
                        <i class="ti ti-book-off text-2xl text-gray-400"></i>
                    </div>
                    <h2 class="text-base font-semibold text-gray-900">Belum ada bab untuk mapel ini</h2>
                    <p class="mt-1.5 text-sm text-gray-500">Bab yang sebelumnya ada mungkin sudah dihapus oleh Guru Mapel.
                    </p>
                </div>
            @else
                {{-- Quick filter + expand/collapse --}}
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                    <div class="relative w-full sm:w-72">
                        <i class="ti ti-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" id="quickFilter" oninput="filterPanels(this.value)"
                            placeholder="Cari kelas, bab, atau materi..."
                            class="w-full rounded-lg border border-gray-300 py-2 pl-9 pr-4 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-100">
                    </div>
                    <div class="flex gap-2">
                        <button type="button" onclick="setAllPanels(true)"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50">
                            <i class="ti ti-chevrons-down text-sm"></i> Buka Semua
                        </button>
                        <button type="button" onclick="setAllPanels(false)"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50">
                            <i class="ti ti-chevrons-up text-sm"></i> Tutup Semua
                        </button>
                    </div>
                </div>

                {{-- Grouped by kelas --}}
                <div class="space-y-3" id="kelasList">
                    @foreach ($kelasGroups as $gmk)
                        @php
                            $searchBlob = strtolower(
                                ($gmk->kelas->nama_kelas ?? '') .
                                    ' ' .
                                    $gmk->babs->pluck('nama_bab')->join(' ') .
                                    ' ' .
                                    $gmk->babs->flatMap(fn($b) => $b->materi->pluck('judul'))->join(' '),
                            );
                        @endphp
                        <div class="kelas-card overflow-hidden rounded-xl border border-gray-200 bg-white"
                            data-search="{{ $searchBlob }}">
                            <button type="button" onclick="toggleKelas(this)"
                                class="flex w-full items-center gap-4 px-4 py-4 text-left transition hover:bg-gray-50/70 sm:px-5">
                                <span
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-sky-50 text-sm font-bold text-sky-700">
                                    <i class="ti ti-list-tree text-base"></i>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-semibold text-gray-900">{{ $gmk->kelas->nama_kelas ?? '-' }}</p>
                                    @if ($gmk->semester)
                                        <p class="mt-0.5 text-xs text-gray-500">{{ $gmk->semester->nama }}</p>
                                    @endif
                                </div>
                                <span
                                    class="hidden shrink-0 items-center gap-1 rounded-full border border-gray-200 px-2.5 py-1 text-xs font-medium text-gray-600 sm:inline-flex">
                                    <i class="ti ti-book text-sm"></i> {{ $gmk->babs->count() }} Bab
                                </span>
                                <i
                                    class="ti ti-chevron-down chevron-icon shrink-0 text-lg text-gray-400 transition-transform duration-200"></i>
                            </button>

                            <div class="kelas-panel overflow-hidden transition-all duration-200 ease-in-out"
                                style="max-height: 0;">
                                <div class="space-y-2 border-t border-gray-100 bg-gray-50/60 px-4 py-3 sm:px-5">
                                    @foreach ($gmk->babs as $bab)
                                        <div class="bab-card overflow-hidden rounded-lg border border-gray-200 bg-white">
                                            <button type="button" onclick="toggleBab(this)"
                                                class="flex w-full items-center gap-3 px-3 py-3 text-left transition hover:bg-gray-50">
                                                <span
                                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-green-50 text-xs font-bold text-green-700">
                                                    {{ $bab->urutan }}
                                                </span>
                                                <div class="min-w-0 flex-1">
                                                    <p class="truncate text-sm font-semibold text-gray-900">
                                                        {{ $bab->nama_bab }}</p>
                                                    @if ($bab->deskripsi)
                                                        <p class="mt-0.5 truncate text-xs text-gray-500">
                                                            {{ Str::limit($bab->deskripsi, 90) }}</p>
                                                    @endif
                                                </div>
                                                <span
                                                    class="hidden shrink-0 items-center gap-1 rounded-full border border-gray-200 px-2 py-0.5 text-[11px] font-medium text-gray-600 sm:inline-flex">
                                                    <i class="ti ti-file-text text-xs"></i> {{ $bab->materi->count() }}
                                                    Materi
                                                </span>
                                                <i
                                                    class="ti ti-chevron-down chevron-icon shrink-0 text-base text-gray-400 transition-transform duration-200"></i>
                                            </button>

                                            <div class="bab-panel overflow-hidden transition-all duration-200 ease-in-out"
                                                style="max-height: 0;">
                                                <div class="border-t border-gray-100 px-3 py-2">
                                                    @if ($bab->materi->isEmpty())
                                                        <div class="flex items-center gap-2 py-2 text-sm text-gray-500">
                                                            <i class="ti ti-file-off text-base"></i> Belum ada materi untuk
                                                            bab ini.
                                                        </div>
                                                    @else
                                                        <ul class="divide-y divide-gray-100">
                                                            @foreach ($bab->materi as $materi)
                                                                <li class="flex items-start gap-3 py-2.5">
                                                                    <span
                                                                        class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-gray-50 text-[11px] font-semibold text-gray-500 ring-1 ring-gray-200">
                                                                        {{ $materi->urutan }}
                                                                    </span>
                                                                    <div class="min-w-0 flex-1">
                                                                        <p
                                                                            class="truncate text-sm font-medium text-gray-900">
                                                                            {{ $materi->judul }}</p>
                                                                        @if ($materi->isi_materi)
                                                                            <p class="mt-0.5 text-xs text-gray-500">
                                                                                {{ Str::limit(strip_tags($materi->isi_materi), 130) }}
                                                                            </p>
                                                                        @endif
                                                                    </div>
                                                                    @if ($materi->file_materi)
                                                                        <a href="{{ $materi->file_materi_url }}"
                                                                            target="_blank"
                                                                            class="inline-flex shrink-0 items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 hover:bg-blue-100">
                                                                            <i class="ti ti-file text-sm"></i> Lihat File
                                                                        </a>
                                                                    @else
                                                                        <span class="shrink-0 text-xs text-gray-400">Tanpa
                                                                            file</span>
                                                                    @endif
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <p id="noMatchNotice"
                    class="hidden rounded-xl border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500">
                    Tidak ada kelas, bab, atau materi yang cocok dengan pencarian.
                </p>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleKelas(btn) {
            const card = btn.closest('.kelas-card');
            const panel = card.querySelector(':scope > .kelas-panel');
            const chevron = btn.querySelector('.chevron-icon');
            const isOpen = panel.style.maxHeight && panel.style.maxHeight !== '0px';

            if (isOpen) {
                panel.style.maxHeight = '0';
                chevron.style.transform = 'rotate(0deg)';
            } else {
                panel.style.maxHeight = panel.scrollHeight + 'px';
                chevron.style.transform = 'rotate(180deg)';
                // pastikan parent (jika ada) ikut menyesuaikan tinggi
                growParents(panel);
            }
        }

        function toggleBab(btn) {
            const card = btn.closest('.bab-card');
            const panel = card.querySelector('.bab-panel');
            const chevron = btn.querySelector('.chevron-icon');
            const isOpen = panel.style.maxHeight && panel.style.maxHeight !== '0px';

            if (isOpen) {
                panel.style.maxHeight = '0';
                chevron.style.transform = 'rotate(0deg)';
            } else {
                panel.style.maxHeight = panel.scrollHeight + 'px';
                chevron.style.transform = 'rotate(180deg)';
            }
            growParents(panel);
        }

        function growParents(el) {
            let parent = el.closest('.kelas-panel');
            if (parent && parent.style.maxHeight && parent.style.maxHeight !== '0px') {
                parent.style.maxHeight = parent.scrollHeight + 'px';
            }
        }

        function setAllPanels(open) {
            document.querySelectorAll('.kelas-card').forEach((card) => {
                if (card.style.display === 'none') return;
                const panel = card.querySelector(':scope > .kelas-panel');
                const chevron = card.querySelector(':scope > button .chevron-icon');
                if (open) {
                    panel.style.maxHeight = panel.scrollHeight + 'px';
                    chevron.style.transform = 'rotate(180deg)';
                } else {
                    panel.style.maxHeight = '0';
                    chevron.style.transform = 'rotate(0deg)';
                }
            });

            document.querySelectorAll('.bab-card').forEach((card) => {
                const panel = card.querySelector('.bab-panel');
                const chevron = card.querySelector('.chevron-icon');
                if (open) {
                    panel.style.maxHeight = panel.scrollHeight + 'px';
                    chevron.style.transform = 'rotate(180deg)';
                } else {
                    panel.style.maxHeight = '0';
                    chevron.style.transform = 'rotate(0deg)';
                }
            });

            // beri waktu render lalu perbesar panel kelas agar muat semua bab yang baru dibuka
            if (open) {
                setTimeout(() => {
                    document.querySelectorAll('.kelas-panel').forEach((panel) => {
                        if (panel.style.maxHeight !== '0px') {
                            panel.style.maxHeight = panel.scrollHeight + 'px';
                        }
                    });
                }, 50);
            }
        }

        function filterPanels(value) {
            const term = value.trim().toLowerCase();
            const cards = document.querySelectorAll('.kelas-card');
            let visibleCount = 0;

            cards.forEach((card) => {
                const match = term === '' || card.dataset.search.includes(term);
                card.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });

            document.getElementById('noMatchNotice').classList.toggle('hidden', visibleCount !== 0);
        }
    </script>
@endpush
