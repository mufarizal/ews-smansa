<?php $__env->startSection('title', 'Kelas Saya - Monitoring Siswa'); ?>

<?php $__env->startSection('content'); ?>
    <div x-data="{ activeModal: null }" class="relative">
        <?php if(session('success')): ?>
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Data Kelas Saya</h1>
                <p class="mt-2 text-gray-600">Pantau perkembangan akademik, absensi, dan perilaku siswa</p>
            </div>
            <?php if($kelas): ?>
                <div class="flex items-center gap-3">
                    <a href="?tab=ringkasan"
                        class="px-4 py-2 text-sm font-medium rounded-lg transition <?php echo e($activeTab === 'ringkasan' ? 'bg-green-600 text-white' : 'bg-white text-gray-700 border border-gray-300'); ?>">Ringkasan</a>
                    <a href="?tab=siswa"
                        class="px-4 py-2 text-sm font-medium rounded-lg transition <?php echo e($activeTab === 'siswa' ? 'bg-green-600 text-white' : 'bg-white text-gray-700 border border-gray-300'); ?>">Data
                        Siswa</a>
                </div>
            <?php endif; ?>
        </div>

        <?php if(!$kelas): ?>
            <div class="overflow-hidden rounded-lg border border-dashed border-gray-300 bg-white p-10 text-center">
                <h2 class="text-lg font-semibold text-gray-900">Belum ada kelas yang diampu</h2>
                <p class="mt-2 text-sm text-gray-500">Data kelas akan muncul setelah Anda ditetapkan sebagai wali kelas.</p>
            </div>
        <?php else: ?>
            <div class="mb-6 grid gap-3 md:grid-cols-3 xl:grid-cols-6">
                <div class="rounded-lg border border-gray-200 bg-white p-3.5">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Nama Kelas</p>
                    <p class="mt-1 text-xl font-bold text-gray-900"><?php echo e($kelas->nama_kelas); ?></p>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-3.5">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Semester</p>
                    <p class="mt-1 text-xl font-bold text-gray-900"><?php echo e($kelas->semester?->nama ?? '-'); ?></p>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-3.5">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total Siswa</p>
                    <p class="mt-1 text-xl font-bold text-gray-900"><?php echo e($ringkasan->total_siswa ?? 0); ?></p>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-3.5">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Absensi Harian</p>
                    <p class="mt-1 text-xl font-bold text-gray-900">
                        <?php echo e($ringkasan->hadir_harian ?? 0); ?>/<?php echo e($ringkasan->total_harian ?? 0); ?></p>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-3.5">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Rata-rata Nilai</p>
                    <p class="mt-1 text-xl font-bold text-gray-900">
                        <?php echo e($ringkasan->rata_rata_tugas > 0 ? $ringkasan->rata_rata_tugas : '-'); ?></p>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-3.5">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Catatan Perilaku</p>
                    <p class="mt-1 text-xl font-bold text-gray-900"><?php echo e($ringkasan->total_catatan_perilaku ?? 0); ?></p>
                </div>
            </div>

            <?php if($activeTab === 'ringkasan'): ?>
                <div class="space-y-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="rounded-lg border border-gray-200 bg-white p-5">
                            <h3 class="text-sm font-semibold text-gray-900 mb-4">Statistik Nilai Akademik</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-xs text-gray-500">Rata-rata Nilai Tugas</span>
                                    <span
                                        class="text-xs font-medium text-gray-900"><?php echo e($ringkasan->rata_rata_tugas > 0 ? $ringkasan->rata_rata_tugas : 'Belum ada data'); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-xs text-gray-500">Rata-rata Nilai Ujian</span>
                                    <span
                                        class="text-xs font-medium text-gray-900"><?php echo e($ringkasan->rata_rata_ujian > 0 ? $ringkasan->rata_rata_ujian : 'Belum ada data'); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 bg-white p-5">
                            <h3 class="text-sm font-semibold text-gray-900 mb-4">Statistik Absensi</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-xs text-gray-500">Absensi Mapel</span>
                                    <span
                                        class="text-xs font-medium text-gray-900"><?php echo e($ringkasan->hadir_mapel ?? 0); ?>/<?php echo e($ringkasan->total_mapel ?? 0); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-xs text-gray-500">Total Keterlambatan</span>
                                    <span class="text-xs font-medium text-gray-900"><?php echo e($ringkasan->total_keterlambatan_menit); ?>

                                        menit</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-5">
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">Catatan Data Mentah</h3>
                        <p class="text-xs text-gray-500">Status prioritas siswa belum ditentukan di halaman ini. Data absensi,
                            nilai, dan perilaku disiapkan sebagai bahan untuk perhitungan SAW terpisah.</p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($activeTab === 'siswa'): ?>
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 bg-gray-50">
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">No</th>
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">NIS</th>
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">Nama Siswa</th>
                                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-700">Kategori SAW</th>
                                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-700">Skor SAW</th>
                                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-700">Tren Harian</th>
                                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-700">Rekomendasi</th>
                                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-700">Nilai Tugas</th>
                                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-700">Nilai Ujian</th>
                                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-700">Absensi</th>
                                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-700">Keterlambatan</th>
                                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-700">Catatan Perilaku
                                    </th>
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">Alamat</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php $__empty_1 = true; $__currentLoopData = $siswa; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <?php
                                        $nilaiTugas = $item->nilai_tugas_avg;
                                        $nilaiUjian = $item->nilai_ujian_avg;
                                        $hadirAbsensi = $item->hadir_absensi_count ?? 0;
                                        $totalAbsensi = $item->total_absensi_count ?? 0;
                                        $keterlambatan = $item->total_keterlambatan_menit ?? 0;
                                        $catatanPerilaku = $item->catatan_perilaku_count ?? 0;
                                    ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-5 py-3.5 text-xs font-mono text-gray-400">
                                            <?php echo e($siswa->firstItem() + $i); ?>

                                        </td>
                                        <td class="px-5 py-3.5 font-mono text-gray-700"><?php echo e($item->nis); ?></td>
                                        <td class="px-5 py-3.5 font-medium text-gray-900"><?php echo e($item->nama); ?></td>
                                        <td class="px-5 py-3.5 text-center"><?php echo $__env->make('partials.badge-kategori', [
                                            'kategori' => $item->saw_kategori ?? null,
                                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></td>
                                        <td class="px-5 py-3.5 text-center text-sm font-bold font-mono text-gray-700">
                                            <?php echo e($item->saw_skor_akhir ? number_format($item->saw_skor_akhir, 2) : '-'); ?></td>
                                        <td class="px-5 py-3.5 text-center"><?php echo $__env->make('partials.trend-indicator', [
                                            'trend' => $item->saw_trend_harian ?? null,
                                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></td>
                                        <td class="px-5 py-3.5 text-center">
                                            <?php if($item->saw_ai_rekomendasi): ?>
                                                <button type="button"
                                                    @click="activeModal = <?php echo e($i); ?>"
                                                    class="text-xs font-medium text-emerald-700 transition-colors hover:text-emerald-800">
                                                    Lihat
                                                </button>
                                            <?php else: ?>
                                                <span class="text-xs text-gray-400">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-5 py-3.5 text-center font-mono text-gray-700">
                                            <?php echo e($nilaiTugas ? round($nilaiTugas) : '-'); ?></td>
                                        <td class="px-5 py-3.5 text-center font-mono text-gray-700">
                                            <?php echo e($nilaiUjian ? round($nilaiUjian) : '-'); ?></td>
                                        <td class="px-5 py-3.5 text-center font-mono text-gray-700">
                                            <?php echo e($hadirAbsensi); ?>/<?php echo e($totalAbsensi); ?></td>
                                        <td class="px-5 py-3.5 text-center font-mono text-gray-700">
                                            <?php echo e($keterlambatan ? $keterlambatan . ' mnt' : '-'); ?></td>
                                        <td class="px-5 py-3.5 text-center font-mono text-gray-700"><?php echo e($catatanPerilaku); ?></td>
                                        <td class="px-5 py-3.5 text-gray-700"><?php echo e($item->alamat ?? '-'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="13" class="px-5 py-12 text-center">
                                            <p class="font-medium text-gray-600">Belum ada data siswa</p>
                                            <p class="mt-1 text-sm text-gray-500">Siswa pada kelas ini belum ditambahkan.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if($siswa->hasPages()): ?>
                        <div
                            class="flex flex-col items-center justify-between gap-3 border-t border-gray-200 bg-gray-50 px-5 py-3.5 sm:flex-row">
                            <p class="text-xs text-gray-500">
                                Menampilkan
                                <span
                                    class="font-semibold text-gray-700"><?php echo e($siswa->firstItem()); ?>–<?php echo e($siswa->lastItem()); ?></span>
                                dari
                                <span class="font-semibold text-gray-700"><?php echo e($siswa->total()); ?></span>
                                siswa
                            </p>

                            <div class="flex items-center gap-1">
                                <?php if($siswa->onFirstPage()): ?>
                                    <span
                                        class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-300 cursor-default select-none">
                                        ‹ Prev
                                    </span>
                                <?php else: ?>
                                    <a href="<?php echo e($siswa->previousPageUrl()); ?>&tab=siswa"
                                        class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 transition">
                                        ‹ Prev
                                    </a>
                                <?php endif; ?>

                                <?php
                                    $currentPage = $siswa->currentPage();
                                    $lastPage = $siswa->lastPage();
                                    $start = max(1, $currentPage - 2);
                                    $end = min($lastPage, $currentPage + 2);
                                ?>

                                <?php if($start > 1): ?>
                                    <a href="<?php echo e($siswa->url(1)); ?>&tab=siswa"
                                        class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 transition">
                                        1
                                    </a>
                                    <?php if($start > 2): ?>
                                        <span class="px-1 text-xs text-gray-400">…</span>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php $__currentLoopData = $siswa->getUrlRange($start, $end); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($page === $currentPage): ?>
                                        <span
                                            class="rounded-md border border-green-600 bg-green-700 px-3 py-1.5 text-xs font-semibold text-white select-none">
                                            <?php echo e($page); ?>

                                        </span>
                                    <?php else: ?>
                                        <a href="<?php echo e($url); ?>&tab=siswa"
                                            class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 transition">
                                            <?php echo e($page); ?>

                                        </a>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                <?php if($end < $lastPage): ?>
                                    <?php if($end < $lastPage - 1): ?>
                                        <span class="px-1 text-xs text-gray-400">…</span>
                                    <?php endif; ?>
                                    <a href="<?php echo e($siswa->url($lastPage)); ?>&tab=siswa"
                                        class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 transition">
                                        <?php echo e($lastPage); ?>

                                    </a>
                                <?php endif; ?>

                                <?php if($siswa->hasMorePages()): ?>
                                    <a href="<?php echo e($siswa->nextPageUrl()); ?>&tab=siswa"
                                        class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 transition">
                                        Next ›
                                    </a>
                                <?php else: ?>
                                    <span
                                        class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-300 cursor-default select-none">
                                        Next ›
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php if($siswa->total() > 0): ?>
                            <div class="border-t border-gray-200 bg-gray-50 px-5 py-3">
                                <p class="text-xs text-gray-500">
                                    Menampilkan semua
                                    <span class="font-semibold text-gray-700"><?php echo e($siswa->total()); ?></span>
                                    siswa
                                </p>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php $__currentLoopData = $siswa; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($item->saw_ai_rekomendasi): ?>
                    <div x-show="activeModal === <?php echo e($i); ?>" x-cloak
                        class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center">
                        <div class="absolute inset-0 bg-slate-950/50" @click="activeModal = null"></div>
                        <div class="relative z-10 w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl max-h-[90vh] flex flex-col">
                            <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-4 sm:px-6">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Rekomendasi AI</p>
                                    <h3 class="mt-1 text-lg font-bold text-gray-900"><?php echo e($item->nama ?? '-'); ?></h3>
                                    <p class="mt-1 text-sm text-gray-500">NIS <?php echo e($item->nis ?? '-'); ?></p>
                                </div>
                                <button type="button" @click="activeModal = null"
                                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 hover:bg-gray-50">
                                    <i class="ti ti-x text-base"></i>
                                </button>
                            </div>

                            <div class="flex-1 overflow-y-auto px-5 py-4 sm:px-6">
                                <div class="space-y-4">
                                    <?php if(!empty($item->saw_ai_rekomendasi['penyebab']) && is_array($item->saw_ai_rekomendasi['penyebab'])): ?>
                                        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4">
                                            <p class="text-sm font-semibold text-rose-800 mb-2">Penyebab
                                                <span
                                                    class="ml-2 rounded-full bg-white px-2 py-0.5 text-[10px] font-semibold text-rose-700"><?php echo e(count($item->saw_ai_rekomendasi['penyebab'])); ?>

                                                    poin</span>
                                            </p>
                                            <ul class="space-y-2 text-sm text-rose-900">
                                                <?php $__currentLoopData = $item->saw_ai_rekomendasi['penyebab']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $penyebab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <li class="rounded-lg bg-white/80 px-3 py-2"><?php echo e($penyebab); ?></li>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>

                                    <?php if(!empty($item->saw_ai_rekomendasi['saran']) && is_array($item->saw_ai_rekomendasi['saran'])): ?>
                                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                                            <p class="text-sm font-semibold text-emerald-800 mb-2">Saran
                                                <span
                                                    class="ml-2 rounded-full bg-white px-2 py-0.5 text-[10px] font-semibold text-emerald-700"><?php echo e(count($item->saw_ai_rekomendasi['saran'])); ?>

                                                    poin</span>
                                            </p>
                                            <ul class="space-y-2 text-sm text-emerald-900">
                                                <?php $__currentLoopData = $item->saw_ai_rekomendasi['saran']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $saran): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <li class="rounded-lg bg-white/80 px-3 py-2"><?php echo e($saran); ?></li>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-2 border-t border-gray-100 px-5 py-4 sm:px-6">
                                <button type="button" @click="activeModal = null"
                                    class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\projects\ews-smansa\resources\views/wali_kelas/kelas_saya/index.blade.php ENDPATH**/ ?>