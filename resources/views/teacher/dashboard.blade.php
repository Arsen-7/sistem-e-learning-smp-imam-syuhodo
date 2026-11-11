<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Dashboard Guru</h1>
            <p class="text-sm text-slate-500">Selamat datang, {{ $teacher->nama }}. Kelola materi, tugas, nilai, dan analitik akademik.</p>
        </div>
    </x-slot>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ([
            ['label' => 'Kelas Diampu', 'value' => $stats['kelas'], 'icon' => 'class', 'tone' => 'cyan'],
            ['label' => 'Jumlah Siswa', 'value' => $stats['siswa'], 'icon' => 'users', 'tone' => 'emerald'],
            ['label' => 'Materi', 'value' => $stats['materi'], 'icon' => 'book', 'tone' => 'amber'],
            ['label' => 'Tugas', 'value' => $stats['tugas'], 'icon' => 'assignment', 'tone' => 'rose'],
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
                        'bg-emerald-50 text-emerald-700' => $item['tone'] === 'emerald',
                        'bg-amber-50 text-amber-700' => $item['tone'] === 'amber',
                        'bg-rose-50 text-rose-700' => $item['tone'] === 'rose',
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
            <h2 class="mb-4 text-lg font-extrabold text-slate-900">Grafik Rata-rata Kelas per Mapel</h2>
            <canvas id="teacherAverageChart" height="120"></canvas>
        </div>
        <div class="rounded-3xl bg-white/90 p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-extrabold text-slate-900">Kelas dan Mapel Diampu</h2>
            <div class="space-y-2">
                @forelse($pairs as $pair)
                    <div class="rounded-2xl bg-cyan-50 px-4 py-3 text-sm font-semibold text-cyan-900">{{ $pair->schoolClass->nama_kelas ?? '-' }} - {{ $pair->subject->nama_mapel ?? '-' }}</div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada relasi pengampu.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="rounded-3xl bg-white/90 p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-extrabold text-slate-900">Pengumuman Terbaru</h2>
            <div class="space-y-2">
                @forelse($latestAnnouncements as $announcement)
                    <a href="{{ route('teacher.announcements.index', ['highlight' => $announcement->id]) }}" class="block rounded-2xl bg-slate-50 p-3">
                        <div class="font-bold text-slate-800">{{ $announcement->judul }}</div>
                        <div class="text-xs text-slate-500">{{ $announcement->audienceLabel() }}</div>
                    </a>
                @empty
                    <p class="text-sm text-slate-500">Belum ada pengumuman.</p>
                @endforelse
            </div>
        </div>
        <div class="rounded-3xl bg-white/90 p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-extrabold text-slate-900">Nilai Tertinggi</h2>
            @forelse($topStudents as $grade)
                <div class="mb-2 flex justify-between rounded-2xl bg-emerald-50 px-4 py-3 text-sm"><span>{{ $grade->student->nama ?? '-' }}</span><span class="font-bold text-emerald-700">{{ $grade->nilai_akhir }}</span></div>
            @empty
                <p class="text-sm text-slate-500">Belum ada nilai.</p>
            @endforelse
        </div>
        <div class="rounded-3xl bg-white/90 p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-extrabold text-slate-900">Nilai Terendah</h2>
            @forelse($lowStudents as $grade)
                <div class="mb-2 flex justify-between rounded-2xl bg-amber-50 px-4 py-3 text-sm"><span>{{ $grade->student->nama ?? '-' }}</span><span class="font-bold text-amber-700">{{ $grade->nilai_akhir }}</span></div>
            @empty
                <p class="text-sm text-slate-500">Belum ada nilai.</p>
            @endforelse
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const averageData = @json($rataKelasChart);
            new Chart(document.getElementById('teacherAverageChart'), {
                type: 'bar',
                data: { labels: averageData.map(i => i.label), datasets: [{ label: 'Rata-rata kelas', data: averageData.map(i => i.value), backgroundColor: '#d97706', borderRadius: 14 }] },
                options: { scales: { y: { min: 0, max: 100 } } }
            });
        </script>
    @endpush
</x-app-layout>
