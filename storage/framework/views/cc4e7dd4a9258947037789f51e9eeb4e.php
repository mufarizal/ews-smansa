<?php $__env->startSection('title', 'Pengajaran Saya'); ?>

<?php $__env->startSection('content'); ?>
    <div class="mx-auto max-w-7xl" x-data="pengajaranSaya()">

        
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Guru BK</p>
                <h1 class="mt-1.5 text-2xl font-bold text-gray-900">Pengajaran Saya</h1>
                <p class="mt-1 text-sm text-gray-500">Kelas yang ditugaskan, daftar siswa, dan jadwal mengajar Anda.</p>
            </div>

            <?php if($guru): ?>
                <form method="GET" action="<?php echo e(route('guru_bk.pengajaran-saya.index')); ?>"
                    class="flex items-end gap-2 shrink-0">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-500">Semester</label>
                        <select name="semester_id" onchange="this.form.submit()"
                            class="rounded-lg border border-gray-300 bg-white py-2 pl-3 pr-8 text-sm text-gray-700 shadow-sm focus:border-pink-600 focus:ring-2 focus:ring-pink-100">
                            <option value="">Semua Semester</option>
                            <?php $__currentLoopData = $semesterList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semester): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($semester->id); ?>"
                                    <?php echo e((string) $selectedSemesterId === (string) $semester->id ? 'selected' : ''); ?>>
                                    <?php echo e($semester->nama); ?><?php echo e($semester->is_active ? ' ✓' : ''); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <?php if($selectedSemesterId): ?>
                        <a href="<?php echo e(route('guru_bk.pengajaran-saya.index')); ?>"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-600 hover:bg-gray-50">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Reset
                        </a>
                    <?php endif; ?>
                </form>
            <?php endif; ?>
        </div>

        <?php if(!$guru): ?>
            <div class="overflow-hidden rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100">
                    <svg class="h-7 w-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <h2 class="text-base font-semibold text-gray-900">Data guru tidak ditemukan</h2>
                <p class="mt-1.5 text-sm text-gray-500">Hubungi administrator untuk melengkapi data guru pada akun ini.</p>
            </div>
        <?php else: ?>
            <?php if($activeSemester): ?>
                <div class="mb-5 flex items-center gap-2.5 rounded-lg border border-blue-100 bg-blue-50 px-4 py-2.5">
                    <span class="h-2 w-2 shrink-0 rounded-full bg-blue-500"></span>
                    <span class="text-sm text-blue-800">
                        Semester aktif: <strong><?php echo e($activeSemester->nama); ?></strong>
                        <span class="text-blue-600 text-xs ml-1">
                            (<?php echo e(\Carbon\Carbon::parse($activeSemester->tanggal_mulai)->translatedFormat('d M Y')); ?>

                            – <?php echo e(\Carbon\Carbon::parse($activeSemester->tanggal_selesai)->translatedFormat('d M Y')); ?>)
                        </span>
                    </span>
                </div>
            <?php else: ?>
                <div class="mb-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm text-amber-800">
                    ⚠ Belum ada semester aktif. Menampilkan semua data pengajaran.
                </div>
            <?php endif; ?>

            <?php
                $jadwalTotal = method_exists($jadwals, 'total') ? $jadwals->total() : $jadwals->count();
                $siswaTotal = $kelasDiajar->sum(fn($kelas) => ($kelas->siswas ?? collect())->count());
                $selectedSemester = $semesterList->firstWhere('id', $selectedSemesterId);
                $semesterLabel = $selectedSemester?->nama ?? 'Semua Semester';
            ?>

            
            <div class="mb-6 rounded-xl border border-gray-200 bg-white p-5">
                <div class="flex items-center justify-between gap-4 mb-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-pink-100 text-pink-700 font-bold text-sm shrink-0">
                            <?php echo e(strtoupper(substr($guru->nama, 0, 2))); ?>

                        </div>
                        <div>
                            <p class="font-semibold text-gray-900"><?php echo e($guru->nama); ?></p>
                            <p class="text-xs text-gray-500">NIP <?php echo e($guru->nip); ?></p>
                        </div>
                    </div>
                    <span
                        class="rounded-full border border-pink-200 bg-pink-50 px-3 py-1 text-xs font-medium text-pink-700">
                        <?php echo e($semesterLabel); ?>

                    </span>
                </div>
                <div style="display:flex; gap:12px;">
                    <div class="rounded-lg bg-gray-50 py-3 text-center" style="flex:1; min-width:0;">
                        <p class="text-lg font-bold text-gray-900"><?php echo e($kelasDiajar->count()); ?></p>
                        <p class="mt-0.5 text-gray-500" style="font-size:11px; line-height:1.3;">Kelas<br>Diajar</p>
                    </div>
                    <div class="rounded-lg bg-gray-50 py-3 text-center" style="flex:1; min-width:0;">
                        <p class="text-lg font-bold text-gray-900"><?php echo e($siswaTotal); ?></p>
                        <p class="mt-0.5 text-gray-500" style="font-size:11px; line-height:1.3;">Total<br>Siswa</p>
                    </div>
                    <div class="rounded-lg bg-gray-50 py-3 text-center" style="flex:1; min-width:0;">
                        <p class="text-lg font-bold text-gray-900"><?php echo e($jadwalTotal); ?></p>
                        <p class="mt-0.5 text-gray-500" style="font-size:11px; line-height:1.3;">Total<br>Jadwal</p>
                    </div>
                    <div class="rounded-lg py-3 text-center <?php echo e($jadwalHariIni->count() > 0 ? 'bg-pink-50' : 'bg-gray-50'); ?>"
                        style="flex:1; min-width:0;">
                        <p
                            class="text-lg font-bold <?php echo e($jadwalHariIni->count() > 0 ? 'text-pink-700' : 'text-gray-900'); ?>">
                            <?php echo e($jadwalHariIni->count()); ?>

                        </p>
                        <p class="mt-0.5 <?php echo e($jadwalHariIni->count() > 0 ? 'text-pink-600' : 'text-gray-500'); ?>"
                            style="font-size:11px; line-height:1.3;">Jadwal<br>Hari Ini</p>
                    </div>
                </div>
            </div>

            
            <?php if($jadwalHariIni->count() > 0): ?>
                <div class="mb-6 rounded-xl border border-pink-200 bg-pink-50 p-4">
                    <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-pink-800">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Mengajar Hari Ini — <?php echo e($todayHari); ?>

                    </h3>
                    <div class="flex flex-wrap gap-2">
                        <?php $__currentLoopData = $jadwalHariIni; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $j): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center gap-2.5 rounded-lg border border-pink-200 bg-white px-3 py-2">
                                <span
                                    class="font-mono text-xs font-semibold text-pink-700"><?php echo e(substr((string) $j->jam_mulai, 0, 5)); ?></span>
                                <span class="h-3 w-px bg-pink-200"></span>
                                <span class="text-xs font-medium text-gray-700"><?php echo e($j->mapel?->nama ?? '–'); ?></span>
                                <span
                                    class="rounded bg-pink-100 px-1.5 py-0.5 text-xs text-pink-700"><?php echo e($j->kelas?->nama_kelas ?? '–'); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endif; ?>

            
            <div x-data="{ activeTab: window.location.hash ? window.location.hash.substring(1) : 'kelas' }">

                <div class="mb-4 flex gap-1 rounded-xl border border-gray-200 bg-gray-100 p-1">
                    <?php
                        $tabs = [
                            [
                                'id' => 'kelas',
                                'label' => 'Kelas Diajar',
                                'count' => $kelasDiajar->count(),
                                'icon' =>
                                    'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                            ],
                            [
                                'id' => 'jadwal',
                                'label' => 'Jadwal Mengajar',
                                'count' => $jadwalTotal,
                                'icon' =>
                                    'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                            ],
                        ];
                    ?>
                    <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button @click="activeTab = '<?php echo e($tab['id']); ?>'; window.location.hash = '<?php echo e($tab['id']); ?>'"
                            :class="activeTab === '<?php echo e($tab['id']); ?>'
                                ?
                                'bg-white text-gray-900 shadow-sm font-semibold' :
                                'text-gray-500 hover:text-gray-700'"
                            class="flex flex-1 items-center justify-center gap-2 rounded-lg px-3 py-2 text-sm transition-all duration-150">
                            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="<?php echo e($tab['icon']); ?>" />
                            </svg>
                            <span class="hidden sm:inline"><?php echo e($tab['label']); ?></span>
                            <span
                                :class="activeTab === '<?php echo e($tab['id']); ?>' ? 'bg-pink-100 text-pink-700' :
                                        'bg-gray-200 text-gray-500'"
                                class="rounded-full px-1.5 py-0.5 text-xs font-semibold leading-none">
                                <?php echo e($tab['count']); ?>

                            </span>
                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                
                <div x-show="activeTab === 'kelas'" x-cloak>

                    
                    <div x-show="!selectedKelas">
                        <?php $__empty_1 = true; $__currentLoopData = $kelasDiajar; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kelas): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $kelasAssignments = $assignments->where('kelas_id', $kelas->id);
                                $semesterNames = $kelasAssignments
                                    ->pluck('semester.nama')
                                    ->filter()
                                    ->unique()
                                    ->sort()
                                    ->values();
                                $siswas = $kelas->siswas ?? collect();
                                $siswaPreview = $siswas->take(10);
                                $siswaMore = max(0, $siswas->count() - 10);

                                $jadwalKelas = $jadwals
                                    ->getCollection()
                                    ->where('kelas_id', $kelas->id)
                                    ->sortBy(
                                        fn($j) => match ($j->hari) {
                                            'Senin' => 1,
                                            'Selasa' => 2,
                                            'Rabu' => 3,
                                            'Kamis' => 4,
                                            'Jumat' => 5,
                                            'Sabtu' => 6,
                                            default => 99,
                                        },
                                    )
                                    ->sortBy('jam_mulai');
                            ?>

                            <div @click="selectedKelas = <?php echo e($kelas->id); ?>"
                                class="mb-4 overflow-hidden rounded-xl border border-gray-200 bg-white hover:border-pink-300 hover:shadow-sm transition-all cursor-pointer group">

                                
                                <div
                                    class="flex items-center justify-between gap-3 border-b border-gray-100 bg-gray-50 px-5 py-3.5">
                                    <div class="flex items-center gap-2.5">
                                        <div
                                            class="flex h-8 w-8 items-center justify-center rounded-full bg-pink-100 text-pink-700 font-bold text-xs shrink-0">
                                            <?php echo e(strtoupper(substr($kelas->nama_kelas, 0, 2))); ?>

                                        </div>
                                        <div>
                                            <h3
                                                class="font-semibold text-gray-900 group-hover:text-pink-700 transition-colors">
                                                <?php echo e($kelas->nama_kelas); ?>

                                            </h3>
                                            <p class="text-xs text-gray-400">
                                                <?php echo e($siswas->count()); ?> siswa · <?php echo e($jadwalKelas->count()); ?> sesi/minggu
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="flex flex-wrap gap-1.5">
                                            <?php $__currentLoopData = $semesterNames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sn): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <span
                                                    class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-500"><?php echo e($sn); ?></span>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                        <svg class="h-4 w-4 text-gray-400 group-hover:text-pink-600 transition-colors shrink-0 ml-1"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </div>
                                </div>

                                
                                <div class="px-5 py-4">
                                    <?php if($siswaPreview->isEmpty()): ?>
                                        <p class="text-sm text-gray-400 italic">Belum ada data siswa.</p>
                                    <?php else: ?>
                                        <div class="grid grid-cols-2 gap-x-6 gap-y-2 sm:grid-cols-3 lg:grid-cols-5">
                                            <?php $__currentLoopData = $siswaPreview; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $siswa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="flex items-center gap-2 min-w-0">
                                                    <span
                                                        class="w-5 text-right text-xs text-gray-300 shrink-0 font-mono"><?php echo e($i + 1); ?></span>
                                                    <div
                                                        class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-xs font-medium text-gray-600 shrink-0">
                                                        <?php echo e(strtoupper(substr($siswa->nama, 0, 1))); ?>

                                                    </div>
                                                    <span
                                                        class="text-xs text-gray-700 truncate"><?php echo e($siswa->nama); ?></span>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                        <?php if($siswaMore > 0): ?>
                                            <p class="mt-2.5 text-xs text-gray-400">+<?php echo e($siswaMore); ?> siswa lainnya</p>
                                        <?php endif; ?>
                                        <p class="mt-3 flex items-center gap-1 text-xs font-medium text-pink-700">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Lihat semua siswa &amp; jadwal kelas
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center">
                                <p class="font-medium text-gray-600">Belum ada kelas yang diajar</p>
                                <p class="mt-1 text-sm text-gray-400">Data kelas akan muncul setelah admin membuat
                                    penugasan BK.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    
                    <?php $__currentLoopData = $kelasDiajar; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kelas): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $kelasAssignments = $assignments->where('kelas_id', $kelas->id);
                            $semesterNames = $kelasAssignments
                                ->pluck('semester.nama')
                                ->filter()
                                ->unique()
                                ->sort()
                                ->values();
                            $siswas = ($kelas->siswas ?? collect())->sortBy('nama')->values();

                            $jadwalKelas = $jadwals->getCollection()->where('kelas_id', $kelas->id)->groupBy('hari');

                            $hariOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                        ?>

                        <div x-show="selectedKelas === <?php echo e($kelas->id); ?>" x-cloak>

                            
                            <button @click="selectedKelas = null"
                                class="mb-5 flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 transition-colors">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                                Kembali ke daftar kelas
                            </button>

                            
                            <div class="mb-6 flex items-center gap-3">
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-full bg-pink-100 text-pink-700 font-bold text-sm shrink-0">
                                    <?php echo e(strtoupper(substr($kelas->nama_kelas, 0, 2))); ?>

                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900"><?php echo e($kelas->nama_kelas); ?></h2>
                                    <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                        <?php $__currentLoopData = $semesterNames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sn): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <span
                                                class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500"><?php echo e($sn); ?></span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="mb-6 overflow-hidden rounded-xl border border-gray-200 bg-white">
                                <div
                                    class="flex items-center justify-between border-b border-gray-100 bg-gray-50 px-5 py-3.5">
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-900">Daftar Siswa</h3>
                                        <p class="text-xs text-gray-400 mt-0.5"><?php echo e($siswas->count()); ?> siswa terdaftar</p>
                                    </div>
                                </div>

                                <?php if($siswas->isEmpty()): ?>
                                    <div class="p-10 text-center text-sm text-gray-400">Belum ada data siswa untuk kelas
                                        ini.</div>
                                <?php else: ?>
                                    <div class="p-4 grid grid-cols-2 gap-2 sm:grid-cols-3 xl:grid-cols-4">
                                        <?php $__currentLoopData = $siswas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $siswa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div
                                                class="flex items-center gap-2.5 rounded-lg bg-gray-50 px-3 py-2.5 min-w-0">
                                                <span
                                                    class="w-5 text-right text-xs text-gray-300 shrink-0 font-mono"><?php echo e($i + 1); ?></span>
                                                <div
                                                    class="flex h-7 w-7 items-center justify-center rounded-full bg-white border border-gray-200 text-xs font-semibold text-gray-600 shrink-0">
                                                    <?php echo e(strtoupper(substr($siswa->nama, 0, 1))); ?>

                                                </div>
                                                <div class="min-w-0">
                                                    <p class="text-sm font-medium text-gray-800 truncate">
                                                        <?php echo e($siswa->nama); ?></p>
                                                    <?php if($siswa->nis): ?>
                                                        <p class="text-xs text-gray-400">NIS <?php echo e($siswa->nis); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            
                            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                                <div class="border-b border-gray-100 bg-gray-50 px-5 py-3.5">
                                    <h3 class="text-sm font-semibold text-gray-900">Jadwal Mengajar di Kelas Ini</h3>
                                    <p class="text-xs text-gray-400 mt-0.5">Per hari dan jam pelajaran</p>
                                </div>

                                <?php if($jadwalKelas->isEmpty()): ?>
                                    <div class="p-10 text-center">
                                        <p class="font-medium text-gray-500 text-sm">Belum ada jadwal untuk kelas ini</p>
                                        <p class="mt-1 text-xs text-gray-400">Jadwal akan muncul setelah admin menambahkan
                                            data.</p>
                                    </div>
                                <?php else: ?>
                                    <?php $__currentLoopData = $hariOrder; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hari): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(isset($jadwalKelas[$hari])): ?>
                                            <div class="border-t border-gray-100 first:border-t-0">
                                                
                                                <div
                                                    class="flex items-center gap-2 px-5 py-2.5
                                                <?php echo e($hari === $todayHari ? 'bg-pink-50' : 'bg-gray-50'); ?>">
                                                    <span
                                                        class="text-xs font-semibold uppercase tracking-wide
                                                <?php echo e($hari === $todayHari ? 'text-pink-700' : 'text-gray-500'); ?>">
                                                        <?php echo e($hari); ?>

                                                    </span>
                                                    <?php if($hari === $todayHari): ?>
                                                        <span
                                                            class="rounded-full bg-pink-100 px-2 py-0.5 text-xs font-semibold text-pink-700">Hari
                                                            Ini</span>
                                                    <?php endif; ?>
                                                </div>

                                                
                                                <?php $__currentLoopData = $jadwalKelas[$hari]->sortBy('jam_mulai'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jadwal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <div
                                                        class="flex flex-col gap-2 border-t border-gray-100 px-5 py-3.5 hover:bg-gray-50
                                                    sm:flex-row sm:items-center sm:gap-5
                                                    <?php echo e(!$jadwal->is_active ? 'opacity-50' : ''); ?>">

                                                        
                                                        <div class="flex items-center gap-2.5 shrink-0 sm:w-32">
                                                            <div
                                                                class="flex h-8 w-8 items-center justify-center rounded-lg
                                                            <?php echo e($hari === $todayHari ? 'bg-pink-100' : 'bg-gray-100'); ?> shrink-0">
                                                                <svg class="h-4 w-4 <?php echo e($hari === $todayHari ? 'text-pink-600' : 'text-gray-400'); ?>"
                                                                    fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                            </div>
                                                            <div>
                                                                <p class="font-mono text-sm font-semibold text-gray-900">
                                                                    <?php echo e(substr((string) $jadwal->jam_mulai, 0, 5)); ?>

                                                                </p>
                                                                <p class="font-mono text-xs text-gray-400">
                                                                    <?php echo e(substr((string) $jadwal->jam_selesai, 0, 5)); ?>

                                                                </p>
                                                            </div>
                                                        </div>

                                                        
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-sm font-medium text-gray-900">
                                                                <?php echo e($jadwal->mapel?->nama ?? '–'); ?></p>
                                                            <?php if($jadwal->semester): ?>
                                                                <p class="text-xs text-gray-400 mt-0.5">
                                                                    <?php echo e($jadwal->semester->nama); ?></p>
                                                            <?php endif; ?>
                                                        </div>

                                                        
                                                        <span
                                                            class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold
                                                    <?php echo e($jadwal->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'); ?>">
                                                            <?php echo e($jadwal->is_active ? 'Aktif' : 'Nonaktif'); ?>

                                                        </span>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </div>

                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                </div>

                
                <div x-show="activeTab === 'jadwal'" x-cloak>
                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                        <?php $__empty_1 = true; $__currentLoopData = $jadwalPerHari; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hari => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="border-t border-gray-100 first:border-t-0">
                                <div
                                    class="flex items-center gap-2 px-5 py-2.5 <?php echo e($hari === $todayHari ? 'bg-pink-50' : 'bg-gray-50'); ?>">
                                    <span
                                        class="text-xs font-semibold uppercase tracking-wide <?php echo e($hari === $todayHari ? 'text-pink-700' : 'text-gray-500'); ?>">
                                        <?php echo e($hari); ?>

                                    </span>
                                    <span
                                        class="rounded-full <?php echo e($hari === $todayHari ? 'bg-pink-100 text-pink-700' : 'bg-gray-200 text-gray-500'); ?> px-2 py-0.5 text-xs font-medium">
                                        <?php echo e($items->count()); ?> sesi
                                    </span>
                                    <?php if($hari === $todayHari): ?>
                                        <span
                                            class="rounded-full bg-pink-100 px-2 py-0.5 text-xs font-semibold text-pink-700">Hari
                                            Ini</span>
                                    <?php endif; ?>
                                </div>

                                <div class="divide-y divide-gray-100">
                                    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jadwal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div
                                            class="flex flex-col gap-2 px-5 py-3.5 hover:bg-gray-50
                                        sm:flex-row sm:items-center sm:gap-4 <?php echo e(!$jadwal->is_active ? 'opacity-50' : ''); ?>">
                                            <div class="flex items-center gap-2.5 shrink-0 sm:w-28">
                                                <div
                                                    class="flex h-8 w-8 items-center justify-center rounded-lg <?php echo e($hari === $todayHari ? 'bg-pink-100' : 'bg-gray-100'); ?> shrink-0">
                                                    <svg class="h-4 w-4 <?php echo e($hari === $todayHari ? 'text-pink-600' : 'text-gray-400'); ?>"
                                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="font-mono text-sm font-semibold text-gray-900">
                                                        <?php echo e(substr((string) $jadwal->jam_mulai, 0, 5)); ?></p>
                                                    <p class="font-mono text-xs text-gray-400">
                                                        <?php echo e(substr((string) $jadwal->jam_selesai, 0, 5)); ?></p>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-medium text-gray-900 truncate">
                                                    <?php echo e($jadwal->mapel?->nama ?? '–'); ?></p>
                                                <p class="text-sm text-gray-500"><?php echo e($jadwal->kelas?->nama_kelas ?? '–'); ?>

                                                </p>
                                            </div>
                                            <div class="flex items-center gap-2 shrink-0">
                                                <span
                                                    class="rounded-full px-2.5 py-1 text-xs font-medium
                                            <?php echo e($jadwal->semester?->is_active ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500'); ?>">
                                                    <?php echo e($jadwal->semester?->nama ?? '–'); ?>

                                                </span>
                                                <span
                                                    class="rounded-full px-2.5 py-1 text-xs font-semibold
                                            <?php echo e($jadwal->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'); ?>">
                                                    <?php echo e($jadwal->is_active ? 'Aktif' : 'Nonaktif'); ?>

                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="p-12 text-center">
                                <p class="font-medium text-gray-600">Belum ada jadwal mengajar</p>
                                <p class="mt-1 text-sm text-gray-400">Jadwal akan muncul setelah admin menambahkan
                                    data.</p>
                            </div>
                        <?php endif; ?>

                        <?php if(method_exists($jadwals, 'hasPages') && $jadwals->hasPages()): ?>
                            <div
                                class="flex flex-col items-center justify-between gap-3 border-t border-gray-200 bg-gray-50 px-5 py-3.5 sm:flex-row">
                                <p class="text-xs text-gray-500">
                                    Menampilkan
                                    <span
                                        class="font-semibold text-gray-700"><?php echo e($jadwals->firstItem()); ?>–<?php echo e($jadwals->lastItem()); ?></span>
                                    dari <span class="font-semibold text-gray-700"><?php echo e($jadwals->total()); ?></span> jadwal
                                </p>
                                <div class="flex items-center gap-1">
                                    <?php if($jadwals->onFirstPage()): ?>
                                        <span
                                            class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-300 cursor-default">‹
                                            Prev</span>
                                    <?php else: ?>
                                        <a href="<?php echo e($jadwals->previousPageUrl()); ?>"
                                            class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100">‹
                                            Prev</a>
                                    <?php endif; ?>
                                    <?php
                                        $cp = $jadwals->currentPage();
                                        $lp = $jadwals->lastPage();
                                        $s = max(1, $cp - 2);
                                        $e = min($lp, $cp + 2);
                                    ?>
                                    <?php if($s > 1): ?>
                                        <a href="<?php echo e($jadwals->url(1)); ?>"
                                            class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100">1</a>
                                        <?php if($s > 2): ?>
                                            <span class="px-1 text-xs text-gray-400">…</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php $__currentLoopData = $jadwals->getUrlRange($s, $e); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if($page === $cp): ?>
                                            <span
                                                class="rounded-md border border-pink-600 bg-pink-700 px-3 py-1.5 text-xs font-semibold text-white"><?php echo e($page); ?></span>
                                        <?php else: ?>
                                            <a href="<?php echo e($url); ?>"
                                                class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100"><?php echo e($page); ?></a>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($e < $lp): ?>
                                        <?php if($e < $lp - 1): ?>
                                            <span class="px-1 text-xs text-gray-400">…</span>
                                        <?php endif; ?>
                                        <a href="<?php echo e($jadwals->url($lp)); ?>"
                                            class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100"><?php echo e($lp); ?></a>
                                    <?php endif; ?>
                                    <?php if($jadwals->hasMorePages()): ?>
                                        <a href="<?php echo e($jadwals->nextPageUrl()); ?>"
                                            class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100">Next
                                            ›</a>
                                    <?php else: ?>
                                        <span
                                            class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-300 cursor-default">Next
                                            ›</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        <?php endif; ?>
    </div>

    <script>
        function pengajaranSaya() {
            return {
                selectedKelas: null,
            }
        }
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\projects\ews-smansa\resources\views/guru_bk/pengajaran_saya/index.blade.php ENDPATH**/ ?>