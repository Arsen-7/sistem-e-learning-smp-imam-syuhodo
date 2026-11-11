<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">Arsip Akademik</h1>
                <p class="text-sm text-slate-500">Riwayat data akademik tersimpan berdasarkan Tahun Akademik.</p>
            </div>
            <div class="rounded-2xl bg-white/90 px-4 py-3 text-sm shadow-sm">
                <span class="font-bold text-slate-500">Tahun aktif:</span>
                <span class="font-extrabold text-cyan-800">{{ $activeYear->nama ?? 'Belum diatur' }}</span>
            </div>
        </div>
    </x-slot>

    @php
        $baseQuery = request()->except(['page', 'teachers_page', 'students_page', 'classes_page', 'homerooms_page', 'subjects_page', 'teaching_page', 'plotting_page', 'grades_page', 'materials_page', 'assignments_page', 'submissions_page', 'announcements_page', 'alumni_page']);
        $exportQuery = http_build_query(array_merge($baseQuery, ['tahun_ajaran_id' => $selectedYear, 'tab' => $activeTab]));
        $activeRows = $archives[$activeTab] ?? collect();
    @endphp

    @if($years->isEmpty())
        <div class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            <div class="font-extrabold">Tahun ajaran belum tersedia.</div>
            <div class="mt-1">Tambahkan dan aktifkan tahun ajaran di Setting Akademik agar arsip bisa dipisahkan dari tahun ke tahun.</div>
            <a href="{{ route('admin.academic-settings.index') }}" class="mt-3 inline-flex rounded-xl bg-amber-600 px-4 py-2 text-sm font-bold text-white">Buka Setting Akademik</a>
        </div>
    @endif

    <form method="GET" class="mb-5 rounded-2xl bg-white/90 p-4 shadow-sm">
        <input type="hidden" name="tab" value="{{ $activeTab }}">
        <div class="grid gap-3 lg:grid-cols-[260px_1fr_auto]">
            <label class="block">
                <span class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Tahun Akademik</span>
                <select name="tahun_ajaran_id" onchange="this.form.submit()" class="w-full rounded-xl border-slate-200 text-sm font-semibold">
                    @foreach($years as $year)
                        <option value="{{ $year->id }}" @selected((int) $selectedYear === $year->id)>{{ $year->nama }}{{ $year->is_active ? ' (Aktif)' : '' }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Search</span>
                <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Cari nama, NIS/NIP, kelas, mapel, judul..." class="w-full rounded-xl border-slate-200 text-sm">
            </label>
            <div class="flex items-end gap-2">
                <button class="rounded-xl bg-cyan-700 px-5 py-2.5 text-sm font-bold text-white">Terapkan</button>
                <a href="{{ route('admin.academic-archives.index', ['tahun_ajaran_id' => $selectedYear, 'tab' => $activeTab]) }}" class="rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-slate-600 ring-1 ring-slate-200">Reset</a>
            </div>
        </div>
        <div class="mt-3 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <select name="class_id" class="rounded-xl border-slate-200 text-sm">
                <option value="">Semua Kelas</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" @selected((int) request('class_id') === $class->id)>{{ $class->nama_kelas }}</option>
                @endforeach
            </select>
            <select name="subject_id" class="rounded-xl border-slate-200 text-sm">
                <option value="">Semua Mata Pelajaran</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected((int) request('subject_id') === $subject->id)>{{ $subject->nama_mapel }}</option>
                @endforeach
            </select>
            <select name="teacher_id" class="rounded-xl border-slate-200 text-sm">
                <option value="">Semua Guru</option>
                @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}" @selected((int) request('teacher_id') === $teacher->id)>{{ $teacher->nama }}</option>
                @endforeach
            </select>
            <select name="student_id" class="rounded-xl border-slate-200 text-sm">
                <option value="">Semua Siswa</option>
                @foreach($students as $student)
                    <option value="{{ $student->id }}" @selected((int) request('student_id') === $student->id)>{{ $student->nama }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <section class="mb-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
        @foreach([
            'teachers' => ['label' => 'Jumlah Guru', 'class' => 'text-cyan-700'],
            'students' => ['label' => 'Jumlah Siswa', 'class' => 'text-emerald-700'],
            'classes' => ['label' => 'Jumlah Kelas', 'class' => 'text-violet-700'],
            'subjects' => ['label' => 'Jumlah Mata Pelajaran', 'class' => 'text-amber-700'],
            'alumni' => ['label' => 'Jumlah Alumni', 'class' => 'text-rose-700'],
        ] as $key => $item)
            <div class="rounded-2xl bg-white/90 p-5 shadow-sm ring-1 ring-slate-100">
                <div class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ $item['label'] }}</div>
                <div class="mt-2 text-3xl font-extrabold text-slate-900">{{ number_format($stats[$key]) }}</div>
                <div class="mt-1 text-xs font-semibold {{ $item['class'] }}">{{ $selectedAcademicYear->nama ?? '-' }}</div>
            </div>
        @endforeach
    </section>

    <section class="mb-5 rounded-2xl bg-white/90 p-3 shadow-sm">
        <div class="flex gap-2 overflow-x-auto pb-1">
            @foreach($sections as $key => $label)
                <a href="{{ route('admin.academic-archives.index', array_merge($baseQuery, ['tahun_ajaran_id' => $selectedYear, 'tab' => $key])) }}" class="whitespace-nowrap rounded-xl px-4 py-2 text-sm font-bold {{ $activeTab === $key ? 'bg-cyan-700 text-white' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </section>

    <section class="rounded-2xl bg-white/90 shadow-sm">
        <div class="flex flex-col gap-3 border-b border-slate-100 p-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900">{{ $sections[$activeTab] }}</h2>
                <p class="text-sm text-slate-500">Tahun Akademik {{ $selectedAcademicYear->nama ?? '-' }}</p>
            </div>
            @unless(in_array($activeTab, $unavailableSections, true))
                <div class="flex gap-2">
                    <a href="{{ route('admin.academic-archives.export', [$activeTab, 'pdf']).'?'.$exportQuery }}" target="_blank" class="rounded-xl bg-white px-4 py-2 text-sm font-bold text-rose-700 ring-1 ring-rose-100">Export PDF</a>
                    <a href="{{ route('admin.academic-archives.export', [$activeTab, 'excel']).'?'.$exportQuery }}" class="rounded-xl bg-white px-4 py-2 text-sm font-bold text-emerald-700 ring-1 ring-emerald-100">Export Excel</a>
                </div>
            @endunless
        </div>

        @if(in_array($activeTab, $unavailableSections, true))
            <div class="p-8 text-center text-sm text-slate-500">
                Modul {{ $sections[$activeTab] }} belum memiliki tabel/model di database aplikasi, sehingga belum bisa diarsipkan berdasarkan tahun akademik.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    @switch($activeTab)
                        @case('summary')
                            <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500"><tr><th class="px-5 py-3">Tahun Akademik</th><th class="px-5 py-3">Periode</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Detail</th></tr></thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($activeRows as $year)
                                    <tr><td class="px-5 py-4 font-extrabold">{{ $year->nama }}</td><td class="px-5 py-4">{{ $year->tanggal_mulai?->format('d M Y') ?? '-' }} - {{ $year->tanggal_selesai?->format('d M Y') ?? '-' }}</td><td class="px-5 py-4">{{ $year->is_active ? 'Aktif' : 'Arsip' }}</td><td class="px-5 py-4">Ringkasan data tersimpan pada card statistik dan rekap tahunan.</td></tr>
                                @endforeach
                            </tbody>
                            @break

                        @case('teachers')
                            <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500"><tr><th class="px-5 py-3">Guru</th><th class="px-5 py-3">NIP</th><th class="px-5 py-3">Kontak</th><th class="px-5 py-3">Detail</th></tr></thead>
                            <tbody class="divide-y divide-slate-100">@forelse($activeRows as $teacher)<tr><td class="px-5 py-4 font-semibold">{{ $teacher->nama }}</td><td class="px-5 py-4">{{ $teacher->nip ?? '-' }}</td><td class="px-5 py-4">{{ $teacher->no_hp ?? '-' }}</td><td class="px-5 py-4">{{ $teacher->jenis_kelamin }} - {{ $teacher->alamat ?? '-' }}</td></tr>@empty<tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">Tidak ada data guru pada tahun ini.</td></tr>@endforelse</tbody>
                            @break

                        @case('students')
                            <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500"><tr><th class="px-5 py-3">Siswa</th><th class="px-5 py-3">NIS</th><th class="px-5 py-3">Kelas</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Detail</th></tr></thead>
                            <tbody class="divide-y divide-slate-100">@forelse($activeRows as $student)<tr><td class="px-5 py-4 font-semibold">{{ $student->nama }}</td><td class="px-5 py-4">{{ $student->nis }}</td><td class="px-5 py-4">{{ $student->schoolClass->nama_kelas ?? '-' }}</td><td class="px-5 py-4">{{ $student->status_siswa }}</td><td class="px-5 py-4">{{ $student->no_hp ?? '-' }} - {{ $student->alamat ?? '-' }}</td></tr>@empty<tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">Tidak ada data siswa pada tahun ini.</td></tr>@endforelse</tbody>
                            @break

                        @case('classes')
                        @case('homerooms')
                            <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500"><tr><th class="px-5 py-3">Kelas</th><th class="px-5 py-3">Tingkat</th><th class="px-5 py-3">Wali Kelas</th><th class="px-5 py-3">Jumlah Siswa Saat Ini</th><th class="px-5 py-3">Tahun</th></tr></thead>
                            <tbody class="divide-y divide-slate-100">@forelse($activeRows as $class)<tr><td class="px-5 py-4 font-semibold">{{ $class->nama_kelas }}</td><td class="px-5 py-4">{{ $class->tingkat }}</td><td class="px-5 py-4">{{ $class->waliKelas->nama ?? '-' }}</td><td class="px-5 py-4">{{ $class->students_count ?? $class->students()->count() }}</td><td class="px-5 py-4">{{ $class->academicYear->nama ?? '-' }}</td></tr>@empty<tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">Tidak ada data kelas pada tahun ini.</td></tr>@endforelse</tbody>
                            @break

                        @case('subjects')
                            <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500"><tr><th class="px-5 py-3">Kode</th><th class="px-5 py-3">Mata Pelajaran</th><th class="px-5 py-3">Tahun</th><th class="px-5 py-3">Detail</th></tr></thead>
                            <tbody class="divide-y divide-slate-100">@forelse($activeRows as $subject)<tr><td class="px-5 py-4 font-semibold">{{ $subject->kode_mapel }}</td><td class="px-5 py-4">{{ $subject->nama_mapel }}</td><td class="px-5 py-4">{{ $subject->academicYear->nama ?? $selectedAcademicYear->nama ?? '-' }}</td><td class="px-5 py-4">Terkait arsip mengajar, materi, tugas, atau nilai pada tahun ini.</td></tr>@empty<tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">Tidak ada data mata pelajaran pada tahun ini.</td></tr>@endforelse</tbody>
                            @break

                        @case('teaching')
                            <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500"><tr><th class="px-5 py-3">Guru</th><th class="px-5 py-3">Mata Pelajaran</th><th class="px-5 py-3">Kelas</th><th class="px-5 py-3">Tahun</th></tr></thead>
                            <tbody class="divide-y divide-slate-100">@forelse($activeRows as $history)<tr><td class="px-5 py-4 font-semibold">{{ $history->teacher->nama ?? '-' }}</td><td class="px-5 py-4">{{ $history->subject->nama_mapel ?? '-' }}</td><td class="px-5 py-4">{{ $history->schoolClass->nama_kelas ?? '-' }}</td><td class="px-5 py-4">{{ $history->academicYear->nama ?? '-' }}</td></tr>@empty<tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">Tidak ada pembagian mengajar pada tahun ini.</td></tr>@endforelse</tbody>
                            @break

                        @case('plotting')
                            <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500"><tr><th class="px-5 py-3">Siswa</th><th class="px-5 py-3">NIS</th><th class="px-5 py-3">Kelas Saat Itu</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Tahun</th></tr></thead>
                            <tbody class="divide-y divide-slate-100">@forelse($activeRows as $history)<tr><td class="px-5 py-4 font-semibold">{{ $history->student->nama ?? '-' }}</td><td class="px-5 py-4">{{ $history->student->nis ?? '-' }}</td><td class="px-5 py-4">{{ $history->schoolClass->nama_kelas ?? '-' }}</td><td class="px-5 py-4">{{ $history->status_siswa }}</td><td class="px-5 py-4">{{ $history->academicYear->nama ?? '-' }}</td></tr>@empty<tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">Tidak ada plotting siswa pada tahun ini.</td></tr>@endforelse</tbody>
                            @break

                        @case('grades')
                            <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500"><tr><th class="px-5 py-3">Siswa</th><th class="px-5 py-3">Kelas</th><th class="px-5 py-3">Mapel</th><th class="px-5 py-3">Tugas</th><th class="px-5 py-3">Kuis</th><th class="px-5 py-3">UTS</th><th class="px-5 py-3">UAS</th><th class="px-5 py-3">Akhir</th></tr></thead>
                            <tbody class="divide-y divide-slate-100">@forelse($activeRows as $grade)<tr><td class="px-5 py-4 font-semibold">{{ $grade->student->nama ?? '-' }}</td><td class="px-5 py-4">{{ $grade->schoolClass->nama_kelas ?? '-' }}</td><td class="px-5 py-4">{{ $grade->subject->nama_mapel ?? '-' }}</td><td class="px-5 py-4">{{ $grade->nilai_tugas ?? '-' }}</td><td class="px-5 py-4">{{ $grade->nilai_kuis ?? '-' }}</td><td class="px-5 py-4">{{ $grade->nilai_uts ?? '-' }}</td><td class="px-5 py-4">{{ $grade->nilai_uas ?? '-' }}</td><td class="px-5 py-4 font-extrabold text-cyan-800">{{ $grade->nilai_akhir ?? '-' }}</td></tr>@empty<tr><td colspan="8" class="px-5 py-8 text-center text-slate-500">Tidak ada nilai pada tahun ini.</td></tr>@endforelse</tbody>
                            @break

                        @case('materials')
                        @case('assignments')
                            <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500"><tr><th class="px-5 py-3">Judul</th><th class="px-5 py-3">Guru</th><th class="px-5 py-3">Mapel</th><th class="px-5 py-3">Kelas</th><th class="px-5 py-3">Detail</th></tr></thead>
                            <tbody class="divide-y divide-slate-100">@forelse($activeRows as $item)<tr><td class="px-5 py-4 font-semibold">{{ $item->judul }}</td><td class="px-5 py-4">{{ $item->teacher->nama ?? '-' }}</td><td class="px-5 py-4">{{ $item->subject->nama_mapel ?? '-' }}</td><td class="px-5 py-4">{{ $item->schoolClass->nama_kelas ?? '-' }}</td><td class="px-5 py-4">{{ $activeTab === 'assignments' ? (($item->deadline?->format('d M Y H:i') ?? '-') . ' - ' . ($item->submissions_count ?? 0) . ' pengumpulan') : ($item->deskripsi ?? '-') }}</td></tr>@empty<tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">Tidak ada data pada tahun ini.</td></tr>@endforelse</tbody>
                            @break

                        @case('submissions')
                            <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500"><tr><th class="px-5 py-3">Siswa</th><th class="px-5 py-3">Tugas</th><th class="px-5 py-3">Tanggal</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Nilai</th><th class="px-5 py-3">Detail</th></tr></thead>
                            <tbody class="divide-y divide-slate-100">@forelse($activeRows as $submission)<tr><td class="px-5 py-4 font-semibold">{{ $submission->student->nama ?? '-' }}</td><td class="px-5 py-4">{{ $submission->assignment->judul ?? '-' }}</td><td class="px-5 py-4">{{ $submission->tanggal_kumpul?->format('d M Y H:i') ?? '-' }}</td><td class="px-5 py-4">{{ $submission->status }}</td><td class="px-5 py-4">{{ $submission->nilai ?? '-' }}</td><td class="px-5 py-4">{{ $submission->catatan ?? '-' }}</td></tr>@empty<tr><td colspan="6" class="px-5 py-8 text-center text-slate-500">Tidak ada pengumpulan tugas pada tahun ini.</td></tr>@endforelse</tbody>
                            @break

                        @case('announcements')
                            <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500"><tr><th class="px-5 py-3">Judul</th><th class="px-5 py-3">Audiens</th><th class="px-5 py-3">Kelas</th><th class="px-5 py-3">Mapel</th><th class="px-5 py-3">Penulis</th><th class="px-5 py-3">Status</th></tr></thead>
                            <tbody class="divide-y divide-slate-100">@forelse($activeRows as $announcement)<tr><td class="px-5 py-4 font-semibold">{{ $announcement->judul }}</td><td class="px-5 py-4">{{ $announcement->audienceLabel() }}</td><td class="px-5 py-4">{{ $announcement->schoolClass->nama_kelas ?? '-' }}</td><td class="px-5 py-4">{{ $announcement->subject->nama_mapel ?? '-' }}</td><td class="px-5 py-4">{{ $announcement->author->name ?? '-' }}</td><td class="px-5 py-4">{{ $announcement->status_aktif ? 'Aktif' : 'Nonaktif' }}</td></tr>@empty<tr><td colspan="6" class="px-5 py-8 text-center text-slate-500">Tidak ada pengumuman pada tahun ini.</td></tr>@endforelse</tbody>
                            @break

                        @case('alumni')
                            <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500"><tr><th class="px-5 py-3">Alumni</th><th class="px-5 py-3">NIS</th><th class="px-5 py-3">Kelas Terakhir</th><th class="px-5 py-3">Tahun Lulus</th><th class="px-5 py-3">Tanggal Lulus</th></tr></thead>
                            <tbody class="divide-y divide-slate-100">@forelse($activeRows as $alumni)<tr><td class="px-5 py-4 font-semibold">{{ $alumni->student->nama ?? '-' }}</td><td class="px-5 py-4">{{ $alumni->student->nis ?? '-' }}</td><td class="px-5 py-4">{{ $alumni->student->schoolClass->nama_kelas ?? '-' }}</td><td class="px-5 py-4">{{ $alumni->academicYear->nama ?? '-' }}</td><td class="px-5 py-4">{{ $alumni->tanggal_lulus?->format('d M Y') ?? '-' }}</td></tr>@empty<tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">Tidak ada alumni pada tahun ini.</td></tr>@endforelse</tbody>
                            @break
                    @endswitch
                </table>
            </div>
            @if(method_exists($activeRows, 'links'))
                <div class="border-t border-slate-100 p-4">{{ $activeRows->links() }}</div>
            @endif
        @endif
    </section>

    <section class="mt-5 rounded-2xl bg-white/90 p-5 shadow-sm">
        <div class="mb-3 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900">Rekap Semua Tahun</h2>
                <p class="text-sm text-slate-500">Gambaran cepat agar perpindahan arsip antar tahun mudah dicek.</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500"><tr><th class="px-4 py-3">Tahun</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Guru</th><th class="px-4 py-3">Siswa</th><th class="px-4 py-3">Kelas</th><th class="px-4 py-3">Mapel</th><th class="px-4 py-3">Nilai</th><th class="px-4 py-3">Tugas</th><th class="px-4 py-3">Alumni</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($yearSummaries as $summary)
                        <tr><td class="px-4 py-3 font-extrabold">{{ $summary['year']->nama }}</td><td class="px-4 py-3">{{ $summary['year']->is_active ? 'Aktif' : 'Arsip' }}</td><td class="px-4 py-3">{{ $summary['teachers'] }}</td><td class="px-4 py-3">{{ $summary['students'] }}</td><td class="px-4 py-3">{{ $summary['classes'] }}</td><td class="px-4 py-3">{{ $summary['subjects'] }}</td><td class="px-4 py-3">{{ $summary['grades'] }}</td><td class="px-4 py-3">{{ $summary['assignments'] }}</td><td class="px-4 py-3">{{ $summary['alumni'] }}</td></tr>
                    @empty
                        <tr><td colspan="9" class="px-4 py-8 text-center text-slate-500">Belum ada tahun akademik.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-app-layout>
