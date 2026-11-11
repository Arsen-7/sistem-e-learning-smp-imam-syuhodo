<div class="space-y-4">
    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="mb-1 block text-sm font-bold text-slate-700">Target Pengumuman</label>
            <select name="audience" class="w-full rounded-2xl border-slate-200" required>
                @foreach(['semua' => 'Semua Pengguna', 'guru' => 'Guru', 'siswa' => 'Siswa', 'kelas' => 'Kelas Tertentu'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('audience', $announcement->audience ?? 'semua') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold text-slate-700">Kelas</label>
            <select name="class_id" class="w-full rounded-2xl border-slate-200">
                <option value="">Pilih jika target kelas tertentu</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" @selected((int) old('class_id', $announcement->class_id ?? 0) === $class->id)>{{ $class->nama_kelas }}</option>
                @endforeach
            </select>
        </div>
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
        <button class="rounded-2xl bg-cyan-700 px-5 py-2.5 text-sm font-bold text-white">Simpan</button>
        <a href="{{ route('admin.announcements.index') }}" class="rounded-2xl bg-white px-5 py-2.5 text-sm font-bold text-slate-600">Batal</a>
    </div>
</div>
