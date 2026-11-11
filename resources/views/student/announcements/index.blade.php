<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Pengumuman</h1>
            <p class="text-sm text-slate-500">Pengumuman sekolah dan kelas yang relevan untuk akun kamu.</p>
        </div>
    </x-slot>

    <div class="space-y-4">
        @forelse($announcements as $announcement)
            <article id="pengumuman-{{ $announcement->id }}" class="rounded-3xl bg-white/90 p-6 shadow-sm ring-1 {{ (int) request('highlight') === $announcement->id ? 'ring-cyan-300' : 'ring-white/60' }}">
                <div class="flex flex-wrap gap-2 text-xs font-bold">
                    <span class="rounded-full bg-cyan-50 px-3 py-1 text-cyan-700">{{ $announcement->audienceLabel() }}</span>
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-blue-700">{{ $announcement->subject->nama_mapel ?? '-' }}</span>
                    @if($announcement->publish_at)
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-600">{{ $announcement->publish_at->format('d M Y H:i') }}</span>
                    @endif
                </div>
                <h2 class="mt-3 text-xl font-black text-slate-900">{{ $announcement->judul }}</h2>
                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $announcement->isi }}</p>
                <div class="mt-3 text-xs text-slate-500">Tujuan {{ $announcement->schoolClass->nama_kelas ?? '-' }} - {{ $announcement->subject->nama_mapel ?? '-' }}. Oleh {{ $announcement->author->name ?? '-' }}</div>
            </article>
        @empty
            <div class="rounded-3xl bg-white/90 p-8 text-center text-slate-500">Belum ada pengumuman.</div>
        @endforelse
    </div>

    <div class="mt-5">{{ $announcements->links() }}</div>
</x-app-layout>
