<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">Dashboard Admin</h1>
                <p class="text-sm text-slate-500">Ringkasan aktivitas akademik SMP Muhammadiyah Imam Syuhodo.</p>
            </div>
        </div>
    </x-slot>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['label' => 'Jumlah Guru', 'value' => $stats['guru'], 'icon' => 'teacher', 'tone' => 'cyan'],
            ['label' => 'Jumlah Siswa', 'value' => $stats['siswa'], 'icon' => 'users', 'tone' => 'emerald'],
            ['label' => 'Jumlah Kelas', 'value' => $stats['kelas'], 'icon' => 'class', 'tone' => 'amber'],
            ['label' => 'Mata Pelajaran', 'value' => $stats['mapel'], 'icon' => 'book', 'tone' => 'sky'],
            ['label' => 'Materi', 'value' => $stats['materi'], 'icon' => 'book', 'tone' => 'violet'],
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
                        'bg-sky-50 text-sky-700' => $item['tone'] === 'sky',
                        'bg-violet-50 text-violet-700' => $item['tone'] === 'violet',
                        'bg-rose-50 text-rose-700' => $item['tone'] === 'rose',
                        'bg-slate-100 text-slate-700' => $item['tone'] === 'slate',
                    ])>
                        <x-sidebar-icon :name="$item['icon']" class="h-5 w-5" />
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-4 md:grid-cols-3">
        @foreach($classSummaries as $summary)
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-sm font-semibold text-slate-500">Kelas {{ $summary['tingkat'] }}</div>
                <div class="mt-2 text-2xl font-black text-cyan-800">{{ $summary['jumlah_kelas'] }} kelas</div>
                <div class="mt-1 text-sm font-semibold text-slate-500">{{ $summary['jumlah_siswa'] }} siswa</div>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="rounded-3xl border border-white/70 bg-white/90 p-6 shadow-sm lg:col-span-2">
            <h2 class="mb-4 text-lg font-bold text-slate-900">Grafik Rata-rata Kelas</h2>
            <canvas id="rataKelasChart" height="120"></canvas>
        </div>

        <div class="rounded-3xl border border-white/70 bg-white/90 p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-bold text-slate-900">Pengumuman Terbaru</h2>
            <div class="space-y-3">
                @forelse($latestAnnouncements as $announcement)
                    <a href="{{ route('admin.announcements.index', ['highlight' => $announcement->id]) }}" class="block rounded-2xl bg-slate-50 p-3">
                        <div class="font-semibold text-slate-800">{{ $announcement->judul }}</div>
                        <div class="text-xs text-slate-500">{{ $announcement->audienceLabel() }}</div>
                    </a>
                @empty
                    <p class="text-sm text-slate-500">Belum ada pengumuman.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="mt-6 rounded-3xl border border-white/70 bg-white/90 p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-bold text-slate-900">Aktivitas Nilai Terbaru</h2>
        <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                @forelse($aktivitasNilai as $grade)
                    <div class="rounded-2xl bg-slate-50 p-3">
                        <div class="font-semibold text-slate-800">{{ $grade->student->nama ?? '-' }}</div>
                        <div class="text-xs text-slate-500">{{ $grade->subject->nama_mapel ?? '-' }} - {{ $grade->schoolClass->nama_kelas ?? '-' }}</div>
                        <div class="mt-1 text-sm font-bold text-cyan-800">Nilai akhir: {{ $grade->nilai_akhir ?? '-' }}</div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada aktivitas nilai.</p>
                @endforelse
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const rataKelas = @json($rataKelasChart);

            new Chart(document.getElementById('rataKelasChart'), {
                type: 'bar',
                data: {
                    labels: rataKelas.map(item => item.label),
                    datasets: [{
                        label: 'Rata-rata kelas',
                        data: rataKelas.map(item => item.value),
                        backgroundColor: '#0e7490',
                        borderRadius: 14,
                    }]
                },
                options: { responsive: true, scales: { y: { min: 0, max: 100 } } }
            });
        </script>
    @endpush
</x-app-layout>
