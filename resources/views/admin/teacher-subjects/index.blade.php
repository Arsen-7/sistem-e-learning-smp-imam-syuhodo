<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">Relasi Guru dan Mata Pelajaran</h1>
                <p class="text-sm text-slate-500">Atur guru pengampu untuk kelas dan mapel tertentu.</p>
            </div>
            <a href="{{ route('admin.teacher-subjects.create') }}" class="rounded-2xl bg-cyan-700 px-4 py-2 text-sm font-bold text-white">Tambah Relasi</a>
        </div>
    </x-slot>

    <div class="overflow-hidden rounded-3xl bg-white/90 shadow-sm">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500"><tr><th class="px-5 py-3">Guru</th><th class="px-5 py-3">Mata Pelajaran</th><th class="px-5 py-3">Kelas</th><th class="px-5 py-3 text-right">Aksi</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($teacherSubjects as $item)
                    <tr>
                        <td class="px-5 py-4 font-semibold">{{ $item->teacher->nama ?? '-' }}</td>
                        <td class="px-5 py-4">{{ $item->subject->nama_mapel ?? '-' }}</td>
                        <td class="px-5 py-4">{{ $item->schoolClass->nama_kelas ?? '-' }}</td>
                        <td class="px-5 py-4 text-right"><div class="app-action-bar"><a href="{{ route('admin.teacher-subjects.edit', $item) }}" class="app-action-button app-action-button--edit">Edit</a><form method="POST" action="{{ route('admin.teacher-subjects.destroy', $item) }}" data-confirm-title="Konfirmasi Hapus" data-confirm="Hapus relasi pengampu ini?" data-confirm-yes="Hapus" data-confirm-no="Tidak">@csrf @method('DELETE')<button class="app-action-button app-action-button--delete">Hapus</button></form></div></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500">Belum ada relasi pengampu.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-4">{{ $teacherSubjects->links() }}</div>
    </div>
</x-app-layout>
