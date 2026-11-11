<div class="space-y-4">
    <div x-data="{ pair: '{{ old('subject_id', $announcement->subject_id ?? '') }}|{{ old('class_id', $announcement->class_id ?? '') }}', sync() { const parts = this.pair.split('|'); this.$refs.subject.value = parts[0] || ''; this.$refs.classId.value = parts[1] || ''; } }" x-init="if (! pair || pair === '|') pair = $refs.pair.options[0]?.value || ''; sync()">
        <input type="hidden" name="subject_id" x-ref="subject">
        <input type="hidden" name="class_id" x-ref="classId">
        <label class="mb-1 block text-sm font-bold text-slate-700">Mapel dan Kelas Tujuan</label>
        <select x-ref="pair" x-model="pair" @change="sync()" class="w-full rounded-2xl border-slate-200" required @disabled($pairs->isEmpty())>
            @forelse($pairs as $pair)
                <option value="{{ $pair->subject_id }}|{{ $pair->class_id }}" @selected((int) old('subject_id', $announcement->subject_id ?? 0) === $pair->subject_id && (int) old('class_id', $announcement->class_id ?? 0) === $pair->class_id)>
                    {{ $pair->subject->nama_mapel ?? '-' }} - {{ $pair->schoolClass->nama_kelas ?? '-' }}
                </option>
            @empty
                <option value="">Belum ada kelas yang diampu</option>
            @endforelse
        </select>
    </div>

    <div>
        <label class="mb-1 block text-sm font-bold text-slate-700">Judul</label>
        <input name="judul" value="{{ old('judul', $announcement->judul ?? '') }}" class="w-full rounded-2xl border-slate-200" required>
    </div>

    <div>
        <label class="mb-1 block text-sm font-bold text-slate-700">Isi Pengumuman</label>
        <textarea name="isi" rows="6" class="w-full rounded-2xl border-slate-200" required>{{ old('isi', $announcement->isi ?? '') }}</textarea>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div>
            <label class="mb-1 block text-sm font-bold text-slate-700">Mulai Tayang</label>
            <input type="datetime-local" name="publish_at" value="{{ old('publish_at', isset($announcement) && $announcement->publish_at ? $announcement->publish_at->format('Y-m-d\TH:i') : '') }}" class="w-full rounded-2xl border-slate-200">
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold text-slate-700">Berakhir</label>
            <input type="datetime-local" name="expires_at" value="{{ old('expires_at', isset($announcement) && $announcement->expires_at ? $announcement->expires_at->format('Y-m-d\TH:i') : '') }}" class="w-full rounded-2xl border-slate-200">
        </div>
        <div class="flex items-end">
            <label class="inline-flex items-center gap-2 rounded-2xl bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700">
                <input type="checkbox" name="status_aktif" value="1" class="rounded border-slate-300 text-cyan-700" @checked(old('status_aktif', $announcement->status_aktif ?? true))>
                Aktif
            </label>
        </div>
    </div>

    <div class="flex gap-2">
        <button class="rounded-2xl bg-cyan-700 px-5 py-2.5 text-sm font-bold text-white disabled:cursor-not-allowed disabled:bg-slate-300" @disabled($pairs->isEmpty())>Simpan</button>
        <a href="{{ route('teacher.announcements.index') }}" class="rounded-2xl bg-white px-5 py-2.5 text-sm font-bold text-slate-600">Batal</a>
    </div>
</div>
