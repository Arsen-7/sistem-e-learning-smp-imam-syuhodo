<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Laporan Akademik</h1>
            <p class="text-sm text-slate-500">Filter laporan nilai siswa, nilai per kelas, nilai per mata pelajaran, tugas, dan rata-rata kelas.</p>
        </div>
    </x-slot>

    <form method="GET" class="mb-6 rounded-3xl bg-white/90 p-5 shadow-sm">
        <div class="grid gap-4 md:grid-cols-5">
            <select name="class_id" class="rounded-2xl border-slate-200"><option value="">Semua kelas</option>@foreach($classes as $class)<option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>{{ $class->nama_kelas }}</option>@endforeach</select>
            <select name="subject_id" class="rounded-2xl border-slate-200"><option value="">Semua mapel</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected(request('subject_id') == $subject->id)>{{ $subject->nama_mapel }}</option>@endforeach</select>
            <select name="teacher_id" class="rounded-2xl border-slate-200"><option value="">Semua guru</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}" @selected(request('teacher_id') == $teacher->id)>{{ $teacher->nama }}</option>@endforeach</select>
            <select name="student_id" class="rounded-2xl border-slate-200"><option value="">Semua siswa</option>@foreach($students as $student)<option value="{{ $student->id }}" @selected(request('student_id') == $student->id)>{{ $student->nama }}</option>@endforeach</select>
            <button class="rounded-2xl bg-cyan-700 px-4 py-2 font-bold text-white">Tampilkan</button>
        </div>
    </form>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-3xl bg-white/90 p-6 shadow-sm lg:col-span-2">
            <h2 class="mb-4 text-lg font-extrabold text-slate-900">Laporan Nilai</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500"><tr><th class="px-4 py-3">Siswa</th><th class="px-4 py-3">Kelas</th><th class="px-4 py-3">Mapel</th><th class="px-4 py-3">Tugas</th><th class="px-4 py-3">Kuis</th><th class="px-4 py-3">UTS</th><th class="px-4 py-3">UAS</th><th class="px-4 py-3">Akhir</th><th class="px-4 py-3">Status</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($grades as $grade)
                            <tr><td class="px-4 py-3 font-semibold">{{ $grade->student->nama ?? '-' }}</td><td class="px-4 py-3">{{ $grade->schoolClass->nama_kelas ?? '-' }}</td><td class="px-4 py-3">{{ $grade->subject->nama_mapel ?? '-' }}</td><td class="px-4 py-3">{{ $grade->nilai_tugas ?? '-' }}</td><td class="px-4 py-3">{{ $grade->nilai_kuis ?? '-' }}</td><td class="px-4 py-3">{{ $grade->nilai_uts ?? '-' }}</td><td class="px-4 py-3">{{ $grade->nilai_uas ?? '-' }}</td><td class="px-4 py-3 font-bold text-cyan-800">{{ $grade->nilai_akhir ?? '-' }}</td><td class="px-4 py-3">{{ $grade->status_kelulusan ?? '-' }}</td></tr>
                        @empty
                            <tr><td colspan="9" class="px-4 py-8 text-center text-slate-500">Tidak ada data nilai sesuai filter.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $grades->links() }}</div>
        </div>

        <div class="rounded-3xl bg-white/90 p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-extrabold text-slate-900">Laporan Tugas</h2>
            <div class="space-y-3">
                @forelse($assignments as $assignment)
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <div class="font-bold text-slate-800">{{ $assignment->judul }}</div>
                        <div class="text-xs text-slate-500">{{ $assignment->subject->nama_mapel ?? '-' }} - {{ $assignment->schoolClass->nama_kelas ?? '-' }}</div>
                        <div class="mt-2 text-sm text-slate-600">Pengumpulan: <span class="font-bold text-cyan-800">{{ $assignment->submissions_count }}</span></div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Tidak ada tugas sesuai filter.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
