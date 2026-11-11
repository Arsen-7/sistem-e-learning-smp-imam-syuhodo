<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Edit Bobot Nilai</h1>
            <p class="text-sm text-slate-500">{{ $gradeWeight->schoolClass->nama_kelas ?? '-' }} - {{ $gradeWeight->subject->nama_mapel ?? '-' }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('teacher.grade-weights.update', $gradeWeight) }}" class="rounded-3xl bg-white/90 p-6 shadow-sm">
        @csrf
        @method('PUT')

        <div class="grid gap-4 md:grid-cols-5">
            @foreach(['bobot_tugas' => 'Bobot Tugas (%)', 'bobot_kuis' => 'Bobot Kuis (%)', 'bobot_uts' => 'Bobot UTS (%)', 'bobot_uas' => 'Bobot UAS (%)', 'kkm' => 'KKM'] as $field => $label)
                <div>
                    <label class="mb-1 block text-sm font-bold text-slate-700">{{ $label }}</label>
                    <input type="number" step="0.01" min="0" max="100" name="{{ $field }}" value="{{ old($field, $gradeWeight->{$field}) }}" class="w-full rounded-2xl border-slate-200" required>
                </div>
            @endforeach
        </div>

        <p class="mt-3 text-sm font-semibold text-slate-500">Total bobot tugas, kuis, UTS, dan UAS wajib tepat 100%.</p>

        <div class="mt-5 flex gap-2">
            <button class="rounded-2xl bg-cyan-700 px-5 py-2.5 text-sm font-bold text-white">Simpan</button>
            <a href="{{ route('teacher.grade-weights.index') }}" class="rounded-2xl bg-white px-5 py-2.5 text-sm font-bold text-slate-600">Batal</a>
        </div>
    </form>
</x-app-layout>
