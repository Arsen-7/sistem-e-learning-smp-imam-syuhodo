<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Analitik Akademik Guru</h1>
            <p class="text-sm text-slate-500">Grafik perkembangan nilai siswa, rata-rata kelas, dan daftar nilai tertinggi serta terendah.</p>
        </div>
    </x-slot>

    @php
        $initialSubject = request('subject_id', $selectedPair?->subject_id ?? '');
        $initialClass = request('class_id', $selectedPair?->class_id ?? '');
    @endphp

    <form method="GET" x-data="{ pair: '{{ $initialSubject }}|{{ $initialClass }}', sync() { const parts = this.pair.split('|'); this.$refs.subject.value = parts[0] || ''; this.$refs.classId.value = parts[1] || ''; } }" x-init="sync()" class="mb-6 rounded-3xl bg-white/90 p-5 shadow-sm">
        <input type="hidden" name="subject_id" x-ref="subject" value="{{ $initialSubject }}">
        <input type="hidden" name="class_id" x-ref="classId" value="{{ $initialClass }}">
        <div class="grid gap-4 md:grid-cols-[1fr_auto]">
            <select x-model="pair" @change="sync()" class="rounded-2xl border-slate-200" @disabled($pairs->isEmpty())>
                @forelse($pairs as $pair)
                    <option value="{{ $pair->subject_id }}|{{ $pair->class_id }}">{{ $pair->schoolClass->nama_kelas ?? '-' }} - {{ $pair->subject->nama_mapel ?? '-' }}</option>
                @empty
                    <option value="">Belum ada kelas dan mata pelajaran diampu</option>
                @endforelse
            </select>
            <button class="rounded-2xl bg-cyan-700 px-5 py-2.5 text-sm font-bold text-white" @disabled($pairs->isEmpty())>Tampilkan</button>
        </div>
    </form>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach([
            ['label' => 'Jumlah Nilai', 'value' => $grades->count()],
            ['label' => 'Rata-rata Kelas', 'value' => $rataKelas],
            ['label' => 'Nilai Tertinggi', 'value' => $grades->max('nilai_akhir') ?? '-'],
            ['label' => 'Nilai Terendah', 'value' => $grades->min('nilai_akhir') ?? '-'],
        ] as $item)
            <div class="rounded-3xl bg-white/90 p-5 shadow-sm">
                <div class="text-sm font-semibold text-slate-500">{{ $item['label'] }}</div>
                <div class="mt-2 text-3xl font-black text-cyan-800">{{ $item['value'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="rounded-3xl bg-white/90 p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-extrabold text-slate-900">Perkembangan Komponen Nilai</h2>
            <canvas id="teacherProgressChart" height="130"></canvas>
        </div>
        <div class="rounded-3xl bg-white/90 p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-extrabold text-slate-900">Nilai Akhir dan Rata-rata Kelas</h2>
            <canvas id="teacherAverageCompareChart" height="130"></canvas>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="rounded-3xl bg-white/90 p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-extrabold text-slate-900">Nilai Tertinggi</h2>
            @forelse($topStudents as $grade)
                <div class="mb-2 flex justify-between rounded-2xl bg-emerald-50 px-4 py-3 text-sm">
                    <span>{{ $grade->student->nama ?? '-' }}</span>
                    <span class="font-bold text-emerald-700">{{ $grade->nilai_akhir }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada nilai.</p>
            @endforelse
        </div>
        <div class="rounded-3xl bg-white/90 p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-extrabold text-slate-900">Nilai Terendah</h2>
            @forelse($lowStudents as $grade)
                <div class="mb-2 flex justify-between rounded-2xl bg-amber-50 px-4 py-3 text-sm">
                    <span>{{ $grade->student->nama ?? '-' }}</span>
                    <span class="font-bold text-amber-700">{{ $grade->nilai_akhir }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada nilai.</p>
            @endforelse
        </div>
        <div class="rounded-3xl bg-white/90 p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-extrabold text-slate-900">Tren Nilai</h2>
            @forelse($grades as $grade)
                @php
                    $components = collect([$grade->nilai_tugas, $grade->nilai_kuis, $grade->nilai_uts, $grade->nilai_uas])->filter(fn ($value) => $value !== null)->map(fn ($value) => (float) $value)->values();
                    $trend = 'Data belum cukup';
                    if ($components->count() >= 2 && $components->last() > $components->first()) {
                        $trend = 'Naik';
                    } elseif ($components->count() >= 2 && $components->last() < $components->first()) {
                        $trend = 'Turun';
                    } elseif ($components->count() >= 2) {
                        $trend = 'Stabil';
                    }
                @endphp
                <div class="mb-2 flex justify-between rounded-2xl bg-slate-50 px-4 py-3 text-sm">
                    <span>{{ $grade->student->nama ?? '-' }}</span>
                    <span class="font-bold text-cyan-800">{{ $trend }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada nilai.</p>
            @endforelse
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const teacherGrades = @json($teacherGradesChart);
            const chartColors = ['#0e7490', '#d97706', '#059669', '#7c3aed', '#be123c', '#2563eb', '#65a30d', '#b45309'];

            new Chart(document.getElementById('teacherProgressChart'), {
                type: 'line',
                data: {
                    labels: ['Tugas', 'Kuis', 'UTS', 'UAS'],
                    datasets: teacherGrades.slice(0, 8).map((item, index) => ({
                        label: item.name,
                        data: item.components,
                        borderColor: chartColors[index % chartColors.length],
                        backgroundColor: chartColors[index % chartColors.length],
                        tension: 0.35,
                    })),
                },
                options: { responsive: true, scales: { y: { min: 0, max: 100 } } },
            });

            new Chart(document.getElementById('teacherAverageCompareChart'), {
                type: 'bar',
                data: {
                    labels: teacherGrades.map(item => item.name),
                    datasets: [
                        { label: 'Nilai akhir', data: teacherGrades.map(item => item.akhir), backgroundColor: '#0e7490', borderRadius: 12 },
                        { label: 'Rata-rata kelas', data: teacherGrades.map(() => {{ (float) $rataKelas }}), backgroundColor: '#d97706', borderRadius: 12 },
                    ],
                },
                options: { responsive: true, scales: { y: { min: 0, max: 100 } } },
            });
        </script>
    @endpush
</x-app-layout>
