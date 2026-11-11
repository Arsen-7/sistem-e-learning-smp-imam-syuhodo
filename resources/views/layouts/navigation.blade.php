@php
    $user = Auth::user();
    $unreadNotificationsCount = $user?->unreadAppNotifications()->count() ?? 0;

    $navItems = [];

    if ($user?->role === 'admin') {
        $navItems = [
            ['label' => 'Dashboard Admin', 'href' => route('admin.dashboard'), 'active' => request()->routeIs('admin.dashboard'), 'icon' => 'dashboard'],
            ['label' => 'Data Pengguna', 'href' => route('admin.users.index'), 'active' => request()->routeIs('admin.users.*') && ! request('role'), 'icon' => 'users'],
            ['label' => 'Hak Akses', 'href' => route('admin.access.index'), 'active' => request()->routeIs('admin.access.*'), 'icon' => 'profile'],
            ['label' => 'Data Kelas', 'href' => route('admin.classes.index'), 'active' => request()->routeIs('admin.classes.*'), 'icon' => 'class'],
            ['label' => 'Mata Pelajaran', 'href' => route('admin.subjects.index'), 'active' => request()->routeIs('admin.subjects.*'), 'icon' => 'book'],
            ['label' => 'Pengampu', 'href' => route('admin.teacher-subjects.index'), 'active' => request()->routeIs('admin.teacher-subjects.*'), 'icon' => 'teacher'],
            ['label' => 'Bobot', 'href' => route('admin.grade-weights.index'), 'active' => request()->routeIs('admin.grade-weights.*'), 'icon' => 'weights'],
            ['label' => 'Setting Akademik', 'href' => route('admin.academic-settings.index'), 'active' => request()->routeIs('admin.academic-settings.*'), 'icon' => 'weights'],
            ['label' => 'Arsip Akademik', 'href' => route('admin.academic-archives.index'), 'active' => request()->routeIs('admin.academic-archives.*'), 'icon' => 'report'],
            ['label' => 'Alumni', 'href' => route('admin.alumni.index'), 'active' => request()->routeIs('admin.alumni.*'), 'icon' => 'users'],
            ['label' => 'Pengumuman', 'href' => route('admin.announcements.index'), 'active' => request()->routeIs('admin.announcements.*'), 'icon' => 'announcement'],
        ];
    } elseif ($user?->role === 'guru') {
        $navItems = [
            ['label' => 'Dashboard Guru', 'href' => route('teacher.dashboard'), 'active' => request()->routeIs('teacher.dashboard'), 'icon' => 'dashboard'],
            ['label' => 'Materi', 'href' => route('teacher.materials.index'), 'active' => request()->routeIs('teacher.materials.*'), 'icon' => 'book'],
            ['label' => 'Tugas', 'href' => route('teacher.assignments.index'), 'active' => request()->routeIs('teacher.assignments.*') && ! request()->routeIs('teacher.assignments.submissions.*') && ! request()->routeIs('teacher.assignments.show'), 'icon' => 'assignment'],
            ['label' => 'Pengumpulan Tugas', 'href' => route('teacher.assignments.submissions.index'), 'active' => request()->routeIs('teacher.assignments.submissions.*') || request()->routeIs('teacher.assignments.show'), 'icon' => 'assignment'],
            ['label' => 'Input Nilai', 'href' => route('teacher.grades.index'), 'active' => request()->routeIs('teacher.grades.*'), 'icon' => 'grade'],
            ['label' => 'Pengumuman', 'href' => route('teacher.announcements.index'), 'active' => request()->routeIs('teacher.announcements.*'), 'icon' => 'announcement'],
            ['label' => 'Bobot', 'href' => route('teacher.grade-weights.index'), 'active' => request()->routeIs('teacher.grade-weights.*'), 'icon' => 'weights'],
            ['label' => 'Dashboard Analitik', 'href' => route('teacher.analytics.index'), 'active' => request()->routeIs('teacher.analytics.*'), 'icon' => 'analytics'],
            ['label' => 'Arsip Mengajar', 'href' => route('teacher.teaching-archives.index'), 'active' => request()->routeIs('teacher.teaching-archives.*'), 'icon' => 'report'],
            ['label' => 'Profil Guru', 'href' => route('profile.edit'), 'active' => request()->routeIs('profile.*'), 'icon' => 'profile'],
        ];

        if ($user->teacher?->homeroomClasses()->exists()) {
            array_splice($navItems, -1, 0, [
                ['label' => 'Dashboard Wali Kelas', 'href' => route('homeroom.dashboard'), 'active' => request()->routeIs('homeroom.dashboard'), 'icon' => 'dashboard'],
                ['label' => 'Data Siswa Kelas', 'href' => route('homeroom.students.index'), 'active' => request()->routeIs('homeroom.students.*'), 'icon' => 'users'],
                ['label' => 'Rekap Nilai Kelas', 'href' => route('homeroom.grades.index'), 'active' => request()->routeIs('homeroom.grades.*'), 'icon' => 'grade'],
                ['label' => 'Rekap Pengumpulan Tugas', 'href' => route('homeroom.submissions.index'), 'active' => request()->routeIs('homeroom.submissions.*'), 'icon' => 'assignment'],
                ['label' => 'Cetak Laporan', 'href' => route('homeroom.reports.index'), 'active' => request()->routeIs('homeroom.reports.*'), 'icon' => 'report'],
            ]);
        }
    } elseif ($user?->role === 'siswa') {
        $navItems = [
            ['label' => 'Dashboard Siswa', 'href' => route('student.dashboard'), 'active' => request()->routeIs('student.dashboard'), 'icon' => 'dashboard'],
            ['label' => 'Materi Siswa', 'href' => route('student.materials.index'), 'active' => request()->routeIs('student.materials.*'), 'icon' => 'book'],
            ['label' => 'Tugas Siswa', 'href' => route('student.assignments.index'), 'active' => request()->routeIs('student.assignments.*'), 'icon' => 'assignment'],
            ['label' => 'Pengumpulan Tugas', 'href' => route('student.assignments.index'), 'active' => request()->routeIs('student.assignments.show'), 'icon' => 'assignment'],
            ['label' => 'Riwayat Nilai / Raport', 'href' => route('student.grades.index'), 'active' => request()->routeIs('student.grades.*'), 'icon' => 'grade'],
            ['label' => 'Pengumuman Kelas', 'href' => route('student.announcements.index'), 'active' => request()->routeIs('student.announcements.*'), 'icon' => 'announcement'],
            ['label' => 'Profil Siswa', 'href' => route('profile.edit'), 'active' => request()->routeIs('profile.*'), 'icon' => 'profile'],
            ['label' => 'Dashboard Analitik', 'href' => route('student.analytics.index'), 'active' => request()->routeIs('student.analytics.*'), 'icon' => 'analytics'],
        ];
    } elseif ($user?->role === 'wali_kelas') {
        $navItems = [
            ['label' => 'Dashboard Wali Kelas', 'href' => route('homeroom.dashboard'), 'active' => request()->routeIs('homeroom.*'), 'icon' => 'dashboard'],
            ['label' => 'Data Siswa Kelas', 'href' => route('homeroom.students.index'), 'active' => request()->routeIs('homeroom.students.*'), 'icon' => 'users'],
            ['label' => 'Rekap Nilai Kelas', 'href' => route('homeroom.grades.index'), 'active' => request()->routeIs('homeroom.grades.*'), 'icon' => 'grade'],
            ['label' => 'Rekap Pengumpulan Tugas', 'href' => route('homeroom.submissions.index'), 'active' => request()->routeIs('homeroom.submissions.*'), 'icon' => 'assignment'],
            ['label' => 'Cetak Laporan', 'href' => route('homeroom.reports.index'), 'active' => request()->routeIs('homeroom.reports.*'), 'icon' => 'report'],
        ];
    }

    $accountItems = [
        ['label' => 'Profil', 'href' => route('profile.edit'), 'active' => request()->routeIs('profile.*'), 'icon' => 'profile'],
        ['label' => 'Notifikasi', 'href' => route('notifications.index'), 'active' => request()->routeIs('notifications.*'), 'icon' => 'notification', 'badge' => $unreadNotificationsCount],
    ];

    $linkBase = 'group flex h-11 items-center gap-3 rounded-xl px-3 text-sm font-bold transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2';
    $activeLink = 'bg-cyan-700 text-white shadow-sm shadow-cyan-900/20';
    $inactiveLink = 'text-slate-600 hover:bg-cyan-50 hover:text-cyan-800';
@endphp

<div
    x-show="sidebarOpen"
    x-cloak
    x-transition.opacity.duration.200ms
    @click="sidebarOpen = false"
    class="fixed inset-0 z-40 bg-slate-950/45 backdrop-blur-sm md:hidden"
></div>

<aside
    id="app-sidebar"
    class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col border-r border-cyan-100/80 bg-white/95 shadow-2xl shadow-slate-900/10 backdrop-blur-xl transition-all duration-300 ease-in-out md:translate-x-0 md:shadow-none"
    :class="sidebarOpen ? 'translate-x-0 md:w-72' : '-translate-x-full md:w-20'"
>
    <div class="flex h-16 items-center gap-3 border-b border-cyan-100 px-4">
        <a href="{{ route('dashboard') }}" class="flex min-w-0 flex-1 items-center gap-3" title="Dashboard">
            <div class="grid h-11 w-11 shrink-0 place-items-center overflow-hidden rounded-xl bg-white p-1 shadow-sm ring-1 ring-cyan-100">
                <x-application-logo class="h-full w-full" />
            </div>
            <div x-show="sidebarOpen" x-transition.opacity.duration.200ms class="min-w-0 leading-tight">
                <div class="truncate text-sm font-extrabold text-slate-900">E-Learning</div>
                <div class="truncate text-xs font-semibold text-slate-500">SMP Muhammadiyah Imam Syuhodo</div>
            </div>
        </a>

        <button
            type="button"
            @click="sidebarOpen = false"
            class="grid h-10 w-10 shrink-0 place-items-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-cyan-800 md:hidden"
            aria-label="Tutup sidebar"
        >
            <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div class="flex min-h-0 flex-1 flex-col overflow-y-auto px-3 py-5">
        <div x-show="sidebarOpen" x-transition.opacity.duration.200ms class="mb-2 px-3 text-xs font-extrabold text-slate-400">
            Menu Utama
        </div>

        <nav class="space-y-1" aria-label="Menu utama">
            @foreach ($navItems as $item)
                <a
                    href="{{ $item['href'] }}"
                    title="{{ $item['label'] }}"
                    @click="if (window.innerWidth < 768) sidebarOpen = false"
                    class="{{ $linkBase }} {{ $item['active'] ? $activeLink : $inactiveLink }}"
                    :class="sidebarOpen ? 'justify-start' : 'justify-center md:px-0'"
                    @if($item['active']) aria-current="page" @endif
                >
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg {{ $item['active'] ? 'bg-white/15' : 'bg-white text-slate-500 ring-1 ring-slate-100 group-hover:text-cyan-700' }}">
                        <x-sidebar-icon :name="$item['icon']" class="h-5 w-5" />
                    </span>
                    <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="truncate">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="mt-6 border-t border-cyan-100 pt-5">
            <div x-show="sidebarOpen" x-transition.opacity.duration.200ms class="mb-2 px-3 text-xs font-extrabold text-slate-400">
                Akun
            </div>

            <div class="space-y-1">
                @foreach ($accountItems as $item)
                    <a
                        href="{{ $item['href'] }}"
                        title="{{ $item['label'] }}"
                        @click="if (window.innerWidth < 768) sidebarOpen = false"
                        class="{{ $linkBase }} {{ $item['active'] ? $activeLink : $inactiveLink }}"
                        :class="sidebarOpen ? 'justify-start' : 'justify-center md:px-0'"
                        @if($item['active']) aria-current="page" @endif
                    >
                        <span class="relative grid h-9 w-9 shrink-0 place-items-center rounded-lg {{ $item['active'] ? 'bg-white/15' : 'bg-white text-slate-500 ring-1 ring-slate-100 group-hover:text-cyan-700' }}">
                            <x-sidebar-icon :name="$item['icon']" class="h-5 w-5" />
                            @if(!empty($item['badge']))
                                <span x-show="! sidebarOpen" class="absolute right-1 top-1 h-2.5 w-2.5 rounded-full bg-rose-500 ring-2 ring-white"></span>
                            @endif
                        </span>
                        <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="flex min-w-0 flex-1 items-center justify-between gap-2">
                            <span class="truncate">{{ $item['label'] }}</span>
                            @if(!empty($item['badge']))
                                <span class="rounded-full bg-rose-100 px-2 py-0.5 text-xs font-extrabold text-rose-700">{{ $item['badge'] }}</span>
                            @endif
                        </span>
                    </a>
                @endforeach

                <form method="POST" action="{{ route('logout') }}" data-confirm-title="Konfirmasi Logout" data-confirm="Yakin ingin logout dari sistem?" data-confirm-yes="Logout" data-confirm-no="Tidak">
                    @csrf
                    <button
                        type="submit"
                        title="Logout"
                        class="{{ $linkBase }} w-full text-rose-600 hover:bg-rose-50 hover:text-rose-700"
                        :class="sidebarOpen ? 'justify-start' : 'justify-center md:px-0'"
                    >
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-white text-rose-500 ring-1 ring-rose-100">
                            <x-sidebar-icon name="logout" class="h-5 w-5" />
                        </span>
                        <span x-show="sidebarOpen" x-transition.opacity.duration.200ms class="truncate">Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    @if($user)
        <div class="border-t border-cyan-100 p-3">
            <div
                class="flex items-center gap-3 rounded-xl bg-slate-50 p-3 ring-1 ring-slate-100"
                :class="sidebarOpen ? 'justify-start' : 'justify-center px-2'"
            >
                <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-cyan-700 text-sm font-extrabold text-white">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div x-show="sidebarOpen" x-transition.opacity.duration.200ms class="min-w-0">
                    <div class="truncate text-sm font-extrabold text-slate-800">{{ $user->name }}</div>
                    <div class="truncate text-xs font-bold text-cyan-700">{{ $user->roleLabel() }}</div>
                </div>
            </div>
        </div>
    @endif
</aside>
