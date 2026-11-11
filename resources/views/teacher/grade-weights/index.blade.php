<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Bobot Nilai</h1>
            <p class="text-sm text-slate-500">Atur bobot tugas, kuis, UTS, UAS, dan KKM untuk kelas serta mata pelajaran yang diampu.</p>
        </div>
    </x-slot>

    <div class="overflow-hidden rounded-3xl bg-white/90 shadow-sm">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                <tr>
                    <th class="px-5 py-3">Kelas</th>
                    <th class="px-5 py-3">Mata Pelajaran</th>
                    <th class="px-5 py-3">Tugas</th>
                    <th class="px-5 py-3">Kuis</th>
                    <th class="px-5 py-3">UTS</th>
                    <th class="px-5 py-3">UAS</th>
                    <th class="px-5 py-3">KKM</th>
                    <th class="px-5 py-3">Total</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($weights as $weight)
                    <tr>
                        <td class="px-5 py-4 font-semibold text-slate-900">{{ $weight->schoolClass->nama_kelas ?? '-' }}</td>
                        <td class="px-5 py-4">{{ $weight->subject->nama_mapel ?? '-' }}</td>
                        <td class="px-5 py-4">{{ $weight->bobot_tugas }}%</td>
                        <td class="px-5 py-4">{{ $weight->bobot_kuis }}%</td>
                        <td class="px-5 py-4">{{ $weight->bobot_uts }}%</td>
                        <td class="px-5 py-4">{{ $weight->bobot_uas }}%</td>
                        <td class="px-5 py-4">{{ $weight->kkm }}</td>
                        <td class="px-5 py-4">
                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ abs($weight->totalBobot() - 100) < 0.001 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">{{ $weight->totalBobot() }}%</span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('teacher.grade-weights.edit', $weight) }}" class="app-action-button app-action-button--edit">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-5 py-8 text-center text-slate-500">Belum ada bobot nilai untuk mata pelajaran yang diampu.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>
