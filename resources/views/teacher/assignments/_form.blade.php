@php
    $initialSubject = old('subject_id', $assignment->subject_id ?? ($pairs->first()->subject_id ?? ''));
    $initialClass = old('class_id', $assignment->class_id ?? ($pairs->first()->class_id ?? ''));
@endphp
<div x-data="{ pair: '{{ $initialSubject }}|{{ $initialClass }}', sync() { const parts = this.pair.split('|'); this.$refs.subject.value = parts[0] || ''; this.$refs.classId.value = parts[1] || ''; } }" x-init="sync()" class="space-y-4">
    <input type="hidden" name="subject_id" x-ref="subject" value="{{ $initialSubject }}">
    <input type="hidden" name="class_id" x-ref="classId" value="{{ $initialClass }}">
    <div class="grid gap-4 md:grid-cols-2">
        <div><label class="mb-1 block text-sm font-bold text-slate-700">Kelas dan Mata Pelajaran</label><select x-model="pair" @change="sync()" class="w-full rounded-2xl border-slate-200" required>@foreach($pairs as $pair)<option value="{{ $pair->subject_id }}|{{ $pair->class_id }}">{{ $pair->schoolClass->nama_kelas ?? '-' }} - {{ $pair->subject->nama_mapel ?? '-' }}</option>@endforeach</select></div>
        <div><label class="mb-1 block text-sm font-bold text-slate-700">Judul Tugas</label><input name="judul" value="{{ old('judul', $assignment->judul ?? '') }}" class="w-full rounded-2xl border-slate-200" required></div>
        <div><label class="mb-1 block text-sm font-bold text-slate-700">Deadline</label><input type="datetime-local" name="deadline" value="{{ old('deadline', isset($assignment) ? $assignment->deadline->format('Y-m-d\TH:i') : '') }}" class="w-full rounded-2xl border-slate-200" required></div>
        <div class="flex items-end"><label class="inline-flex items-center gap-2 rounded-2xl bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700"><input type="checkbox" name="status_aktif" value="1" class="rounded border-slate-300 text-cyan-700" @checked(old('status_aktif', $assignment->status_aktif ?? true))> Tugas aktif</label></div>
    </div>
    <div><label class="mb-1 block text-sm font-bold text-slate-700">Deskripsi</label><textarea name="deskripsi" rows="5" class="w-full rounded-2xl border-slate-200">{{ old('deskripsi', $assignment->deskripsi ?? '') }}</textarea></div>
    <div><label class="mb-1 block text-sm font-bold text-slate-700">File Pendukung</label><input type="file" name="file" class="w-full rounded-2xl border border-slate-200 bg-white p-2"><p class="mt-1 text-xs text-slate-500">Format: PDF, Word, PowerPoint, Excel, ZIP, JPG, PNG. Maksimal 5 MB.</p>@isset($assignment) @if($assignment->file)<a href="{{ asset('storage/'.$assignment->file) }}" target="_blank" class="mt-2 inline-block text-sm font-bold text-cyan-700">Lihat file saat ini</a>@endif @endisset</div>
    <div class="flex gap-2"><button class="rounded-2xl bg-cyan-700 px-5 py-2.5 text-sm font-bold text-white">Simpan</button><a href="{{ route('teacher.assignments.index') }}" class="rounded-2xl bg-white px-5 py-2.5 text-sm font-bold text-slate-600">Batal</a></div>
</div>
