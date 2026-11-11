<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div><h1 class="text-2xl font-extrabold text-slate-900">Kelola Tugas</h1><p class="text-sm text-slate-500">Buat tugas, lihat pengumpulan, dan beri nilai tugas.</p></div>
            <a href="{{ route('teacher.assignments.create') }}" class="rounded-2xl bg-cyan-700 px-4 py-2 text-sm font-bold text-white">Tambah Tugas</a>
        </div>
    </x-slot>

    <div class="overflow-hidden rounded-3xl bg-white/90 shadow-sm">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500"><tr><th class="px-5 py-3">Tugas</th><th class="px-5 py-3">Mapel/Kelas</th><th class="px-5 py-3">Deadline</th><th class="px-5 py-3">Pengumpulan</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Aksi</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($assignments as $assignment)
                    <tr>
                        <td class="px-5 py-4 font-semibold">{{ $assignment->judul }}</td>
                        <td class="px-5 py-4">{{ $assignment->subject->nama_mapel ?? '-' }} / {{ $assignment->schoolClass->nama_kelas ?? '-' }}</td>
                        <td class="px-5 py-4">{{ $assignment->deadline->format('d M Y H:i') }}</td>
                        <td class="px-5 py-4">{{ $assignment->submissions_count }}</td>
                        <td class="px-5 py-4"><span class="rounded-full px-3 py-1 text-xs font-bold {{ $assignment->status_aktif ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $assignment->status_aktif ? 'Aktif' : 'Nonaktif' }}</span></td>
                        <td class="px-5 py-4 text-right"><div class="app-action-bar"><a href="{{ route('teacher.assignments.show', $assignment) }}" class="app-action-button app-action-button--detail">Pengumpulan</a><a href="{{ route('teacher.assignments.edit', $assignment) }}" class="app-action-button app-action-button--edit">Edit</a><form method="POST" action="{{ route('teacher.assignments.destroy', $assignment) }}" data-confirm-title="Konfirmasi Hapus" data-confirm="Hapus tugas ini?" data-confirm-yes="Hapus" data-confirm-no="Tidak">@csrf @method('DELETE')<button class="app-action-button app-action-button--delete">Hapus</button></form></div></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-slate-500">Belum ada tugas.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-4">{{ $assignments->links() }}</div>
    </div>
</x-app-layout>
