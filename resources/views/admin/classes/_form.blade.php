<div class="grid gap-4 md:grid-cols-3">
    <div>
        <label class="mb-1 block text-sm font-bold text-slate-700">Nama Kelas</label>
        <input name="nama_kelas" value="{{ old('nama_kelas', $schoolClass->nama_kelas ?? '') }}" class="w-full rounded-2xl border-slate-200" required>
    </div>
    <div>
        <label class="mb-1 block text-sm font-bold text-slate-700">Tingkat</label>
        <input name="tingkat" value="{{ old('tingkat', $schoolClass->tingkat ?? '') }}" placeholder="VII / VIII / IX" class="w-full rounded-2xl border-slate-200" required>
    </div>
    <div>
        <label class="mb-1 block text-sm font-bold text-slate-700">Wali Kelas</label>
        <select name="wali_kelas_id" class="w-full rounded-2xl border-slate-200">
            <option value="">Belum ditentukan</option>
            @foreach($teachers as $teacher)
                <option value="{{ $teacher->id }}" @selected((int) old('wali_kelas_id', $schoolClass->wali_kelas_id ?? 0) === $teacher->id)>{{ $teacher->nama }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="mt-5 flex gap-2">
    <button class="rounded-2xl bg-cyan-700 px-5 py-2.5 text-sm font-bold text-white">Simpan</button>
    <a href="{{ route('admin.classes.index') }}" class="rounded-2xl bg-white px-5 py-2.5 text-sm font-bold text-slate-600">Batal</a>
</div>
