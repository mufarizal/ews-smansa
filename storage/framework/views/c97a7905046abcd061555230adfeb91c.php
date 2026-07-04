<?php $__env->startSection('title', 'Dashboard Guru BK'); ?>

<?php $__env->startSection('content'); ?>
    <div class="mx-auto max-w-7xl space-y-6">

        
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Guru BK</p>
                <h1 class="mt-1.5 text-2xl font-bold text-gray-900">Dashboard</h1>
                <p class="mt-1 text-sm text-gray-500">Ringkasan status Early Warning System untuk kelas yang Anda ampu.</p>
            </div>
        </div>

        
        <?php if($semester): ?>
            <div class="flex items-center gap-2.5 rounded-lg border border-blue-100 bg-blue-50 px-4 py-2.5">
                <span class="h-2 w-2 shrink-0 rounded-full bg-blue-500"></span>
                <span class="text-sm text-blue-800">
                    Semester aktif: <strong><?php echo e($semester->nama); ?></strong>
                    <span class="text-blue-600 text-xs ml-1">
                        (<?php echo e(\Carbon\Carbon::parse($semester->tanggal_mulai)->translatedFormat('d M Y')); ?>

                        – <?php echo e(\Carbon\Carbon::parse($semester->tanggal_selesai)->translatedFormat('d M Y')); ?>)
                    </span>
                </span>
            </div>
        <?php else: ?>
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm text-amber-800">
                Belum ada semester aktif. Menampilkan data apa adanya.
            </div>
        <?php endif; ?>

        
        <?php if($kelasBelumGenerate->isNotEmpty() || $kelasStale->isNotEmpty()): ?>
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                <div class="flex items-start gap-3">
                    <svg class="h-5 w-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-amber-800">Data SAW perlu diperbarui</p>
                        <ul class="mt-1.5 space-y-1">
                            <?php $__currentLoopData = $kelasBelumGenerate; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gbk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="text-xs text-amber-700 flex items-center justify-between">
                                    <span><span class="font-medium"><?php echo e($gbk->kelas->nama_kelas ?? '-'); ?></span> — belum
                                        pernah di-generate</span>
                                    <a href="<?php echo e(route('guru_bk.monitoring.show', $gbk->kelas_id)); ?>"
                                        class="ml-4 underline hover:text-amber-900 whitespace-nowrap">Buka Monitoring →</a>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php $__currentLoopData = $kelasStale; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gbk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $info = $hasilPerKelas[$gbk->kelas_id]; ?>
                                <li class="text-xs text-amber-700 flex items-center justify-between">
                                    <span>
                                        <span class="font-medium"><?php echo e($gbk->kelas->nama_kelas ?? '-'); ?></span> — terakhir
                                        di-generate
                                        <?php echo e(\Carbon\Carbon::parse($info->last_generated_at)->diffForHumans()); ?>

                                    </span>
                                    <a href="<?php echo e(route('guru_bk.monitoring.show', $gbk->kelas_id)); ?>"
                                        class="ml-4 underline hover:text-amber-900 whitespace-nowrap">Buka Monitoring →</a>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        
        <?php if(isset($ringkasan['total']) && $ringkasan['total'] > 0): ?>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <?php
                    $total = $ringkasan['total'];
                    $pct = fn($n) => $total > 0 ? round(($n / $total) * 100) : 0;
                ?>
                <div class="rounded-xl border border-pink-200 bg-white p-5 shadow-sm">
                    <p class="text-3xl font-bold text-gray-800"><?php echo e($total); ?></p>
                    <p class="text-sm text-gray-500 mt-0.5">Total Siswa</p>
                    <p class="text-xs text-gray-400 mt-0.5"><?php echo e($guruBkKelas->count()); ?> kelas diampu</p>
                </div>
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-5 shadow-sm">
                    <p class="text-3xl font-bold text-rose-600"><?php echo e($ringkasan['binaan']); ?></p>
                    <p class="text-sm font-medium text-rose-700 mt-0.5">Binaan</p>
                    <p class="text-xs text-rose-400 mt-0.5"><?php echo e($pct($ringkasan['binaan'])); ?>% dari total</p>
                </div>
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                    <p class="text-3xl font-bold text-amber-600"><?php echo e($ringkasan['perhatian']); ?></p>
                    <p class="text-sm font-medium text-amber-700 mt-0.5">Perhatian</p>
                    <p class="text-xs text-amber-400 mt-0.5"><?php echo e($pct($ringkasan['perhatian'])); ?>% dari total</p>
                </div>
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                    <p class="text-3xl font-bold text-emerald-600"><?php echo e($ringkasan['aman']); ?></p>
                    <p class="text-sm font-medium text-emerald-700 mt-0.5">Aman</p>
                    <p class="text-xs text-emerald-400 mt-0.5"><?php echo e($pct($ringkasan['aman'])); ?>% dari total</p>
                </div>
            </div>
        <?php endif; ?>

        
        <?php if($guruBkKelas->isNotEmpty()): ?>
            <?php
                $chartBinaan = $guruBkKelas->map(fn($g) => ($ringkasanPerKelas[$g->kelas_id]['siswa_terburuk'] ?? collect())->where('kategori', 'binaan')->count());
                $chartPerhatian = $guruBkKelas->map(fn($g) => ($ringkasanPerKelas[$g->kelas_id]['siswa_terburuk'] ?? collect())->where('kategori', 'perhatian')->count());
                $chartAman = $guruBkKelas->map(fn($g) => ($ringkasanPerKelas[$g->kelas_id]['siswa_terburuk'] ?? collect())->where('kategori', 'aman')->count());
            ?>
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Distribusi Kategori per Kelas</h3>
                <div class="relative h-64 w-full">
                    <canvas id="chartPerKelas"></canvas>
                </div>
            </div>

            <?php $__env->startPush('scripts'); ?>
                <?php if (! $__env->hasRenderedOnce('10caf795-119d-426f-8a6a-2b948cd6d89d')): $__env->markAsRenderedOnce('10caf795-119d-426f-8a6a-2b948cd6d89d'); ?>
                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <?php endif; ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const canvas = document.getElementById('chartPerKelas');
                        if (!canvas || typeof Chart === 'undefined') {
                            return;
                        }

                        const ctx = canvas.getContext('2d');
                        const labels = <?php echo json_encode($guruBkKelas->map(fn($g) => $g->kelas->nama_kelas ?? '-'), 15, 512) ?>;

                        const allData = [...<?php echo json_encode($chartBinaan, 15, 512) ?>, ...<?php echo json_encode($chartPerhatian, 15, 512) ?>, ...<?php echo json_encode($chartAman, 15, 512) ?>].filter(v => v > 0);
                        const maxVal = Math.max(...allData, 0) + 1 || 5;

                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [
                                    {
                                        label: 'Binaan',
                                        data: <?php echo json_encode($chartBinaan, 15, 512) ?>,
                                        backgroundColor: 'rgba(225, 29, 72, 0.2)',
                                        borderColor: 'rgb(225, 29, 72)',
                                        borderWidth: 1,
                                    },
                                    {
                                        label: 'Perhatian',
                                        data: <?php echo json_encode($chartPerhatian, 15, 512) ?>,
                                        backgroundColor: 'rgba(217, 119, 19, 0.2)',
                                        borderColor: 'rgb(217, 119, 19)',
                                        borderWidth: 1,
                                    },
                                    {
                                        label: 'Aman',
                                        data: <?php echo json_encode($chartAman, 15, 512) ?>,
                                        backgroundColor: 'rgba(16, 185, 129, 0.2)',
                                        borderColor: 'rgb(16, 185, 129)',
                                        borderWidth: 1,
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'top',
                                    }
                                },
                                scales: {
                                    y: {
                                        min: 0,
                                        max: maxVal,
                                        ticks: {
                                            stepSize: 1
                                        },
                                        grid: {
                                            color: 'rgba(148, 163, 184, 0.16)'
                                        }
                                    },
                                    x: {
                                        grid: {
                                            display: false
                                        }
                                    }
                                }
                            }
                        });
                    });
                </script>
            <?php $__env->stopPush(); ?>
        <?php endif; ?>

        
        <div class="space-y-4">
            <?php $__empty_1 = true; $__currentLoopData = $guruBkKelas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gbk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $perKelas = $ringkasanPerKelas[$gbk->kelas_id] ?? [
                        'kelas' => $gbk->kelas,
                        'siswa_terburuk' => collect(),
                        'trend_mingguan' => null,
                    ];
                    $trendMingguan = $perKelas['trend_mingguan'];
                    $isStale = $kelasStale->contains('kelas_id', $gbk->kelas_id);
                    $isBelum = $kelasBelumGenerate->contains('kelas_id', $gbk->kelas_id);
                ?>
                <div
                    class="overflow-hidden rounded-xl border <?php echo e($isStale || $isBelum ? 'border-amber-200' : 'border-gray-200'); ?> bg-white hover:border-pink-300 hover:shadow-sm transition-all">
                    <?php $siswaPreview = $perKelas['siswa_terburuk']->take(5); ?>
                    <div class="flex items-center justify-between gap-3 border-b border-gray-100 bg-gray-50 px-5 py-3.5">
                        <div class="flex items-center gap-2.5">
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-pink-100 text-pink-700 font-bold text-xs shrink-0">
                                <?php echo e(strtoupper(substr($gbk->kelas->nama_kelas ?? '?', 0, 2))); ?>

                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 group-hover:text-pink-700 transition-colors">
                                    <?php echo e($gbk->kelas->nama_kelas ?? '-'); ?>

                                </h3>
                                <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                    <?php if($isBelum): ?>
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500">Belum
                                            generate</span>
                                    <?php elseif($isStale): ?>
                                        <span
                                            class="rounded-full bg-amber-50 border border-amber-200 px-2 py-0.5 text-xs text-amber-700">Stale</span>
                                    <?php endif; ?>
                                    <?php echo $__env->make('partials.trend-mingguan-badge', ['trend' => $trendMingguan], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                </div>
                            </div>
                        </div>
                        <a href="<?php echo e(route('guru_bk.monitoring.show', $gbk->kelas_id)); ?>"
                            class="text-xs font-medium text-pink-700 hover:text-pink-800 transition-colors shrink-0 ml-2">
                            Lihat semua siswa →
                        </a>
                    </div>

                    <div class="px-5 py-4">
                        <?php if($siswaPreview->isEmpty()): ?>
                            <p class="text-sm text-gray-400 italic">Belum ada data siswa untuk kelas ini.</p>
                        <?php else: ?>
                            <div class="divide-y divide-gray-50">
                                <?php $__currentLoopData = $siswaPreview; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="flex items-center justify-between gap-3 py-2.5 first:pt-0 last:pb-0">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <div
                                                class="flex h-7 w-7 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-600 shrink-0">
                                                <?php echo e(strtoupper(substr($item->siswa->nama ?? '?', 0, 1))); ?>

                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-sm font-medium text-gray-800 truncate">
                                                    <?php echo e($item->siswa->nama ?? '-'); ?></p>
                                                <p class="text-xs text-gray-400">NIS <?php echo e($item->siswa->nis ?? ''); ?></p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <?php echo $__env->make('partials.badge-kategori', [
                                                'kategori' => $item->kategori ?? null,
                                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                            <span
                                                class="text-sm font-bold font-mono text-gray-700"><?php echo e(number_format($item->skor_akhir ?? 0, 2)); ?></span>
                                            <?php echo $__env->make('partials.trend-indicator', [
                                                'trend' => $item->trend_harian ?? null,
                                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center">
                    <p class="font-medium text-gray-600">Belum ada kelas yang diampu</p>
                    <p class="mt-1 text-sm text-gray-400">Data kelas akan muncul setelah admin membuat penugasan BK.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\projects\ews-smansa\resources\views/guru_bk/dashboard.blade.php ENDPATH**/ ?>