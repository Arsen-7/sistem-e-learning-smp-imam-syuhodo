<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div><h1 class="text-2xl font-extrabold text-slate-900">Kelola Materi</h1><p class="text-sm text-slate-500">Daftar materi berdasarkan kelas dan mata pelajaran yang diampu.</p></div>
            <a href="{{ route('teacher.materials.create') }}" class="rounded-2xl bg-cyan-700 px-4 py-2 text-sm font-bold text-white">Tambah Materi</a>
        </div>
    </x-slot>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse($materials as $material)
            <div class="rounded-3xl bg-white/90 p-5 shadow-sm">
                <div class="text-xs font-bold uppercase tracking-wide text-cyan-700">{{ $material->subject->nama_mapel ?? '-' }} - {{ $material->schoolClass->nama_kelas ?? '-' }}</div>
                <h2 class="mt-2 text-lg font-extrabold text-slate-900">{{ $material->judul }}</h2>
                <p class="mt-2 line-clamp-3 text-sm text-slate-600">{{ $material->deskripsi }}</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    @if($material->file)<a href="{{ asset('storage/'.$material->file) }}" class="app-action-button app-action-button--detail" target="_blank">Unduh File</a>@endif
                    <a href="{{ route('teacher.materials.edit', $material) }}" class="app-action-button app-action-button--edit">Edit</a>
                    <form method="POST" action="{{ route('teacher.materials.destroy', $material) }}" data-confirm-title="Konfirmasi Hapus" data-confirm="Hapus materi ini?" data-confirm-yes="Hapus" data-confirm-no="Tidak">@csrf @method('DELETE')<button class="app-action-button app-action-button--delete">Hapus</button></form>
                </div>
            </div>
        @empty
            <div class="rounded-3xl bg-white/90 p-8 text-center text-slate-500 md:col-span-2 xl:col-span-3">Belum ada materi pembelajaran.</div>
        @endforelse
    </div>
    <div class="mt-5">{{ $materials->links() }}</div>
</x-app-layout>
