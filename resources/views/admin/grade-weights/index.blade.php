<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">Kelola Bobot Nilai</h1>
                <p class="text-sm text-slate-500">Total bobot tugas, kuis, UTS, dan UAS harus 100%.</p>
            </div>
            <a href="{{ route('admin.grade-weights.create') }}" class="rounded-2xl bg-cyan-700 px-4 py-2 text-sm font-bold text-white">Tambah Bobot</a>
        </div>
    </x-slot>

    <div class="overflow-hidden rounded-3xl bg-white/90 shadow-sm">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500"><tr><th class="px-5 py-3">Mapel</th><th class="px-5 py-3">Kelas</th><th class="px-5 py-3">Tugas</th><th class="px-5 py-3">Kuis</th><th class="px-5 py-3">UTS</th><th class="px-5 py-3">UAS</th><th class="px-5 py-3">KKM</th><th class="px-5 py-3 text-right">Aksi</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($gradeWeights as $weight)
                    <tr>
                        <td class="px-5 py-4 font-semibold">{{ $weight->subject->nama_mapel ?? '-' }}</td>
                        <td class="px-5 py-4">{{ $weight->schoolClass->nama_kelas ?? '-' }}</td>
                        <td class="px-5 py-4">{{ $weight->bobot_tugas }}%</td>
                        <td class="px-5 py-4">{{ $weight->bobot_kuis }}%</td>
                        <td class="px-5 py-4">{{ $weight->bobot_uts }}%</td>
                        <td class="px-5 py-4">{{ $weight->bobot_uas }}%</td>
                        <td class="px-5 py-4">{{ $weight->kkm }}</td>
                        <td class="px-5 py-4 text-right"><div class="app-action-bar"><a href="{{ route('admin.grade-weights.edit', $weight) }}" class="app-action-button app-action-button--edit">Edit</a><form method="POST" action="{{ route('admin.grade-weights.destroy', $weight) }}" data-confirm-title="Konfirmasi Hapus" data-confirm="Hapus bobot nilai ini?" data-confirm-yes="Hapus" data-confirm-no="Tidak">@csrf @method('DELETE')<button class="app-action-button app-action-button--delete">Hapus</button></form></div></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-5 py-8 text-center text-slate-500">Belum ada data bobot nilai.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-4">{{ $gradeWeights->links() }}</div>
    </div>
</x-app-layout>
