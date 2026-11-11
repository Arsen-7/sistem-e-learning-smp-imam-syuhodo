<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="mb-1 block text-sm font-bold text-slate-700">Mata Pelajaran</label>
        <select name="subject_id" class="w-full rounded-2xl border-slate-200" required>
            <option value="">Pilih mapel</option>
            @foreach($subjects as $subject)
                <option value="{{ $subject->id }}" @selected((int) old('subject_id', $gradeWeight->subject_id ?? 0) === $subject->id)>{{ $subject->nama_mapel }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="mb-1 block text-sm font-bold text-slate-700">Kelas</label>
        <select name="class_id" class="w-full rounded-2xl border-slate-200" required>
            <option value="">Pilih kelas</option>
            @foreach($classes as $class)
                <option value="{{ $class->id }}" @selected((int) old('class_id', $gradeWeight->class_id ?? 0) === $class->id)>{{ $class->nama_kelas }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="mt-4 grid gap-4 md:grid-cols-5">
    @foreach(['bobot_tugas' => 'Bobot Tugas (%)', 'bobot_kuis' => 'Bobot Kuis (%)', 'bobot_uts' => 'Bobot UTS (%)', 'bobot_uas' => 'Bobot UAS (%)', 'kkm' => 'KKM'] as $field => $label)
        <div>
            <label class="mb-1 block text-sm font-bold text-slate-700">{{ $label }}</label>
            <input type="number" step="0.01" min="0" max="100" name="{{ $field }}" value="{{ old($field, $gradeWeight->{$field} ?? ($field === 'bobot_tugas' ? 25 : ($field === 'bobot_kuis' ? 20 : ($field === 'bobot_uts' ? 25 : ($field === 'bobot_uas' ? 30 : 75))))) }}" class="w-full rounded-2xl border-slate-200" required>
        </div>
    @endforeach
</div>
<p class="mt-3 text-sm font-semibold text-slate-500">Catatan: total bobot tugas + kuis + UTS + UAS wajib tepat 100%.</p>
<div class="mt-5 flex gap-2"><button class="rounded-2xl bg-cyan-700 px-5 py-2.5 text-sm font-bold text-white">Simpan</button><a href="{{ route('admin.grade-weights.index') }}" class="rounded-2xl bg-white px-5 py-2.5 text-sm font-bold text-slate-600">Batal</a></div>
