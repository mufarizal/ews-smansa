<?php $__env->startSection('title', 'Edit Perilaku'); ?>

<?php $__env->startSection('content'); ?>
    <div class="mx-auto max-w-2xl">
        <div class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Point Perilaku</p>
            <h1 class="mt-1.5 text-2xl font-bold text-gray-900">Edit Perilaku</h1>
            <p class="mt-1 text-sm text-gray-500">Ubah data perilaku beserta poinnya.</p>
        </div>

        <?php if($errors->any()): ?>
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-inside list-disc">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="rounded-xl border border-gray-200 bg-white p-6">
            <form method="POST" action="<?php echo e(route('guru_bk.point-perilaku.update', $perilaku->id)); ?>">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <div class="mb-4">
                    <label for="nama_perilaku" class="mb-1.5 block text-sm font-medium text-gray-700">Nama Perilaku</label>
                    <input type="text" name="nama_perilaku" id="nama_perilaku"
                        value="<?php echo e(old('nama_perilaku', $perilaku->nama_perilaku)); ?>"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500"
                        required>
                    <?php $__errorArgs = ['nama_perilaku'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-1 text-xs text-rose-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="mb-4">
                    <label for="jenis" class="mb-1.5 block text-sm font-medium text-gray-700">Jenis</label>
                    <select name="jenis" id="jenis"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500"
                        required>
                        <option value="">-- Pilih Jenis --</option>
                        <option value="positif" <?php echo e(old('jenis', $perilaku->jenis) == 'positif' ? 'selected' : ''); ?>>Positif
                        </option>
                        <option value="negatif" <?php echo e(old('jenis', $perilaku->jenis) == 'negatif' ? 'selected' : ''); ?>>Negatif
                        </option>
                    </select>
                    <?php $__errorArgs = ['jenis'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-1 text-xs text-rose-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="mb-4">
                    <label for="poin" class="mb-1.5 block text-sm font-medium text-gray-700">Poin</label>
                    <input type="number" name="poin" id="poin" value="<?php echo e(old('poin', $perilaku->poin)); ?>"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500"
                        required>
                    <?php $__errorArgs = ['poin'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-1 text-xs text-rose-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="mb-6">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="status_aktif" id="status_aktif" value="1"
                            <?php echo e(old('status_aktif', $perilaku->status_aktif) ? 'checked' : ''); ?>

                            class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                        <span class="text-sm text-gray-700">Aktif</span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="<?php echo e(route('guru_bk.point-perilaku.index')); ?>"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        Kembali
                    </a>
                    <button type="submit"
                        class="rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-800">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\projects\ews-smansa\resources\views/guru_bk/point-perilaku/edit.blade.php ENDPATH**/ ?>