<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Detail Materi</h1>
            <p class="text-sm text-slate-500">{{ $material->subject->nama_mapel ?? '-' }} - {{ $material->schoolClass->nama_kelas ?? '-' }}</p>
        </div>
    </x-slot>

    <div class="rounded-3xl bg-white/90 p-6 shadow-sm">
        <div class="text-xs font-bold uppercase tracking-wide text-cyan-700">{{ $material->teacher->nama ?? '-' }}</div>
        <h2 class="mt-2 text-2xl font-black text-slate-900">{{ $material->judul }}</h2>
        <div class="mt-4 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $material->deskripsi ?: 'Tidak ada deskripsi materi.' }}</div>

        <div class="mt-6 flex flex-wrap gap-2">
            @if($material->file)
                <a href="{{ asset('storage/'.$material->file) }}" target="_blank" class="rounded-2xl bg-cyan-700 px-4 py-2 text-sm font-bold text-white">Unduh Materi</a>
            @endif
            <a href="{{ route('student.materials.index') }}" class="rounded-2xl bg-slate-100 px-4 py-2 text-sm font-bold text-slate-700">Kembali</a>
        </div>
    </div>
</x-app-layout>
