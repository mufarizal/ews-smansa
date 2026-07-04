<?php $__env->startSection('title', 'Monitoring Siswa — ' . ($siswa->nama ?? 'Detail')); ?>

<?php $__env->startSection('content'); ?>
    <div class="mx-auto max-w-7xl space-y-6" x-data="{ openAiModal: false }">

        
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-center gap-3">
                <a href="<?php echo e(route('guru_bk.monitoring.show', $kelas->id)); ?>"
                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Guru BK</p>
                    <h1 class="mt-1.5 text-2xl font-bold text-gray-900">Detail Siswa — <?php echo e($siswa->nama ?? '-'); ?></h1>
                    <p class="mt-1 text-xs text-gray-500">Kelas <?php echo e($kelas->nama_kelas ?? '-'); ?> · NIS
                        <?php echo e($siswa->nis ?? '-'); ?></p>
                </div>
            </div>
        </div>

        
        <?php if($hasilTerbaru): ?>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Kategori</p>
                    <div class="mt-2"><?php echo $__env->make('partials.badge-kategori', ['kategori' => $hasilTerbaru->kategori ?? null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Skor Akhir</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900"><?php echo e(number_format($hasilTerbaru->skor_akhir ?? 0, 2)); ?>

                    </p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">C1 · Akademik</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900"><?php echo e(number_format($hasilTerbaru->c1_akademik ?? 0, 2)); ?></p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">C2 · Absensi</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900"><?php echo e(number_format($hasilTerbaru->c2_absensi ?? 0, 2)); ?></p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">C3 · Perilaku</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900"><?php echo e(number_format($hasilTerbaru->c3_perilaku ?? 0, 2)); ?></p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">R1 · Akademik</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900"><?php echo e(number_format($hasilTerbaru->r1_akademik ?? 0, 2)); ?></p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">R2 · Absensi</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900"><?php echo e(number_format($hasilTerbaru->r2_absensi ?? 0, 2)); ?></p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">R3 · Perilaku</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900"><?php echo e(number_format($hasilTerbaru->r3_perilaku ?? 0, 2)); ?></p>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Tren Harian</p>
                <div class="mt-2"><?php echo $__env->make('partials.trend-indicator', ['trend' => $trendHarian], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
            </div>
        <?php else: ?>
            <div class="rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center">
                <p class="font-medium text-gray-600">Belum ada hasil SAW</p>
                <p class="mt-1 text-sm text-gray-400">Proses perhitungan belum dilakukan untuk siswa ini.</p>
            </div>
        <?php endif; ?>

        
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Rekomendasi AI</h3>
                    <p class="mt-1 text-xs text-gray-500">Detail dibuka dalam modal agar tetap rapi di mobile dan desktop.
                    </p>
                </div>
                <button type="button" @click="openAiModal = true"
                    class="inline-flex items-center justify-center rounded-lg border border-pink-200 bg-pink-50 px-4 py-2 text-xs font-semibold text-pink-700 transition hover:bg-pink-100">
                    Lihat hasil generate AI
                </button>
            </div>

<?php if($rekomendasiAi): ?>
                 <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                     <?php if(!empty($rekomendasiAi['generated_at'])): ?>
                         <span>Terakhir diperbarui
                             <?php echo e(\Carbon\Carbon::parse($rekomendasiAi['generated_at'])->diffForHumans()); ?></span>
                     <?php endif; ?>
                 </div>
             <?php else: ?>
                <div class="mt-3">
                    <?php echo $__env->make('partials.saw-rekomendasi-empty', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if($rekomendasiAi): ?>
            <div x-show="openAiModal" x-cloak class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center">
                <div class="absolute inset-0 bg-slate-950/50" @click="openAiModal = false"></div>
                <div class="relative z-10 w-full max-w-4xl overflow-hidden rounded-2xl bg-white shadow-2xl">
                    <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-4 sm:px-6">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Rekomendasi AI</p>
                            <h3 class="mt-1 text-lg font-bold text-gray-900"><?php echo e($siswa->nama ?? '-'); ?></h3>
                            <p class="mt-1 text-sm text-gray-500">Kelas <?php echo e($kelas->nama_kelas ?? '-'); ?> · NIS
                                <?php echo e($siswa->nis ?? '-'); ?></p>
                        </div>
                        <button type="button" @click="openAiModal = false"
                            class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 hover:bg-gray-50">
                            <i class="ti ti-x text-base"></i>
                        </button>
                    </div>

<div class="max-h-[75vh] overflow-y-auto px-5 py-5 sm:px-6">
                        <div class="space-y-4">
                            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4">
                                <p class="text-sm font-semibold text-rose-800 mb-2">Penyebab
                                    <span class="ml-2 rounded-full bg-white px-2 py-0.5 text-[10px] font-semibold text-rose-700"><?php echo e(count($rekomendasiAi['penyebab'] ?? [])); ?> poin</span>
                                </p>
                                <?php if(!empty($rekomendasiAi['penyebab'])): ?>
                                    <ul class="space-y-2 text-sm text-rose-900">
                                        <?php $__currentLoopData = $rekomendasiAi['penyebab']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <li class="rounded-lg bg-white/80 px-3 py-2"><?php echo e($item); ?></li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="mt-3 text-sm text-rose-900">Tidak ada rincian penyebab.</p>
                                <?php endif; ?>
                            </div>

                            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                                <p class="text-sm font-semibold text-emerald-800 mb-2">Saran
                                    <span class="ml-2 rounded-full bg-white px-2 py-0.5 text-[10px] font-semibold text-emerald-700"><?php echo e(count($rekomendasiAi['saran'] ?? [])); ?> poin</span>
                                </p>
                                <?php if(!empty($rekomendasiAi['saran'])): ?>
                                    <ul class="space-y-2 text-sm text-emerald-900">
                                        <?php $__currentLoopData = $rekomendasiAi['saran']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <li class="rounded-lg bg-white/80 px-3 py-2"><?php echo e($item); ?></li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="mt-3 text-sm text-emerald-900">Tidak ada saran yang diberikan.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-gray-100 px-5 py-4 sm:px-6">
                        <button type="button" @click="openAiModal = false"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Rentang Riwayat</h3>
            <form method="GET" action="<?php echo e(route('guru_bk.monitoring.siswa', [$kelas->id, $siswa->id])); ?>"
                class="flex flex-wrap items-center gap-2">
                <?php $__currentLoopData = [7, 30, 90, 180, 365]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $active = request('range') == $r && !request('dari'); ?>
                    <button type="submit" name="range" value="<?php echo e($r); ?>"
                        <?php if($active): ?> disabled class="rounded-lg border border-pink-600 bg-pink-700 px-3 py-1.5 text-xs font-semibold text-white" <?php else: ?> class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 transition-colors" <?php endif; ?>>
                        <?php echo e($r); ?> hari
                    </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <span class="text-xs text-gray-400">atau</span>
                <input type="date" name="dari" value="<?php echo e(request('dari')); ?>"
                    class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 focus:border-pink-600 focus:ring-2 focus:ring-pink-100">
                <span class="text-xs text-gray-400">—</span>
                <input type="date" name="sampai" value="<?php echo e(request('sampai')); ?>"
                    class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-700 focus:border-pink-600 focus:ring-2 focus:ring-pink-100">
                <button type="submit"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 transition-colors">Terapkan</button>
                <?php if(request()->hasAny(['range', 'dari', 'sampai'])): ?>
                    <a href="<?php echo e(route('guru_bk.monitoring.siswa', [$kelas->id, $siswa->id])); ?>"
                        class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                        Reset
                    </a>
                <?php endif; ?>
            </form>
            <p class="mt-2 text-xs text-gray-400">Menampilkan:
                <?php echo e(\Carbon\Carbon::parse($dari)->translatedFormat('d M Y')); ?> —
                <?php echo e(\Carbon\Carbon::parse($sampai)->translatedFormat('d M Y')); ?></p>
        </div>

        
        <?php if($riwayat->count() > 1): ?>
            <?php
                $chartData = $riwayat
                    ->map(
                        fn($r) => [
                            'tanggal' => \Carbon\Carbon::parse($r->tanggal_hitung)->format('d M Y'),
                            'skor' => (float) ($r->skor_akhir ?? 0),
                        ],
                    )
                    ->values();
                $chartScores = $chartData->pluck('skor');
                $chartMin = max(0, floor(($chartScores->min() - 0.05) * 100) / 100);
                $chartMax = min(1, ceil(($chartScores->max() + 0.05) * 100) / 100);
            ?>
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Grafik Tren Skor</h3>
                <div class="relative h-72 w-full">
                    <canvas id="riwayatChart"></canvas>
                </div>
            </div>

            <?php $__env->startPush('scripts'); ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const canvas = document.getElementById('riwayatChart');
                        if (!canvas || typeof Chart === 'undefined') {
                            return;
                        }

                        const ctx = canvas.getContext('2d');
                        const labels = <?php echo json_encode($chartData->pluck('tanggal'), 15, 512) ?>;
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
            <div class="px-5 py-3.5 border-b border-gray-100 bg-gray-50">
                <h3 class="text-sm font-semibold text-gray-900">Riwayat Perhitungan</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-700">Tanggal</th>
                            <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-700">Skor Akhir</th>
                            <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-700">Kategori</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__empty_1 = true; $__currentLoopData = $riwayat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3.5 text-sm text-gray-700">
                                    <?php echo e(\Carbon\Carbon::parse($r->tanggal_hitung)->translatedFormat('d M Y')); ?></td>
                                <td class="px-5 py-3.5 text-center text-sm font-bold font-mono text-gray-800">
                                    <?php echo e(number_format($r->skor_akhir ?? 0, 2)); ?></td>
                                <td class="px-5 py-3.5 text-center"><?php echo $__env->make('partials.badge-kategori', ['kategori' => $r->kategori ?? null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="3" class="px-5 py-10 text-center text-xs text-gray-400">Belum ada riwayat
                                    perhitungan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\projects\ews-smansa\resources\views/guru_bk/monitoring/siswa.blade.php ENDPATH**/ ?>