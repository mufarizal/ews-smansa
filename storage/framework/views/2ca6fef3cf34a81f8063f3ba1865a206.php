<?php if(isset($_group, $_items)): ?>
    
    <?php $currentUrl = url()->current(); ?>

    <div class="mb-1">
        <p class="px-2 pt-4 pb-1 text-[10px] font-bold uppercase tracking-widest text-white/30">
            <?php echo e($_group); ?>

        </p>
        <ul>
            <?php $__currentLoopData = $_items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $_item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $isActive = $_item['route'] !== '#' && str_starts_with($currentUrl, $_item['route']);
                ?>
                <li>
                    <a href="<?php echo e($_item['route']); ?>"
                        class="flex items-center gap-2.5 rounded-md px-2.5 py-2 text-sm transition-colors
                              <?php echo e($isActive
                                  ? 'border-l-2 border-yellow-400 bg-green-800 font-semibold text-white'
                                  : 'border-l-2 border-transparent font-normal text-white/60 hover:bg-white/5 hover:text-white/90'); ?>">
                        <i
                            class="ti ti-<?php echo e($_item['icon']); ?> text-base <?php echo e($isActive ? 'opacity-100' : 'opacity-60'); ?>"></i>
                        <?php echo e($_item['label']); ?>

                    </a>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php else: ?>
    
    <aside id="sidebar"
        class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-green-950 text-white transition-transform duration-300 md:translate-x-0">

        
        <div class="flex items-center gap-3 border-b border-white/10 bg-green-900 px-5 py-4">
            <img src="<?php echo e(asset('img/logo.png')); ?>" alt="Logo" class="h-10 w-10 shrink-0 object-contain"
                onerror="this.classList.add('hidden')">
            <div>
                <p class="text-sm font-bold leading-tight text-white">SMAN 1 Cikarang Selatan</p>
                <p class="text-[10px] text-white/40">Kab. Bekasi</p>
            </div>
        </div>

        
        <?php
            $roleMeta = [
                'admin' => ['label' => 'Admin', 'class' => 'bg-yellow-400 text-green-950'],
                'kurikulum' => ['label' => 'Kurikulum', 'class' => 'bg-violet-400 text-violet-950'],
                'guru_mapel' => ['label' => 'Guru Mapel', 'class' => 'bg-sky-400 text-sky-950'],
                'wali_kelas' => ['label' => 'Wali Kelas', 'class' => 'bg-emerald-400 text-emerald-950'],
                'guru_piket' => ['label' => 'Guru Piket', 'class' => 'bg-orange-400 text-orange-950'],
                'siswa' => ['label' => 'Siswa', 'class' => 'bg-rose-400 text-rose-950'],
                'guru_bk' => ['label' => 'Guru BK', 'class' => 'bg-pink-400 text-pink-950'],
            ];
            $activeRoleKey =
                session('active_role') ??
                (auth()->user()->default_role ?? optional(auth()->user()->roles->first())->slug);
            $badge = $roleMeta[$activeRoleKey] ?? ['label' => 'User', 'class' => 'bg-stone-400 text-stone-950'];
            $userRoles = auth()->user()->roles;
            $multiRole = $userRoles->count() > 1;
        ?>

        <div class="border-b border-white/10">
            <?php if($multiRole): ?>
                
                <button onclick="toggleRoleMenu()" id="role-btn" aria-expanded="false"
                    class="flex w-full items-center justify-between px-4 py-2.5 transition hover:bg-white/5">
                    <div class="flex items-center gap-2">
                        <span
                            class="rounded px-2 py-0.5 text-[10px] font-bold uppercase tracking-widest <?php echo e($badge['class']); ?>">
                            <?php echo e($badge['label']); ?>

                        </span>
                        <span class="text-[11px] text-white/40">aktif</span>
                    </div>
                    <i class="ti ti-chevron-down text-sm text-white/35 transition-transform duration-200"
                        id="role-chevron"></i>
                </button>

                
                <div id="role-menu" class="overflow-hidden transition-all duration-200 ease-in-out"
                    style="max-height: 0;">
                    <div
                        class="max-h-[126px] overflow-y-auto
                                [&::-webkit-scrollbar]:w-1
                                [&::-webkit-scrollbar-thumb]:rounded
                                [&::-webkit-scrollbar-thumb]:bg-white/20
                                [&::-webkit-scrollbar-track]:bg-transparent">
                        <?php $__currentLoopData = $userRoles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $rm = $roleMeta[$role->slug] ?? [
                                    'label' => $role->name,
                                    'class' => 'bg-stone-400 text-stone-950',
                                ];
                                $isCurrentRole = $role->slug === $activeRoleKey;
                            ?>
                            <form method="POST" action="<?php echo e(route('role.switch')); ?>">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="role" value="<?php echo e($role->slug); ?>">
                                <button type="submit"
                                    class="flex w-full items-center gap-2.5 border-l-2 px-4 py-2.5 text-left text-sm transition
                                           <?php echo e($isCurrentRole
                                               ? 'border-yellow-400 bg-white/5 text-white'
                                               : 'border-transparent text-white/55 hover:bg-white/5 hover:text-white/85'); ?>">
                                    <span
                                        class="h-1.5 w-1.5 shrink-0 rounded-full
                                                 <?php echo e($isCurrentRole ? 'bg-yellow-400' : 'bg-transparent'); ?>"></span>
                                    <span
                                        class="inline-block rounded px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide <?php echo e($rm['class']); ?>">
                                        <?php echo e($rm['label']); ?>

                                    </span>
                                    <?php if($isCurrentRole): ?>
                                        <i class="ti ti-check ml-auto text-sm text-yellow-400"></i>
                                    <?php endif; ?>
                                </button>
                            </form>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="px-4 py-2.5">
                    <span
                        class="inline-block rounded px-2 py-0.5 text-[10px] font-bold uppercase tracking-widest <?php echo e($badge['class']); ?>">
                        <?php echo e($badge['label']); ?>

                    </span>
                </div>
            <?php endif; ?>
        </div>

        
        <nav class="flex-1 overflow-y-auto px-3 py-2">

            <?php if($activeRoleKey === 'admin'): ?>
                <?php echo $__env->make('partials.sidebar', [
                    '_group' => 'Administrasi',
                    '_items' => [
                        [
                            'icon' => 'layout-dashboard',
                            'label' => 'Dashboard',
                            'route' => route('admin.dashboard'),
                        ],
                        [
                            'icon' => 'user-cog',
                            'label' => 'Manajemen User',
                            'route' => route('admin.users.index'),
                        ],
                    ],
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endif; ?>

            <?php if($activeRoleKey === 'kurikulum'): ?>
                <?php echo $__env->make('partials.sidebar', [
                    '_group' => 'Kurikulum',
                    '_items' => [
                        [
                            'icon' => 'layout-dashboard',
                            'label' => 'Dashboard',
                            'route' => route('kurikulum.dashboard'),
                        ],
                        [
                            'icon' => 'calendar',
                            'label' => 'Manajemen Semester',
                            'route' => route('kurikulum.semesters.index'),
                        ],
                        [
                            'icon' => 'list-tree',
                            'label' => 'Manajemen Kelas',
                            'route' => route('kurikulum.kelas.index'),
                        ],
                        [
                            'icon' => 'books',
                            'label' => 'Mata Pelajaran',
                            'route' => route('kurikulum.mapel.index'),
                        ],
                        [
                            'icon' => 'users',
                            'label' => 'Manajemen Siswa',
                            'route' => route('kurikulum.siswa.index'),
                        ],
                        [
                            'icon' => 'user-pause',
                            'label' => 'Manajemen Guru',
                            'route' => route('kurikulum.guru.index'),
                        ],
                        [
                            'icon' => 'git-branch',
                            'label' => 'Penugasan Guru',
                            'route' => route('kurikulum.penugasan-guru.mapel.index'),
                        ],
                        [
                            'icon' => 'calendar-clock',
                            'label' => 'Jadwal Pelajaran',
                            'route' => route('kurikulum.jadwal.index'),
                        ],
                        [
                            'icon' => 'book',
                            'label' => 'Perangkat Ajar',
                            'route' => '#',
                        ],
                    ],
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endif; ?>

            <?php if($activeRoleKey === 'guru_mapel'): ?>
                <?php echo $__env->make('partials.sidebar', [
                    '_group' => 'Guru Mata Pelajaran',
                    '_items' => [
                        [
                            'icon' => 'layout-dashboard',
                            'label' => 'Dashboard',
                            'route' => route('guru_mapel.dashboard'),
                        ],
                        [
                            'icon' => 'school',
                            'label' => 'Pengajaran Saya',
                            'route' => route('guru_mapel.pengajaran-saya.index'),
                        ],
                        [
                            'icon' => 'calendar-clock',
                            'label' => 'Absensi Mapel',
                            'route' => route('guru_mapel.absensi.index'),
                        ],
                        [
                            'icon' => 'book-2',
                            'label' => 'Bab & Materi',
                            'route' => route('guru_mapel.bab.index'),
                        ],
                        [
                            'icon' => 'pencil',
                            'label' => 'Tugas',
                            'route' => route('guru_mapel.tugas.index'),
                        ],
                        [
                            'icon' => 'file-text',
                            'label' => 'Ujian Harian',
                            'route' => route('guru_mapel.ujian.index'),
                        ],
                        [
                            'icon' => 'alert-triangle',
                            'label' => 'Perilaku Siswa',
                            'route' => route('guru_mapel.perilaku-siswa.index'),
                        ],
                    ],
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endif; ?>

            <?php if($activeRoleKey === 'wali_kelas'): ?>
                <?php echo $__env->make('partials.sidebar', [
                    '_group' => 'Wali Kelas',
                    '_items' => [
                        [
                            'icon' => 'layout-dashboard',
                            'label' => 'Dashboard',
                            'route' => route('wali_kelas.dashboard'),
                        ],
                        [
                            'icon' => 'users',
                            'label' => 'Monitoring Siswa',
                            'route' => route('wali_kelas.kelas-saya.index'),
                        ],
                        [
                            'icon' => 'alert-triangle',
                            'label' => 'Perilaku Siswa',
                            'route' => route('wali_kelas.perilaku-siswa.index'),
                        ],
                    ],
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endif; ?>

            <?php if($activeRoleKey === 'guru_piket'): ?>
                <?php echo $__env->make('partials.sidebar', [
                    '_group' => 'Guru Piket',
                    '_items' => [
                        [
                            'icon' => 'layout-dashboard',
                            'label' => 'Dashboard',
                            'route' => route('guru_piket.dashboard'),
                        ],
                        [
                            'icon' => 'qrcode',
                            'label' => 'Barcode Absensi',
                            'route' => route('guru_piket.qr'),
                        ],
                        [
                            'icon' => 'clock',
                            'label' => 'Riwayat Absensi',
                            'route' => route('guru_piket.attendance.history'),
                        ],
                    ],
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endif; ?>

                    <?php if($activeRoleKey === 'guru_bk'): ?>
                <?php echo $__env->make('partials.sidebar', [
                    '_group' => 'Guru BK',
                    '_items' => [
                        [
                            'icon' => 'layout-dashboard',
                            'label' => 'Dashboard',
                            'route' => route('guru_bk.dashboard'),
                        ],
                        [
                            'icon' => 'chart-line',
                            'label' => 'Monitoring SAW',
                            'route' => route('guru_bk.monitoring.index'),
                        ],
                        [
                            'icon' => 'school',
                            'label' => 'Pengajaran Saya',
                            'route' => route('guru_bk.pengajaran-saya.index'),
                        ],
                        [
                            'icon' => 'clipboard-list',
                            'label' => 'Point Perilaku',
                            'route' => route('guru_bk.point-perilaku.index'),
                        ],
                    ],
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endif; ?>

            <?php if($activeRoleKey === 'siswa'): ?>
                <?php echo $__env->make('partials.sidebar', [
                    '_group' => 'Siswa',
                    '_items' => [
                        [
                            'icon' => 'layout-dashboard',
                            'label' => 'Dashboard',
                            'route' => route('siswa.dashboard'),
                        ],
                        [
                            'icon' => 'user',
                            'label' => 'Profil Saya',
                            'route' => route('siswa.profil.index'),
                        ],
                        [
                            'icon' => 'scan',
                            'label' => 'Scan QR Absensi',
                            'route' => route('siswa.qr.scan'),
                        ],
                        [
                            'icon' => 'pencil',
                            'label' => 'Tugas',
                            'route' => route('siswa.tugas.index'),
                        ],
                        [
                            'icon' => 'file-text',
                            'label' => 'Ujian Harian',
                            'route' => route('siswa.ujian.index'),
                        ],
                    ],
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endif; ?>

        </nav>

        
        <div class="border-t border-white/10 px-5 py-3 text-[10px] font-mono text-white/25">
            EWS v1.0
        </div>

    </aside>

    <script>
        function toggleRoleMenu() {
            const menu = document.getElementById('role-menu');
            const chevron = document.getElementById('role-chevron');
            const btn = document.getElementById('role-btn');
            const isOpen = menu.style.maxHeight !== '0px' && menu.style.maxHeight !== '';

            if (isOpen) {
                menu.style.maxHeight = '0';
                chevron.style.transform = 'rotate(0deg)';
                btn.setAttribute('aria-expanded', 'false');
            } else {
                menu.style.maxHeight = menu.scrollHeight + 'px';
                chevron.style.transform = 'rotate(180deg)';
                btn.setAttribute('aria-expanded', 'true');
            }
        }

        document.addEventListener('click', function(e) {
            const btn = document.getElementById('role-btn');
            const menu = document.getElementById('role-menu');
            if (!menu || !btn) return;
            const isOpen = menu.style.maxHeight !== '0px' && menu.style.maxHeight !== '';
            if (isOpen && !btn.contains(e.target) && !menu.contains(e.target)) {
                menu.style.maxHeight = '0';
                document.getElementById('role-chevron').style.transform = 'rotate(0deg)';
                btn.setAttribute('aria-expanded', 'false');
            }
        });
    </script>
<?php endif; ?>
<?php /**PATH C:\projects\ews-smansa\resources\views/partials/sidebar.blade.php ENDPATH**/ ?>