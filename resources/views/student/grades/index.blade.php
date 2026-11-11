<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Riwayat Nilai / Raport</h1>
            <p class="text-sm text-slate-500">Komponen nilai dari seluruh tahun pelajaran, semester, dan kelas.</p>
        </div>
    </x-slot>

    <form method="GET" class="mb-5 flex flex-wrap gap-3 rounded-3xl bg-white/90 p-4 shadow-sm">
        <select name="tahun_ajaran_id" onchange="this.form.submit()" class="rounded-2xl border-slate-200"><option value="">Semua Tahun Pelajaran</option>@foreach($years as $year)<option value="{{ $year->id }}" @selected($yearId === $year->id)>{{ $year->nama }}</option>@endforeach</select>
        <select name="semester_id" onchange="this.form.submit()" class="rounded-2xl border-slate-200"><option value="">Semua Semester</option>@foreach($semesters as $semester)<option value="{{ $semester->id }}" @selected($semesterId === $semester->id)>{{ $semester->nama }}</option>@endforeach</select>
        <button type="button" onclick="window.print()" class="rounded-2xl border border-slate-200 px-4 text-sm font-bold text-slate-700">Cetak Rekap</button>
    </form>

    <div class="mb-5 grid gap-4 md:grid-cols-3">
        <div class="rounded-3xl bg-white/90 p-5 shadow-sm md:col-span-2">
            <div class="text-sm font-semibold text-slate-500">Rata-rata akhir seluruh mata pelajaran</div>
            <div class="mt-2 text-4xl font-black text-cyan-800">{{ $overallAverage ? number_format($overallAverage, 2) : '-' }}</div>
            <div class="mt-1 text-sm text-slate-500">Dihitung dari nilai akhir setiap mapel yang sudah diinput guru.</div>
        </div>
        <div class="rounded-3xl bg-white/90 p-5 shadow-sm">
            <div class="text-sm font-semibold text-slate-500">Jumlah mapel dinilai</div>
            <div class="mt-2 text-4xl font-black text-slate-900">{{ $grades->whereNotNull('nilai_akhir')->count() }}</div>
        </div>
    </div>

    <div class="overflow-hidden rounded-3xl bg-white/90 shadow-sm">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">Tahun / Semester</th>
                    <th class="px-4 py-3">Kelas & Mata Pelajaran</th>
                    <th class="px-4 py-3">Rata-rata Akhir</th>
                    <th class="px-4 py-3">Komponen</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($grades as $grade)
                    <tr>
                        <td class="px-4 py-3 text-xs text-slate-600">{{ $grade->academicYear->nama ?? '-' }}<br>{{ $grade->semester->nama ?? '-' }}</td>
                        <td class="px-4 py-3 font-semibold text-slate-900">{{ $grade->schoolClass->nama_kelas ?? '-' }}<br>{{ $grade->subject->nama_mapel ?? '-' }}</td>
                        <td class="px-4 py-3 text-lg font-black text-cyan-800">{{ $grade->nilai_akhir ?? '-' }}</td>
                        <td class="px-4 py-3 text-xs text-slate-600">
                            Tugas {{ $grade->nilai_tugas ?? '-' }},
                            Kuis {{ $grade->nilai_kuis ?? '-' }},
                            UTS {{ $grade->nilai_uts ?? '-' }},
                            UAS {{ $grade->nilai_uas ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $grade->status_kelulusan === 'Lulus' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ $grade->status_kelulusan ?? '-' }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">Belum ada data nilai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>
