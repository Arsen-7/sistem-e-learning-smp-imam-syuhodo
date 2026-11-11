<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">Kelola Kelas</h1>
                <p class="text-sm text-slate-500">Data kelas dan wali kelas.</p>
            </div>
            <a href="{{ route('admin.classes.create') }}" class="rounded-2xl bg-cyan-700 px-4 py-2 text-sm font-bold text-white">Tambah Kelas</a>
        </div>
    </x-slot>

    <div class="mb-6 grid gap-4 md:grid-cols-3">
        @foreach($classSummaries as $summary)
            <div class="rounded-3xl bg-white/90 p-5 shadow-sm">
                <div class="text-sm font-semibold text-slate-500">Kelas {{ $summary['tingkat'] }}</div>
                <div class="mt-2 text-3xl font-black text-cyan-800">{{ $summary['jumlah_kelas'] }} kelas</div>
                <div class="mt-1 text-sm font-semibold text-slate-500">{{ $summary['jumlah_siswa'] }} siswa</div>
            </div>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-3xl bg-white/90 shadow-sm">
        <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500"><tr><th class="px-5 py-3">Kelas</th><th class="px-5 py-3">Tingkat</th><th class="px-5 py-3">Tahun Ajaran</th><th class="px-5 py-3">Wali Kelas</th><th class="px-5 py-3">Jumlah Siswa</th><th class="px-5 py-3">Daftar Siswa</th><th class="px-5 py-3 text-right">Aksi</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($classes as $class)
                    <tr class="align-top">
                        <td class="px-5 py-4 font-semibold align-top">
                            {{ $class->nama_kelas }}
                            @if($class->is_archived)
                                <span class="ml-2 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-600">Arsip</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 align-top">{{ $class->tingkat }}</td>
                        <td class="px-5 py-4 align-top">{{ $class->academicYear->nama ?? '-' }}</td>
                        <td class="px-5 py-4 align-top">{{ $class->waliKelas->nama ?? '-' }}</td>
                        <td class="px-5 py-4 align-top">{{ $class->students_count }}</td>
                        <td class="px-5 py-4 align-top">
                            @if($class->students_count > 0)
                                <div class="grid max-w-xl gap-1 sm:grid-cols-2">
                                    @foreach($class->students->take(6) as $student)
                                        <div class="text-xs leading-5 text-slate-600">
                                            <span class="font-bold text-slate-800">{{ $loop->iteration }}.</span>
                                            {{ $student->nama }}
                                            <span class="text-slate-400">({{ $student->nis }})</span>
                                        </div>
                                    @endforeach
                                </div>
                                @if($class->students_count > 6)
                                    <div class="mt-2 text-xs font-semibold text-cyan-700">
                                        +{{ $class->students_count - 6 }} siswa lainnya. Buka Detail untuk daftar lengkap.
                                    </div>
                                @endif
                            @else
                                <div class="text-xs text-slate-400">Belum ada siswa.</div>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right align-top">
                            <div class="app-action-bar">
                                <a href="{{ route('admin.classes.show', $class) }}" class="app-action-button app-action-button--detail">Detail</a>
                                <a href="{{ route('admin.classes.edit', $class) }}" class="app-action-button app-action-button--edit">Edit</a>
                                <form method="POST" action="{{ route('admin.classes.destroy', $class) }}" data-confirm-title="Konfirmasi Hapus" data-confirm="Hapus kelas ini?" data-confirm-yes="Hapus" data-confirm-no="Tidak">@csrf @method('DELETE')<button class="app-action-button app-action-button--delete">Hapus</button></form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-8 text-center text-slate-500">Belum ada data kelas.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="px-5 py-4">{{ $classes->links() }}</div>
    </div>
</x-app-layout>
