<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label class="mb-1 block text-sm font-bold text-slate-700">Kode Mata Pelajaran</label>
        <select name="kode_mapel" class="w-full rounded-2xl border-slate-200" required>
            <option value="">Pilih kode mapel</option>
            @foreach($officialSubjects as $code => $name)
                <option value="{{ $code }}" @selected(old('kode_mapel', $subject->kode_mapel ?? '') === $code)>{{ $code }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="mb-1 block text-sm font-bold text-slate-700">Nama Mata Pelajaran</label>
        <select name="nama_mapel" class="w-full rounded-2xl border-slate-200" required>
            <option value="">Pilih mata pelajaran</option>
            @foreach($officialSubjects as $name)
                <option value="{{ $name }}" @selected(old('nama_mapel', $subject->nama_mapel ?? '') === $name)>{{ $name }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="mt-5 flex gap-2">
    <button class="rounded-2xl bg-cyan-700 px-5 py-2.5 text-sm font-bold text-white">Simpan</button>
    <a href="{{ route('admin.subjects.index') }}" class="rounded-2xl bg-white px-5 py-2.5 text-sm font-bold text-slate-600">Batal</a>
</div>
