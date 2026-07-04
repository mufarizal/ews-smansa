<?php $__env->startSection('title', 'Monitoring SAW'); ?>

<?php $__env->startSection('content'); ?>
    <div class="mx-auto max-w-7xl space-y-6">

        
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Guru BK</p>
                <h1 class="mt-1.5 text-2xl font-bold text-gray-900">Monitoring SAW</h1>
                <p class="mt-1 text-sm text-gray-500">Pilih kelas untuk melihat detail status siswa.</p>
            </div>
            <?php if($semester): ?>
                <div class="shrink-0">
                    <span class="rounded-full border border-pink-200 bg-pink-50 px-3 py-1 text-xs font-medium text-pink-700">
                        <?php echo e($semester->nama); ?>

                    </span>
                </div>
            <?php endif; ?>
        </div>

        
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <?php $__empty_1 = true; $__currentLoopData = $ringkasanPerKelas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a href="<?php echo e(route('guru_bk.monitoring.show', $item['kelas']->id)); ?>" class="block overflow-hidden rounded-xl border border-gray-200 bg-white hover:border-pink-300 hover:shadow-sm transition-all">
                    <div class="border-b border-gray-100 bg-gray-50 px-5 py-3.5">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-pink-100 text-pink-700 font-bold text-xs">
                                <?php echo e(strtoupper(substr($item['kelas']->nama_kelas ?? '?', 0, 2))); ?>

                            </div>
                            <h3 class="font-semibold text-gray-900"><?php echo e($item['kelas']->nama_kelas ?? '-'); ?></h3>
                        </div>
                    </div>
                    <div class="px-5 py-4">
                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            <span class="rounded-full bg-rose-100 px-2 py-0.5 font-semibold text-rose-700"><?php echo e($item['binaan']); ?> Binaan</span>
                            <span class="rounded-full bg-amber-100 px-2 py-0.5 font-semibold text-amber-700"><?php echo e($item['perhatian']); ?> Perhatian</span>
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 font-semibold text-emerald-700"><?php echo e($item['aman']); ?> Aman</span>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">Total <?php echo e($item['total']); ?> siswa</p>
                    </div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="sm:col-span-2 lg:col-span-3 rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center">
                    <p class="font-medium text-gray-600">Belum ada data kelas</p>
                    <p class="mt-1 text-sm text-gray-400">Belum ada penugasan kelas untuk Anda di semester ini.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\projects\ews-smansa\resources\views/guru_bk/monitoring/index.blade.php ENDPATH**/ ?>