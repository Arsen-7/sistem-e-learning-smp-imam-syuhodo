<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Pengumpulan Tugas</h1>
            <p class="text-sm text-slate-500">{{ $assignment->judul }} - {{ $assignment->subject->nama_mapel ?? '-' }} - {{ $assignment->schoolClass->nama_kelas ?? '-' }}</p>
        </div>
    </x-slot>

    <div class="mb-6 rounded-3xl bg-white/90 p-6 shadow-sm">
        <div class="mb-5 flex flex-wrap justify-end gap-2">
            <a href="{{ route('teacher.assignments.grade-report', $assignment) }}" target="_blank" class="app-action-button app-action-button--detail">Cetak Laporan</a>
            <a href="{{ route('teacher.assignments.grade-report', ['assignment' => $assignment, 'export' => 'csv']) }}" class="app-action-button app-action-button--edit">Export CSV</a>
        </div>
        <div class="grid gap-4 md:grid-cols-4">
            <div>
                <div class="text-sm font-semibold text-slate-500">Deadline</div>
                <div class="mt-1 font-bold text-slate-900">{{ $assignment->deadline->format('d M Y H:i') }}</div>
            </div>
            <div>
                <div class="text-sm font-semibold text-slate-500">Status</div>
                <div class="mt-1 font-bold {{ $assignment->status_aktif ? 'text-emerald-700' : 'text-slate-500' }}">{{ $assignment->status_aktif ? 'Aktif' : 'Nonaktif' }}</div>
            </div>
            <div>
                <div class="text-sm font-semibold text-slate-500">Sudah Mengumpulkan</div>
                <div class="mt-1 font-bold text-cyan-800">{{ $submittedCount }} dari {{ $totalStudents }} siswa</div>
            </div>
            <div>
                <div class="text-sm font-semibold text-slate-500">File Pendukung</div>
                @if($assignment->file)
                    <a href="{{ asset('storage/'.$assignment->file) }}" target="_blank" class="mt-1 inline-block font-bold text-cyan-700">Unduh file</a>
                @else
                    <div class="mt-1 text-slate-500">Tidak ada file</div>
                @endif
            </div>
        </div>
        @if($assignment->deskripsi)
            <div class="mt-5 whitespace-pre-line rounded-2xl bg-slate-50 p-4 text-sm text-slate-700">{{ $assignment->deskripsi }}</div>
        @endif
    </div>

    <form method="GET" class="mb-6 grid gap-3 rounded-3xl bg-white/90 p-4 shadow-sm md:grid-cols-[1fr_220px_auto]">
        <input name="search" value="{{ $filters['search'] }}" class="rounded-2xl border-slate-200" placeholder="Cari nama atau NIS">
        <select name="status" class="rounded-2xl border-slate-200">
            <option value="">Semua status</option>
            <option value="submitted" @selected($filters['status'] === 'submitted')>Sudah mengumpulkan</option>
            <option value="missing" @selected($filters['status'] === 'missing')>Belum mengumpulkan</option>
        </select>
        <button class="rounded-2xl bg-cyan-700 px-5 py-2.5 text-sm font-bold text-white">Filter</button>
    </form>

    <div class="overflow-hidden rounded-3xl bg-white/90 shadow-sm">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                <tr>
                    <th class="px-5 py-3">Siswa</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">Jawaban</th>
                    <th class="px-5 py-3">Nilai dan Catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($students as $student)
                    @php($submission = $submissions->get($student->id))
                    <tr>
                        <td class="px-5 py-4">
                            <div class="font-semibold text-slate-900">{{ $student->nama }}</div>
                            <div class="text-xs text-slate-500">NIS {{ $student->nis }}</div>
                        </td>
                        <td class="px-5 py-4">
                            @if($submission)
                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">{{ $submission->nilai !== null ? 'Sudah Dinilai' : 'Sudah Dikumpulkan' }}</span>
                                <div class="mt-1 text-xs text-slate-500">{{ optional($submission->tanggal_kumpul)->format('d M Y H:i') ?? '-' }}</div>
                            @else
                                <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">Belum Mengumpulkan</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if($submission)
                                <div class="font-semibold text-slate-900">{{ $submission->fileName() }}</div>
                                <div class="mt-1 text-xs text-slate-500">
                                    Upload {{ optional($submission->tanggal_kumpul)->format('d M Y H:i') ?? '-' }}
                                    @if($submission->file_size)
                                        - {{ number_format($submission->file_size / 1024, 1) }} KB
                                    @endif
                                </div>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @if($submission->file_jawaban)
                                        <a href="{{ asset('storage/'.$submission->file_jawaban) }}" target="_blank" class="app-action-button app-action-button--detail">Lihat</a>
                                        <a href="{{ asset('storage/'.$submission->file_jawaban) }}" download="{{ $submission->fileName() }}" class="app-action-button app-action-button--edit">Download</a>
                                    @else
                                        <span class="text-slate-400">File belum tersedia.</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if($submission)
                                <form method="POST" action="{{ route('teacher.assignments.submissions.grade', [$assignment, $submission]) }}" class="grid gap-2 md:grid-cols-[120px_1fr_auto]">
                                    @csrf
                                    @method('PUT')
                                    <input type="number" step="0.01" min="0" max="100" name="nilai" value="{{ old('nilai', $submission->nilai) }}" class="rounded-2xl border-slate-200" placeholder="Nilai" required>
                                    <input name="catatan" value="{{ old('catatan', $submission->catatan) }}" class="rounded-2xl border-slate-200" placeholder="Catatan untuk siswa">
                                    <button class="rounded-2xl bg-cyan-700 px-4 py-2 text-xs font-bold text-white">Simpan</button>
                                </form>
                            @else
                                <span class="text-slate-400">Menunggu pengumpulan.</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">Belum ada siswa di kelas ini.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-4">{{ $students->links() }}</div>
    </div>
</x-app-layout>
