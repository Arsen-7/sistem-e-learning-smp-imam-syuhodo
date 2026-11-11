<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Dashboard Siswa</h1>
            <p class="text-sm text-slate-500">Selamat datang, {{ $student->nama }}. Pantau materi, tugas, nilai, dan perkembangan akademik pribadi.</p>
        </div>
    </x-slot>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['label' => 'Materi Tersedia', 'value' => $stats['materi'], 'icon' => 'book', 'tone' => 'cyan'],
            ['label' => 'Tugas Aktif', 'value' => $stats['tugas'], 'icon' => 'assignment', 'tone' => 'amber'],
            ['label' => 'Mapel Dinilai', 'value' => $stats['mapel'], 'icon' => 'class', 'tone' => 'emerald'],
            ['label' => 'Rata-rata Nilai', 'value' => $stats['rata_nilai'], 'icon' => 'grade', 'tone' => 'slate'],
        ] as $item)
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold text-slate-500">{{ $item['label'] }}</div>
                        <div class="mt-2 text-3xl font-black text-slate-950">{{ $item['value'] }}</div>
                    </div>
                    <div @class([
                        'grid h-11 w-11 shrink-0 place-items-center rounded-lg',
                        'bg-cyan-50 text-cyan-700' => $item['tone'] === 'cyan',
                        'bg-amber-50 text-amber-700' => $item['tone'] === 'amber',
                        'bg-emerald-50 text-emerald-700' => $item['tone'] === 'emerald',
                        'bg-slate-100 text-slate-700' => $item['tone'] === 'slate',
                    ])>
                        <x-sidebar-icon :name="$item['icon']" class="h-5 w-5" />
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="rounded-3xl bg-white/90 p-6 shadow-sm lg:col-span-2">
            <h2 class="mb-4 text-lg font-extrabold text-slate-900">Grafik Perkembangan Nilai</h2>
            <canvas id="studentDashboardChart" height="120"></canvas>
        </div>

        <div class="rounded-3xl bg-white/90 p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-extrabold text-slate-900">Pengumuman Terbaru</h2>
            <div class="space-y-3">
                @forelse($latestAnnouncements as $announcement)
                    <a href="{{ route('student.announcements.index', ['highlight' => $announcement->id]) }}" class="block rounded-2xl bg-slate-50 p-4">
                        <div class="font-bold text-slate-800">{{ $announcement->judul }}</div>
                        <div class="text-xs text-slate-500">{{ $announcement->audienceLabel() }}</div>
                    </a>
                @empty
                    <p class="text-sm text-slate-500">Belum ada pengumuman.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="rounded-3xl bg-white/90 p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-extrabold text-slate-900">Notifikasi Terbaru</h2>
            <div class="space-y-3">
                @forelse($latestNotifications as $notification)
                    <a href="{{ route('notifications.open', $notification) }}" class="block rounded-2xl bg-slate-50 p-4">
                        <div class="font-bold text-slate-800">{{ $notification->title }}</div>
                        <div class="text-xs text-slate-500">{{ $notification->created_at->format('d M Y H:i') }}</div>
                    </a>
                @empty
                    <p class="text-sm text-slate-500">Belum ada notifikasi.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-3xl bg-white/90 p-6 shadow-sm lg:col-span-2">
            <h2 class="mb-4 text-lg font-extrabold text-slate-900">Status Akademik</h2>
            <div class="space-y-3">
                @forelse($grades as $grade)
                    @php($analytic = $analytics->get($grade->subject_id))
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="font-bold text-slate-800">{{ $grade->subject->nama_mapel ?? '-' }}</div>
                                <div class="text-xs text-slate-500">Rata-rata kelas: {{ $analytic?->rata_rata_kelas ?? '-' }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-xl font-black text-cyan-800">{{ $grade->nilai_akhir }}</div>
                                <div class="text-xs font-semibold text-slate-500">{{ $grade->status_kelulusan ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada nilai akhir.</p>
                @endforelse
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const studentDashboardData = @json($studentDashboardChart);

            new Chart(document.getElementById('studentDashboardChart'), {
                type: 'line',
                data: {
                    labels: studentDashboardData.map(item => item.label),
                    datasets: [
                        {
                            label: 'Nilai pribadi',
                            data: studentDashboardData.map(item => item.nilai),
                            borderColor: '#0e7490',
                            backgroundColor: 'rgba(14, 116, 144, 0.12)',
                            tension: 0.35,
                            fill: true,
                        },
                        {
                            label: 'Rata-rata kelas',
                            data: studentDashboardData.map(item => item.rata),
                            borderColor: '#d97706',
                            backgroundColor: 'rgba(217, 119, 6, 0.10)',
                            tension: 0.35,
                            fill: false,
                        },
                    ],
                },
                options: { responsive: true, scales: { y: { min: 0, max: 100 } } },
            });
        </script>
    @endpush
</x-app-layout>
