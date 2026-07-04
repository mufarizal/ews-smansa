<?php $__env->startSection('title', 'Dashboard Wali Kelas'); ?>

<?php $__env->startSection('content'); ?>
    <div class="mx-auto max-w-7xl space-y-6">

        
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Wali Kelas</p>
                <h1 class="mt-1.5 text-2xl font-bold text-gray-900">Dashboard</h1>
                <p class="mt-1 text-sm text-gray-500">Ringkasan status Early Warning System kelas yang Anda ampu.</p>
            </div>
        </div>

        <?php if(!$kelas): ?>
            <div class="rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center">
                <h2 class="text-lg font-semibold text-gray-900">Belum ada kelas yang diampu</h2>
                <p class="mt-2 text-sm text-gray-500">Data kelas akan muncul setelah Anda ditetapkan sebagai wali kelas.</p>
            </div>
        <?php else: ?>
            
            <?php if($semester): ?>
                <div class="flex flex-wrap items-center gap-3 rounded-lg border border-blue-100 bg-blue-50 px-4 py-2.5">
                    <span class="h-2 w-2 shrink-0 rounded-full bg-blue-500"></span>
                    <span class="text-sm text-blue-800">
                        Semester aktif: <strong><?php echo e($semester->nama); ?></strong>
                        <span class="text-blue-600 text-xs ml-1">
                            (<?php echo e(\Carbon\Carbon::parse($semester->tanggal_mulai)->translatedFormat('d M Y')); ?>

                            – <?php echo e(\Carbon\Carbon::parse($semester->tanggal_selesai)->translatedFormat('d M Y')); ?>)
                        </span>
                    </span>
                    <?php echo $__env->make('partials.trend-mingguan-badge', ['trend' => $trendMingguanKelas], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            <?php endif; ?>

            
            <?php if($ringkasan && $ringkasan['total'] > 0): ?>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="rounded-xl border border-emerald-200 bg-white p-5 shadow-sm">
                        <p class="text-3xl font-bold text-gray-800"><?php echo e($ringkasan['total']); ?></p>
                        <p class="text-sm text-gray-500 mt-0.5">Total Siswa</p>
                    </div>
                    <div class="rounded-xl border border-emerald-200 bg-white p-5 shadow-sm">
                        <p class="text-3xl font-bold text-rose-600"><?php echo e($ringkasan['binaan'] ?? 0); ?></p>
                        <p class="text-sm font-medium text-rose-700 mt-0.5">Binaan</p>
                    </div>
                    <div class="rounded-xl border border-emerald-200 bg-white p-5 shadow-sm">
                        <p class="text-3xl font-bold text-amber-600"><?php echo e($ringkasan['perhatian'] ?? 0); ?></p>
                        <p class="text-sm font-medium text-amber-700 mt-0.5">Perhatian</p>
                    </div>
                    <div class="rounded-xl border border-emerald-200 bg-white p-5 shadow-sm">
                        <p class="text-3xl font-bold text-emerald-600"><?php echo e($ringkasan['aman'] ?? 0); ?></p>
                        <p class="text-sm font-medium text-emerald-700 mt-0.5">Aman</p>
                    </div>
                </div>
            <?php endif; ?>

            
            <?php if($siswaTerurut->count() > 1): ?>
                <?php
                    $chartData = $siswaTerurut->take(20)->map(
                        fn($r) => [
                            'nama' => $r->siswa->nama ?? '?',
                            'skor' => (float) ($r->skor_akhir ?? 0),
                        ],
                    )->values();
                    $chartScores = $chartData->pluck('skor');
                    $chartMin = max(0, floor(($chartScores->min() - 0.05) * 100) / 100);
                    $chartMax = min(1, ceil(($chartScores->max() + 0.05) * 100) / 100);
                ?>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Grafik Skor SAW Siswa</h3>
                    <div class="relative h-64 w-full">
                        <canvas id="waliKelasChart"></canvas>
                    </div>
                </div>

                <?php $__env->startPush('scripts'); ?>
                    <?php if (! $__env->hasRenderedOnce('afc37552-8455-4c8d-8447-b70baf0cf7d4')): $__env->markAsRenderedOnce('afc37552-8455-4c8d-8447-b70baf0cf7d4'); ?>
                        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <?php endif; ?>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const canvas = document.getElementById('waliKelasChart');
                            if (!canvas || typeof Chart === 'undefined') {
                                return;
                            }

                            const ctx = canvas.getContext('2d');
                            const labels = <?php echo json_encode($chartData->pluck('nama'), 15, 512) ?>;
                            const data = <?php echo json_encode($chartData->pluck('skor'), 15, 512) ?>;

                            const gradient = ctx.createLinearGradient(0, 0, 0, 260);
                            gradient.addColorStop(0, 'rgba(219, 39, 119, 0.25)');
                            gradient.addColorStop(1, 'rgba(219, 39, 119, 0.02)');

                            new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        label: 'Skor Akhir',
                                        data: data,
                                        borderColor: '#db2777',
                                        backgroundColor: gradient,
                                        borderWidth: 2.5,
                                        pointBackgroundColor: '#db2777',
                                        pointBorderColor: '#ffffff',
                                        pointBorderWidth: 2,
                                        pointRadius: 4,
                                        pointHoverRadius: 5,
                                        fill: true,
                                        tension: 0.35,
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            display: false
                                        },
                                        tooltip: {
                                            callbacks: {
                                                label: function(context) {
                                                    return 'Skor: ' + Number(context.parsed.y).toFixed(2);
                                                }
                                            }
                                        }
                                    },
                                    scales: {
                                        y: {
                                            min: <?php echo e($chartMin); ?>,
                                            max: <?php echo e($chartMax); ?>,
                                            ticks: {
                                                callback: function(value) {
                                                    return Number(value).toFixed(2);
                                                }
                                            },
                                            grid: {
                                                color: 'rgba(148, 163, 184, 0.16)'
                                            }
                                        },
                                        x: {
                                            ticks: {
                                                maxRotation: 45,
                                                minRotation: 45
                                            },
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

            
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="border-b border-gray-100 bg-gray-50 px-5 py-3.5">
                    <h2 class="text-sm font-semibold text-gray-900">Siswa Prioritas</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Diurutkan dari skor terendah</p>
                </div>
                <?php if($siswaTerurut->isEmpty()): ?>
                    <div class="py-12 text-center">
                        <p class="text-sm text-gray-400">Belum ada data SAW.</p>
                        <p class="text-xs text-gray-300 mt-1">Generate analisis SAW terlebih dahulu.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 bg-gray-50">
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">No</th>
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">Nama Siswa</th>
                                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-700">Kategori</th>
                                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-700">Skor Akhir</th>
                                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-700">Tren Harian</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php $__currentLoopData = $siswaTerurut; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-5 py-3.5 text-xs font-mono text-gray-400"><?php echo e($i + 1); ?></td>
                                        <td class="px-5 py-3.5">
                                            <div class="flex items-center gap-2.5">
                                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-600">
                                                    <?php echo e(strtoupper(substr($item->siswa->nama ?? '?', 0, 1))); ?>

                                                </div>
                                                <div class="min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 truncate"><?php echo e($item->siswa->nama ?? '-'); ?></p>
                                                    <p class="text-xs text-gray-400">NIS <?php echo e($item->siswa->nis ?? ''); ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3.5 text-center"><?php echo $__env->make('partials.badge-kategori', ['kategori' => $item->kategori ?? null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></td>
                                        <td class="px-5 py-3.5 text-center text-sm font-bold font-mono text-gray-800"><?php echo e(number_format($item->skor_akhir ?? 0, 2)); ?></td>
                                        <td class="px-5 py-3.5 text-center"><?php echo $__env->make('partials.trend-indicator', ['trend' => $item->trend_harian ?? null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            
            <?php if($jadwals->isNotEmpty()): ?>
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                    <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-emerald-800">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Jadwal Mengampu — <?php echo e($todayHari); ?>

                    </h3>
                    <div class="flex flex-wrap gap-2">
                        <?php $__currentLoopData = $jadwals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $j): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center gap-2.5 rounded-lg border border-emerald-200 bg-white px-3 py-2">
                                <span class="font-mono text-xs font-semibold text-emerald-700"><?php echo e(substr((string) $j->jam_mulai, 0, 5)); ?></span>
                                <span class="h-3 w-px bg-emerald-200"></span>
                                <span class="text-xs font-medium text-gray-700"><?php echo e($j->mapel?->nama ?? '–'); ?></span>
                                <span class="rounded bg-emerald-100 px-1.5 py-0.5 text-xs text-emerald-700"><?php echo e($j->kelas?->nama_kelas ?? '–'); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\projects\ews-smansa\resources\views/wali_kelas/dashboard.blade.php ENDPATH**/ ?>