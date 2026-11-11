<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Arsip Mengajar</h1>
            <p class="text-sm text-slate-500">Riwayat kelas dan mata pelajaran yang pernah diampu berdasarkan tahun ajaran.</p>
        </div>
    </x-slot>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse($histories as $history)
            <div class="rounded-3xl bg-white/90 p-5 shadow-sm">
                <div class="text-xs font-bold uppercase tracking-wide text-cyan-700">{{ $history->academicYear->nama ?? '-' }}</div>
                <div class="mt-2 text-lg font-extrabold text-slate-900">{{ $history->subject->nama_mapel ?? '-' }}</div>
                <div class="text-sm text-slate-500">Kelas {{ $history->schoolClass->nama_kelas ?? '-' }}</div>
            </div>
        @empty
            <div class="rounded-3xl bg-white/90 p-8 text-center text-slate-500 md:col-span-2 xl:col-span-3">Belum ada arsip mengajar.</div>
        @endforelse
    </div>

    <div class="mt-5">{{ $histories->links() }}</div>
</x-app-layout>
