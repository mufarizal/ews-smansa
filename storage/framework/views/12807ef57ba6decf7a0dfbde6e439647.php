<?php $__env->startSection('title', 'Point Perilaku'); ?>

<?php $__env->startSection('content'); ?>
<div class="mx-auto max-w-7xl">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Guru BK</p>
            <h1 class="mt-1.5 text-2xl font-bold text-gray-900">Point Perilaku</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola master data perilaku dan poin yang dapat dicatat guru mapel.</p>
        </div>
        <a href="<?php echo e(route('guru_bk.point-perilaku.create')); ?>"
            class="inline-flex items-center gap-2 rounded-lg bg-green-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-800">
            <i class="ti ti-plus text-base"></i>
            Tambah Perilaku
        </a>
    </div>

    <?php if(session('success')): ?>
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if($perilakus->isEmpty()): ?>
        <div class="overflow-hidden rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100">
                <i class="ti ti-clipboard-list text-2xl text-gray-400"></i>
            </div>
            <h2 class="text-base font-semibold text-gray-900">Belum ada data perilaku</h2>
            <p class="mt-1.5 text-sm text-gray-500">Tambahkan perilaku baru untuk memulai.</p>
        </div>
    <?php else: ?>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-5 py-3.5 text-left">Nama Perilaku</th>
                        <th class="px-5 py-3.5 text-center">Jenis</th>
                        <th class="px-5 py-3.5 text-center">Poin</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__currentLoopData = $perilakus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $perilaku): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4 font-medium text-gray-900"><?php echo e($perilaku->nama_perilaku); ?></td>
                            <td class="px-5 py-4 text-center">
                                <?php if($perilaku->jenis === 'positif'): ?>
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">
                                        Positif
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-medium text-rose-800">
                                        Negatif
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="font-mono <?php echo e($perilaku->poin > 0 ? 'text-emerald-700' : 'text-rose-700'); ?>">
                                    <?php echo e($perilaku->poin > 0 ? '+' : ''); ?><?php echo e($perilaku->poin); ?>

                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <?php if($perilaku->status_aktif): ?>
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                        Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">
                                        Nonaktif
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="<?php echo e(route('guru_bk.point-perilaku.edit', $perilaku)); ?>"
                                        class="rounded-lg p-2 text-blue-600 hover:bg-blue-50">
                                        <i class="ti ti-pencil text-base"></i>
                                    </a>
                                    <form method="POST" action="<?php echo e(route('guru_bk.point-perilaku.destroy', $perilaku)); ?>"
                                        onsubmit="return confirm('Hapus perilaku ini?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit"
                                            class="rounded-lg p-2 text-rose-600 hover:bg-rose-50">
                                            <i class="ti ti-trash text-base"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\projects\ews-smansa\resources\views/guru_bk/point-perilaku/index.blade.php ENDPATH**/ ?>