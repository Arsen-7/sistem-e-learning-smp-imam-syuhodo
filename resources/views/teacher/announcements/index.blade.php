<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">Pengumuman Kelas</h1>
                <p class="text-sm text-slate-500">Buat dan pantau pengumuman untuk kelas yang diampu.</p>
            </div>
            <a href="{{ route('teacher.announcements.create') }}" class="rounded-2xl bg-cyan-700 px-4 py-2 text-sm font-bold text-white">Tambah Pengumuman</a>
        </div>
    </x-slot>

    <div class="space-y-4">
        @forelse($announcements as $announcement)
            <article id="pengumuman-{{ $announcement->id }}" class="rounded-3xl bg-white/90 p-6 shadow-sm ring-1 {{ (int) request('highlight') === $announcement->id ? 'ring-cyan-300' : 'ring-white/60' }}">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap gap-2 text-xs font-bold">
                            <span class="rounded-full bg-cyan-50 px-3 py-1 text-cyan-700">{{ $announcement->audienceLabel() }}</span>
                            <span class="rounded-full bg-blue-50 px-3 py-1 text-blue-700">{{ $announcement->subject->nama_mapel ?? '-' }}</span>
                            <span class="rounded-full {{ $announcement->status_aktif ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }} px-3 py-1">{{ $announcement->status_aktif ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>
                        <h2 class="mt-3 text-xl font-black text-slate-900">{{ $announcement->judul }}</h2>
                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $announcement->isi }}</p>
                        <div class="mt-3 text-xs text-slate-500">Tujuan {{ $announcement->schoolClass->nama_kelas ?? '-' }} - {{ $announcement->subject->nama_mapel ?? '-' }}. Oleh {{ $announcement->author->name ?? '-' }}</div>
                    </div>
                    @if($announcement->user_id === auth()->id())
                        <div class="app-action-bar">
                            <a href="{{ route('teacher.announcements.edit', $announcement) }}" class="app-action-button app-action-button--edit">Edit</a>
                            <form method="POST" action="{{ route('teacher.announcements.destroy', $announcement) }}" data-confirm-title="Konfirmasi Hapus" data-confirm="Hapus pengumuman ini?" data-confirm-yes="Hapus" data-confirm-no="Tidak">
                                @csrf
                                @method('DELETE')
                                <button class="app-action-button app-action-button--delete">Hapus</button>
                            </form>
                        </div>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-3xl bg-white/90 p-8 text-center text-slate-500">Belum ada pengumuman.</div>
        @endforelse
    </div>

    <div class="mt-5">{{ $announcements->links() }}</div>
</x-app-layout>
