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

        <div class="relative px-4 pt-3 pb-2">
            @if ($multiRole)
                <button onclick="toggleRoleMenu()" id="role-btn"
                    class="flex w-full items-center justify-between rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-left transition hover:bg-white/10">
                    <div class="flex items-center gap-2">
                        <span
                            class="rounded px-2 py-0.5 text-[10px] font-bold uppercase tracking-widest {{ $badge['class'] }}">
                            {{ $badge['label'] }}
                        </span>
                        <span class="text-xs text-white/50">aktif</span>
                    </div>
                    <i class="ti ti-selector text-sm text-white/40"></i>
                </button>

                <div id="role-menu"
                    class="absolute left-4 right-4 top-full z-50 mt-1 hidden overflow-hidden rounded-lg border border-white/10 bg-green-900 shadow-xl">
                    <p class="px-3 pt-2.5 pb-1 text-[10px] font-bold uppercase tracking-widest text-white/30">
                        Pindah ke Role
                    </p>
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
                                class="flex w-full items-center gap-2.5 px-3 py-2.5 text-left text-sm transition
                                       {{ $isCurrentRole ? 'bg-green-800/60 text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                                <span
                                    class="h-2 w-2 shrink-0 rounded-full {{ $isCurrentRole ? 'bg-yellow-400' : 'bg-transparent' }}"></span>
                                <span
                                    class="inline-block rounded px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $rm['class'] }}">
                                    {{ $rm['label'] }}
                                </span>
                                @if ($isCurrentRole)
                                    <i class="ti ti-check ml-auto text-sm text-yellow-400"></i>
                                @endif
                            </button>
                        </form>
                    @endforeach
                </div>
            @else
                <span
                    class="inline-block rounded px-2 py-0.5 text-[10px] font-bold uppercase tracking-widest {{ $badge['class'] }}">
                    {{ $badge['label'] }}
                </span>
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
                
                        // ── Data Master ──────────────────────────────
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
                
                        // ── SDM ──────────────────────────────────────
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
                
                        // ── Operasional ──────────────────────────────
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
                            'icon' => 'pencil',
                            'label' => 'Input Nilai',
                            'route' => '#',
                        ],
                        [
                            'icon' => 'list',
                            'label' => 'Rekap Nilai',
                            'route' => '#',
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
                            'label' => 'Kelas Saya',
                            'route' => route('wali_kelas.kelas-saya.index'),
                        ],
                        [
                            'icon' => 'bell',
                            'label' => 'Peringatan',
                            'route' => '#',
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
                            'icon' => 'school',
                            'label' => 'Pengajaran Saya',
                            'route' => route('guru_bk.pengajaran-saya.index'),
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
                            'icon' => 'scan',
                            'label' => 'Scan QR Absensi',
                            'route' => route('siswa.qr.scan'),
                        ],
                        [
                            'icon' => 'clock',
                            'label' => 'Absensi',
                            'route' => '#',
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
            document.getElementById('role-menu').classList.toggle('hidden');
        }
        document.addEventListener('click', function(e) {
            const btn = document.getElementById('role-btn');
            const menu = document.getElementById('role-menu');
            if (menu && btn && !btn.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });
    </script>
@endif
