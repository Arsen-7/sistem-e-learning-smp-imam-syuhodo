<div class="grid gap-4 md:grid-cols-3">
    <div>
        <label class="mb-1 block text-sm font-bold text-slate-700">Guru</label>
        <select name="teacher_id" class="w-full rounded-2xl border-slate-200" required>
            <option value="">Pilih guru</option>
            @foreach($teachers as $teacher)
                <option value="{{ $teacher->id }}" @selected((int) old('teacher_id', $teacherSubject->teacher_id ?? 0) === $teacher->id)>{{ $teacher->nama }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="mb-1 block text-sm font-bold text-slate-700">Mata Pelajaran</label>
        <select name="subject_id" class="w-full rounded-2xl border-slate-200" required>
            <option value="">Pilih mapel</option>
            @foreach($subjects as $subject)
                <option value="{{ $subject->id }}" @selected((int) old('subject_id', $teacherSubject->subject_id ?? 0) === $subject->id)>{{ $subject->nama_mapel }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="mb-1 block text-sm font-bold text-slate-700">Kelas</label>
        <select name="class_id" class="w-full rounded-2xl border-slate-200" required>
            <option value="">Pilih kelas</option>
            @foreach($classes as $class)
                <option value="{{ $class->id }}" @selected((int) old('class_id', $teacherSubject->class_id ?? 0) === $class->id)>{{ $class->nama_kelas }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="mt-5 flex gap-2"><button class="rounded-2xl bg-cyan-700 px-5 py-2.5 text-sm font-bold text-white">Simpan</button><a href="{{ route('admin.teacher-subjects.index') }}" class="rounded-2xl bg-white px-5 py-2.5 text-sm font-bold text-slate-600">Batal</a></div>
