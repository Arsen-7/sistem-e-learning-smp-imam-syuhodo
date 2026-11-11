<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Detail Alumni</h1>
            <p class="text-sm text-slate-500">{{ $alumni->student->nama ?? '-' }} - {{ $alumni->student->nis ?? '-' }}</p>
        </div>
    </x-slot>

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-3xl bg-white/90 p-5 shadow-sm"><div class="text-xs font-bold uppercase text-slate-500">Nama</div><div class="mt-1 font-extrabold">{{ $alumni->student->nama ?? '-' }}</div></div>
        <div class="rounded-3xl bg-white/90 p-5 shadow-sm"><div class="text-xs font-bold uppercase text-slate-500">NIS/NISN</div><div class="mt-1 font-extrabold">{{ $alumni->student->nis ?? '-' }}</div></div>
        <div class="rounded-3xl bg-white/90 p-5 shadow-sm"><div class="text-xs font-bold uppercase text-slate-500">Kelas Terakhir</div><div class="mt-1 font-extrabold">{{ $alumni->student->schoolClass->nama_kelas ?? '-' }}</div></div>
        <div class="rounded-3xl bg-white/90 p-5 shadow-sm"><div class="text-xs font-bold uppercase text-slate-500">Tahun Lulus</div><div class="mt-1 font-extrabold">{{ $alumni->academicYear->nama ?? '-' }}</div></div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="rounded-3xl bg-white/90 p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-extrabold text-slate-900">Riwayat Kelas</h2>
            @forelse($alumni->student->classHistories as $history)
                <div class="mb-2 rounded-2xl bg-slate-50 p-4 text-sm">
                    <div class="font-bold">{{ $history->schoolClass->nama_kelas ?? '-' }}</div>
                    <div>{{ $history->academicYear->nama ?? '-' }} - {{ $history->status_siswa }}</div>
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada riwayat kelas.</p>
            @endforelse
        </div>

        <div class="rounded-3xl bg-white/90 p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-extrabold text-slate-900">Riwayat Nilai</h2>
            @forelse($alumni->student->grades as $grade)
                <div class="mb-2 flex justify-between rounded-2xl bg-slate-50 p-4 text-sm">
                    <span>{{ $grade->subject->nama_mapel ?? '-' }} - {{ $grade->schoolClass->nama_kelas ?? '-' }}</span>
                    <span class="font-bold text-cyan-800">{{ $grade->nilai_akhir ?? '-' }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada riwayat nilai.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
