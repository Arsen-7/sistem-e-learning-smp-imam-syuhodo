<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Materi Pembelajaran</h1>
            <p class="text-sm text-slate-500">Daftar materi sesuai kelas dan mata pelajaran siswa.</p>
        </div>
    </x-slot>

    <form method="GET" class="mb-5 grid gap-3 rounded-2xl bg-white/90 p-4 shadow-sm md:grid-cols-[1fr_auto]">
        <select name="subject_id" class="rounded-xl border-slate-200 text-sm">
            <option value="">Semua Mapel</option>
            @foreach($subjects as $subject)
                <option value="{{ $subject->id }}" @selected((int) $selectedSubject === $subject->id)>{{ $subject->nama_mapel }}</option>
            @endforeach
        </select>
        <button class="rounded-xl bg-cyan-700 px-5 py-2 text-sm font-bold text-white">Filter</button>
    </form>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse($materials as $material)
            <div class="rounded-3xl bg-white/90 p-5 shadow-sm">
                <div class="text-xs font-bold uppercase tracking-wide text-cyan-700">{{ $material->subject->nama_mapel ?? '-' }} - {{ $material->schoolClass->nama_kelas ?? '-' }}</div>
                <h2 class="mt-2 text-lg font-extrabold text-slate-900">{{ $material->judul }}</h2>
                <p class="mt-2 line-clamp-3 text-sm text-slate-600">{{ $material->deskripsi ?: 'Tidak ada deskripsi.' }}</p>
                <div class="mt-4 text-xs text-slate-500">Guru: {{ $material->teacher->nama ?? '-' }}</div>
                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('student.materials.show', $material) }}" class="app-action-button app-action-button--detail">Detail</a>
                    @if($material->file)
                        <a href="{{ asset('storage/'.$material->file) }}" target="_blank" class="app-action-button app-action-button--detail">Unduh File</a>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-3xl bg-white/90 p-8 text-center text-slate-500 md:col-span-2 xl:col-span-3">Belum ada materi pembelajaran untuk filter ini.</div>
        @endforelse
    </div>

    <div class="mt-5">{{ $materials->links() }}</div>
</x-app-layout>
