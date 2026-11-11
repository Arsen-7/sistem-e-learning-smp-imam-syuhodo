<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Analitik Akademik</h1>
            <p class="text-sm text-slate-500">Perkembangan nilai pribadi dan perbandingan dengan rata-rata kelas.</p>
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-3xl bg-white/90 p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-extrabold text-slate-900">Grafik Nilai Pribadi</h2>
            <canvas id="studentLineChart" height="130"></canvas>
        </div>
        <div class="rounded-3xl bg-white/90 p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-extrabold text-slate-900">Perbandingan Rata-rata Kelas</h2>
            <canvas id="studentCompareChart" height="130"></canvas>
        </div>
    </div>

    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse($grades as $grade)
            @php($analytic = $analytics->get($grade->subject_id))
            <div class="rounded-3xl bg-white/90 p-5 shadow-sm">
                <div class="text-xs font-bold uppercase tracking-wide text-cyan-700">{{ $grade->subject->nama_mapel ?? '-' }}</div>
                <div class="mt-2 flex items-end justify-between">
                    <div>
                        <div class="text-3xl font-black text-slate-900">{{ $grade->nilai_akhir }}</div>
                        <div class="text-sm font-semibold text-slate-500">{{ $grade->status_kelulusan ?? '-' }}</div>
                    </div>
                    <div class="text-right text-sm">
                        <div class="font-bold text-amber-700">{{ $analytic?->tren_nilai ?? 'Data belum cukup' }}</div>
                        <div class="text-xs text-slate-500">Rata-rata kelas {{ $analytic?->rata_rata_kelas ?? '-' }}</div>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-3xl bg-white/90 p-8 text-center text-slate-500 md:col-span-2 xl:col-span-3">Belum ada data analitik.</div>
        @endforelse
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const studentAnalytics = @json($studentAnalyticsChart);

            new Chart(document.getElementById('studentLineChart'), {
                type: 'line',
                data: {
                    labels: studentAnalytics.map(item => item.label),
                    datasets: [{
                        label: 'Nilai akhir',
                        data: studentAnalytics.map(item => item.nilai),
                        borderColor: '#0e7490',
                        backgroundColor: 'rgba(14, 116, 144, 0.12)',
                        tension: 0.35,
                        fill: true,
                    }],
                },
                options: { responsive: true, scales: { y: { min: 0, max: 100 } } },
            });

            new Chart(document.getElementById('studentCompareChart'), {
                type: 'bar',
                data: {
                    labels: studentAnalytics.map(item => item.label),
                    datasets: [
                        { label: 'Nilai pribadi', data: studentAnalytics.map(item => item.nilai), backgroundColor: '#0e7490', borderRadius: 12 },
                        { label: 'Rata-rata kelas', data: studentAnalytics.map(item => item.rata), backgroundColor: '#d97706', borderRadius: 12 },
                    ],
                },
                options: { responsive: true, scales: { y: { min: 0, max: 100 } } },
            });
        </script>
    @endpush
</x-app-layout>
