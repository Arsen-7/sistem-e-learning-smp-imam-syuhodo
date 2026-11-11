<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Detail Tugas</h1>
            <p class="text-sm text-slate-500">{{ $assignment->subject->nama_mapel ?? '-' }} - Deadline {{ $assignment->deadline->format('d M Y H:i') }}</p>
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-3xl bg-white/90 p-6 shadow-sm lg:col-span-2">
            <div class="text-xs font-bold uppercase tracking-wide text-cyan-700">{{ $assignment->teacher->nama ?? '-' }} - {{ $assignment->schoolClass->nama_kelas ?? '-' }}</div>
            <h2 class="mt-2 text-2xl font-black text-slate-900">{{ $assignment->judul }}</h2>
            <div class="mt-4 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $assignment->deskripsi ?: 'Tidak ada deskripsi tugas.' }}</div>

            <div class="mt-6 flex flex-wrap gap-2">
                @if($assignment->file)
                    <a href="{{ asset('storage/'.$assignment->file) }}" target="_blank" class="rounded-2xl bg-slate-100 px-4 py-2 text-sm font-bold text-slate-700">Unduh File Pendukung</a>
                @endif
                <a href="{{ route('student.assignments.index') }}" class="rounded-2xl bg-white px-4 py-2 text-sm font-bold text-slate-600">Kembali</a>
            </div>
        </div>

        <div class="rounded-3xl bg-white/90 p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-extrabold text-slate-900">Pengumpulan Jawaban</h2>

            @if($submission)
                <div class="mb-4 rounded-2xl bg-slate-50 p-4 text-sm">
                    <div class="font-bold text-slate-800">Status: {{ $submission->nilai !== null ? 'Sudah Dinilai' : 'Sudah Dikumpulkan' }}</div>
                    <div class="mt-1 text-slate-500">Tanggal kumpul: {{ $submission->tanggal_kumpul->format('d M Y H:i') }}</div>
                    <div class="mt-1 text-slate-500">Nilai: <span class="font-bold text-cyan-800">{{ $submission->nilai ?? 'Belum dinilai' }}</span></div>
                    @if($submission->catatan)
                        <div class="mt-2 rounded-xl bg-white p-3 text-slate-600">{{ $submission->catatan }}</div>
                    @endif
                    <a href="{{ asset('storage/'.$submission->file_jawaban) }}" target="_blank" class="mt-3 inline-block text-sm font-bold text-cyan-700">Lihat jawaban terkirim</a>
                </div>
            @endif

            @if(! $submission || $submission->nilai === null)
            <form method="POST" action="{{ route('student.assignments.submit', $assignment) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-bold text-slate-700">Upload File Jawaban</label>
                    <input type="file" name="file_jawaban" class="w-full rounded-2xl border border-slate-200 bg-white p-2" required>
                    <p class="mt-1 text-xs text-slate-500">Format: PDF, Word, Excel, ZIP, JPG, PNG. Maksimal 5 MB.</p>
                    @if($submission)
                        <p class="mt-1 text-xs text-slate-500">Upload baru akan mengganti jawaban yang sudah terkirim.</p>
                    @endif
                </div>
                <button class="w-full rounded-2xl bg-cyan-700 px-4 py-2.5 text-sm font-bold text-white">{{ $submission ? 'Perbarui Jawaban' : 'Kumpulkan Tugas' }}</button>
            </form>
            @else
                <p class="rounded-2xl bg-emerald-50 p-3 text-sm font-semibold text-emerald-700">Tugas telah dinilai; jawaban tidak dapat diubah.</p>
            @endif
        </div>
    </div>
</x-app-layout>
