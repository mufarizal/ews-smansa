@if (isset($_group, $_items))
    {{-- ── NAV GROUP RENDERER ── --}}
    @php $currentUrl = url()->current(); @endphp

    <div class="mb-1">
        <p class="px-2 pt-4 pb-1 text-[10px] font-bold uppercase tracking-widest text-white/30">
            {{ $_group }}
        </p>
        <ul>
            @foreach ($_items as $_item)
                @php
                    $isActive = $_item['route'] !== '#' && str_starts_with($currentUrl, $_item['route']);
                @endphp
                <li>
                    <a href="{{ $_item['route'] }}"
                        class="flex items-center gap-2.5 rounded-md px-2.5 py-2 text-sm transition-colors
                              {{ $isActive
                                  ? 'border-l-2 border-yellow-400 bg-green-800 font-semibold text-white'
                                  : 'border-l-2 border-transparent font-normal text-white/60 hover:bg-white/5 hover:text-white/90' }}">
                        <i
                            class="ti ti-{{ $_item['icon'] }} text-base {{ $isActive ? 'opacity-100' : 'opacity-60' }}"></i>
                        {{ $_item['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@else
    {{-- ── FULL SIDEBAR ── --}}
    <aside id="sidebar"
        class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-green-950 text-white transition-transform duration-300 md:translate-x-0">

        {{-- BRAND --}}
        <div class="flex items-center gap-3 border-b border-white/10 bg-green-900 px-5 py-4">
            <img src="{{ asset('img/logo.png') }}" alt="Logo" class="h-10 w-10 shrink-0 object-contain"
                onerror="this.classList.add('hidden')">
            <div>
                <p class="text-sm font-bold leading-tight text-white">SMAN 1 Cikarang Selatan</p>
                <p class="text-[10px] text-white/40">Kab. Bekasi</p>
            </div>
        </div>

        {{-- ROLE BADGE / SWITCHER --}}
        @php
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
        @endphp

        <div class="border-b border-white/10">
            @if ($multiRole)
                {{-- Toggle button --}}
                <button onclick="toggleRoleMenu()" id="role-btn" aria-expanded="false"
                    class="flex w-full items-center justify-between px-4 py-2.5 transition hover:bg-white/5">
                    <div class="flex items-center gap-2">
                        <span
                            class="rounded px-2 py-0.5 text-[10px] font-bold uppercase tracking-widest {{ $badge['class'] }}">
                            {{ $badge['label'] }}
                        </span>
                        <span class="text-[11px] text-white/40">aktif</span>
                    </div>
                    <i class="ti ti-chevron-down text-sm text-white/35 transition-transform duration-200"
                        id="role-chevron"></i>
                </button>

                {{-- Dropdown — max 3 item visible, sisanya scroll --}}
                <div id="role-menu" class="overflow-hidden transition-all duration-200 ease-in-out"
                    style="max-height: 0;">
                    <div
                        class="max-h-[126px] overflow-y-auto
                                [&::-webkit-scrollbar]:w-1
                                [&::-webkit-scrollbar-thumb]:rounded
                                [&::-webkit-scrollbar-thumb]:bg-white/20
                                [&::-webkit-scrollbar-track]:bg-transparent">
                        @foreach ($userRoles as $role)
                            @php
                                $rm = $roleMeta[$role->slug] ?? [
                                    'label' => $role->name,
                                    'class' => 'bg-stone-400 text-stone-950',
                                ];
                                $isCurrentRole = $role->slug === $activeRoleKey;
                            @endphp
                            <form method="POST" action="{{ route('role.switch') }}">
                                @csrf
                                <input type="hidden" name="role" value="{{ $role->slug }}">
                                <button type="submit"
                                    class="flex w-full items-center gap-2.5 border-l-2 px-4 py-2.5 text-left text-sm transition
                                           {{ $isCurrentRole
                                               ? 'border-yellow-400 bg-white/5 text-white'
                                               : 'border-transparent text-white/55 hover:bg-white/5 hover:text-white/85' }}">
                                    <span
                                        class="h-1.5 w-1.5 shrink-0 rounded-full
                                                 {{ $isCurrentRole ? 'bg-yellow-400' : 'bg-transparent' }}"></span>
                                    <span
                                        class="inline-block rounded px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide {{ $rm['class'] }}">
                                        {{ $rm['label'] }}
                                    </span>
                                    @if ($isCurrentRole)
                                        <i class="ti ti-check ml-auto text-sm text-yellow-400"></i>
                                    @endif
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="px-4 py-2.5">
                    <span
                        class="inline-block rounded px-2 py-0.5 text-[10px] font-bold uppercase tracking-widest {{ $badge['class'] }}">
                        {{ $badge['label'] }}
                    </span>
                </div>
            @endif
        </div>

        {{-- NAV --}}
        <nav class="flex-1 overflow-y-auto px-3 py-2">

            @if ($activeRoleKey === 'admin')
                @include('partials.sidebar', [
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
                ])
            @endif

            @if ($activeRoleKey === 'kurikulum')
                @include('partials.sidebar', [
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
                ])
            @endif

            @if ($activeRoleKey === 'guru_mapel')
                @include('partials.sidebar', [
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
                ])
            @endif

            @if ($activeRoleKey === 'wali_kelas')
                @include('partials.sidebar', [
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
                ])
            @endif

            @if ($activeRoleKey === 'guru_piket')
                @include('partials.sidebar', [
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
                ])
            @endif

                    @if ($activeRoleKey === 'guru_bk')
                @include('partials.sidebar', [
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
                ])
            @endif

            @if ($activeRoleKey === 'siswa')
                @include('partials.sidebar', [
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
                ])
            @endif

        </nav>

        {{-- FOOTER --}}
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
@endif
