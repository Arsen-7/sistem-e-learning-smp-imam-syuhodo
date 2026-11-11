<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Pengumpulan Tugas</h1>
            <p class="text-sm text-slate-500">Daftar jawaban tugas dari semua kelas yang diampu.</p>
        </div>
    </x-slot>

    <form method="GET" class="mb-6 grid gap-3 rounded-3xl bg-white/90 p-4 shadow-sm md:grid-cols-[1fr_220px_auto]">
        <input name="search" value="{{ $filters['search'] }}" class="rounded-2xl border-slate-200" placeholder="Cari siswa, NIS, atau tugas">
        <select name="status" class="rounded-2xl border-slate-200">
            <option value="">Semua status</option>
            <option value="graded" @selected($filters['status'] === 'graded')>Sudah dinilai</option>
            <option value="ungraded" @selected($filters['status'] === 'ungraded')>Belum dinilai</option>
        </select>
        <button class="rounded-2xl bg-cyan-700 px-5 py-2.5 text-sm font-bold text-white">Filter</button>
    </form>

    <div class="overflow-hidden rounded-3xl bg-white/90 shadow-sm">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                <tr>
                    <th class="px-5 py-3">Siswa</th>
                    <th class="px-5 py-3">Tugas</th>
                    <th class="px-5 py-3">Tanggal</th>
                    <th class="px-5 py-3">Jawaban</th>
                    <th class="px-5 py-3">Nilai dan Catatan</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($submissions as $submission)
                    <tr>
                        <td class="px-5 py-4">
                            <div class="font-semibold text-slate-900">{{ $submission->student->nama ?? '-' }}</div>
                            <div class="text-xs text-slate-500">NIS {{ $submission->student->nis ?? '-' }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-semibold text-slate-900">{{ $submission->assignment->judul ?? '-' }}</div>
                            <div class="text-xs text-slate-500">{{ $submission->assignment->subject->nama_mapel ?? '-' }} / {{ $submission->assignment->schoolClass->nama_kelas ?? '-' }}</div>
                        </td>
                        <td class="px-5 py-4">{{ optional($submission->tanggal_kumpul)->format('d M Y H:i') ?? '-' }}</td>
                        <td class="px-5 py-4">
                            @if($submission->file_jawaban)
                                <a href="{{ asset('storage/'.$submission->file_jawaban) }}" target="_blank" class="font-bold text-cyan-700">{{ $submission->fileName() }}</a>
                            @else
                                <span class="text-slate-400">File belum tersedia.</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if($submission->assignment)
                                <form method="POST" action="{{ route('teacher.assignments.submissions.grade', [$submission->assignment, $submission]) }}" class="grid min-w-72 gap-2 md:grid-cols-[88px_1fr_auto]">
                                    @csrf
                                    @method('PUT')
                                    <input type="number" step="0.01" min="0" max="100" name="nilai" value="{{ old('nilai', $submission->nilai) }}" class="rounded-xl border-slate-200 text-sm" placeholder="Nilai" required>
                                    <input name="catatan" value="{{ old('catatan', $submission->catatan) }}" class="rounded-xl border-slate-200 text-sm" placeholder="Feedback untuk siswa">
                                    <button class="rounded-xl bg-cyan-700 px-3 py-2 text-xs font-bold text-white">{{ $submission->nilai !== null ? 'Perbarui' : 'Nilai' }}</button>
                                </form>
                            @else
                                <span class="text-slate-400">Tugas tidak tersedia.</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            @if($submission->assignment)
                                <a href="{{ route('teacher.assignments.show', ['assignment' => $submission->assignment, 'search' => $submission->student->nis ?? $submission->student->nama ?? null, 'status' => 'submitted']) }}" class="app-action-button app-action-button--detail">Detail</a>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-slate-500">Belum ada pengumpulan tugas.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-4">{{ $submissions->links() }}</div>
    </div>
</x-app-layout>
