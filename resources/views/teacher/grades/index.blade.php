<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Input Nilai Siswa</h1>
            <p class="text-sm text-slate-500">Input nilai tugas, kuis, UTS, dan UAS. Nilai akhir dihitung otomatis berdasarkan bobot.</p>
        </div>
    </x-slot>

    @php
        $initialSubject = request('subject_id', $selectedPair?->subject_id ?? '');
        $initialClass = request('class_id', $selectedPair?->class_id ?? '');
    @endphp

    <form method="GET" x-data="{ pair: '{{ $initialSubject }}|{{ $initialClass }}', sync() { const parts = this.pair.split('|'); this.$refs.subject.value = parts[0] || ''; this.$refs.classId.value = parts[1] || ''; } }" x-init="sync()" class="mb-6 rounded-3xl bg-white/90 p-5 shadow-sm">
        <input type="hidden" name="subject_id" x-ref="subject" value="{{ $initialSubject }}">
        <input type="hidden" name="class_id" x-ref="classId" value="{{ $initialClass }}">
        <div class="grid gap-4 md:grid-cols-[1fr_auto]">
            <select x-model="pair" @change="sync()" class="rounded-2xl border-slate-200" @disabled($pairs->isEmpty())>
                @forelse($pairs as $pair)
                    <option value="{{ $pair->subject_id }}|{{ $pair->class_id }}">{{ $pair->schoolClass->nama_kelas ?? '-' }} - {{ $pair->subject->nama_mapel ?? '-' }}</option>
                @empty
                    <option value="">Belum ada kelas dan mata pelajaran diampu</option>
                @endforelse
            </select>
            <button class="rounded-2xl bg-cyan-700 px-5 py-2.5 text-sm font-bold text-white" @disabled($pairs->isEmpty())>Tampilkan</button>
        </div>
    </form>

    @if($selectedPair)
        <div class="mb-6 grid gap-4 md:grid-cols-2 lg:grid-cols-6">
            @foreach([
                ['label' => 'Jumlah Siswa', 'value' => $students->count()],
                ['label' => 'Bobot Tugas', 'value' => $weight?->bobot_tugas.'%'],
                ['label' => 'Bobot Kuis', 'value' => $weight?->bobot_kuis.'%'],
                ['label' => 'Bobot UTS', 'value' => $weight?->bobot_uts.'%'],
                ['label' => 'Bobot UAS', 'value' => $weight?->bobot_uas.'%'],
                ['label' => 'KKM', 'value' => $weight?->kkm],
            ] as $item)
                <div class="rounded-3xl bg-white/90 p-4 shadow-sm">
                    <div class="text-sm font-semibold text-slate-500">{{ $item['label'] }}</div>
                    <div class="mt-1 text-2xl font-black text-cyan-800">{{ $item['value'] }}</div>
                </div>
            @endforeach
        </div>

        <form method="POST" action="{{ route('teacher.grades.update') }}" class="overflow-hidden rounded-3xl bg-white/90 shadow-sm">
            @csrf
            @method('PUT')
            <input type="hidden" name="subject_id" value="{{ $selectedPair->subject_id }}">
            <input type="hidden" name="class_id" value="{{ $selectedPair->class_id }}">

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Siswa</th>
                            <th class="px-4 py-3">Tugas</th>
                            <th class="px-4 py-3">Kuis</th>
                            <th class="px-4 py-3">UTS</th>
                            <th class="px-4 py-3">UAS</th>
                            <th class="px-4 py-3">Nilai Akhir</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($students as $student)
                            @php($grade = $grades->get($student->id))
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-900">{{ $student->nama }}</div>
                                    <div class="text-xs text-slate-500">NIS {{ $student->nis }}</div>
                                </td>
                                @foreach(['nilai_tugas' => 'Tugas', 'nilai_kuis' => 'Kuis', 'nilai_uts' => 'UTS', 'nilai_uas' => 'UAS'] as $field => $label)
                                    <td class="px-4 py-3">
                                        <input type="number" step="0.01" min="0" max="100" name="grades[{{ $student->id }}][{{ $field }}]" value="{{ old('grades.'.$student->id.'.'.$field, $grade?->{$field}) }}" class="w-24 rounded-2xl border-slate-200" aria-label="{{ $label }} {{ $student->nama }}">
                                    </td>
                                @endforeach
                                <td class="px-4 py-3 text-lg font-black text-cyan-800">{{ $grade?->nilai_akhir ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $grade?->status_kelulusan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-8 text-center text-slate-500">Belum ada siswa di kelas ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end border-t border-slate-100 px-5 py-4">
                <button class="rounded-2xl bg-cyan-700 px-5 py-2.5 text-sm font-bold text-white">Simpan dan Hitung Nilai Akhir</button>
            </div>
        </form>
    @else
        <div class="rounded-3xl bg-white/90 p-8 text-center text-slate-500">Belum ada relasi guru dengan kelas dan mata pelajaran.</div>
    @endif
</x-app-layout>
