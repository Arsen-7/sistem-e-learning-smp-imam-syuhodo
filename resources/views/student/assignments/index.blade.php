<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Tugas Siswa</h1>
            <p class="text-sm text-slate-500">Lihat tugas aktif dan status pengumpulan jawaban.</p>
        </div>
    </x-slot>

    <form method="GET" class="mb-5 grid gap-3 rounded-2xl bg-white/90 p-4 shadow-sm md:grid-cols-[1fr_auto]">
        <select name="subject_id" class="rounded-xl border-slate-200 text-sm">
            <option value="">Semua Mapel</option>
            @foreach($subjects as $subject)
                <option value="{{ $subject->id }}" @selected((int) $selectedSubject === $subject->id)>{{ $subject->nama_mapel }}</option>
            @endforeach
        </select>
        <button class="rounded-xl bg-cyan-700 px-5 py-2 text-sm font-bold text-white">Filter</button>
    </form>

    <div class="overflow-hidden rounded-3xl bg-white/90 shadow-sm">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                <tr>
                    <th class="px-5 py-3">Tugas</th>
                    <th class="px-5 py-3">Mapel</th>
                    <th class="px-5 py-3">Deadline</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($assignments as $assignment)
                    @php($submission = $assignment->submissions->first())
                    <tr>
                        <td class="px-5 py-4 font-semibold text-slate-900">{{ $assignment->judul }}</td>
                        <td class="px-5 py-4">{{ $assignment->subject->nama_mapel ?? '-' }}</td>
                        <td class="px-5 py-4">{{ $assignment->deadline->format('d M Y H:i') }}</td>
                        <td class="px-5 py-4">
                            @if($submission)
                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">{{ ucfirst($submission->status) }}</span>
                            @elseif(now()->greaterThan($assignment->deadline))
                                <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700">Melewati Deadline</span>
                            @else
                                <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">Belum Dikumpulkan</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('student.assignments.show', $assignment) }}" class="app-action-button app-action-button--detail">
                                {{ $submission ? 'Perbarui Jawaban' : 'Kumpulkan Tugas' }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">Belum ada tugas aktif.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-4">{{ $assignments->links() }}</div>
    </div>
</x-app-layout>
