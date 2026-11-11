@php
    $isEdit = isset($user);
    $selectedRole = old('role', $user->role ?? ($role ?? 'siswa'));
    $teacher = $user->teacher ?? null;
    $student = $user->student ?? null;
@endphp

<div x-data="{ role: '{{ $selectedRole }}' }" class="space-y-5">
    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="mb-1 block text-sm font-bold text-slate-700">Nama Lengkap</label>
            <input name="name" value="{{ old('name', $user->name ?? '') }}" class="w-full rounded-2xl border-slate-200" required>
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold text-slate-700">Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" class="w-full rounded-2xl border-slate-200" required>
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold text-slate-700">Role</label>
            <select name="role" x-model="role" class="w-full rounded-2xl border-slate-200" required>
                <option value="admin">Admin</option>
                <option value="guru">Guru</option>
                <option value="wali_kelas">Wali Kelas</option>
                <option value="siswa">Siswa</option>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold text-slate-700">Status Akun</label>
            <select name="status_akun" class="w-full rounded-2xl border-slate-200">
                @foreach(['Aktif', 'Nonaktif'] as $status)
                    <option value="{{ $status }}" @selected(old('status_akun', $user->status_akun ?? 'Aktif') === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold text-slate-700">Jenis Kelamin</label>
            <select name="jenis_kelamin" class="w-full rounded-2xl border-slate-200">
                <option value="">Pilih jika guru/siswa</option>
                <option value="L" @selected(old('jenis_kelamin', $teacher->jenis_kelamin ?? $student->jenis_kelamin ?? '') === 'L')>Laki-laki</option>
                <option value="P" @selected(old('jenis_kelamin', $teacher->jenis_kelamin ?? $student->jenis_kelamin ?? '') === 'P')>Perempuan</option>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold text-slate-700">Password {{ $isEdit ? '(kosongkan jika tidak diganti)' : '' }}</label>
            <input type="password" name="password" class="w-full rounded-2xl border-slate-200" {{ $isEdit ? '' : 'required' }}>
        </div>
        <div>
            <label class="mb-1 block text-sm font-bold text-slate-700">Konfirmasi Password</label>
            <input type="password" name="password_confirmation" class="w-full rounded-2xl border-slate-200" {{ $isEdit ? '' : 'required' }}>
        </div>
    </div>

    <div x-show="role === 'guru' || role === 'wali_kelas'" class="rounded-3xl bg-cyan-50 p-5">
        <h3 class="mb-4 font-extrabold text-cyan-900">Profil Guru</h3>
        <div class="grid gap-4 md:grid-cols-4">
            <div>
                <label class="mb-1 block text-sm font-bold text-slate-700">NIP</label>
                <input name="nip" value="{{ old('nip', $teacher->nip ?? '') }}" class="w-full rounded-2xl border-slate-200">
            </div>
            <div>
                <label class="mb-1 block text-sm font-bold text-slate-700">Gelar</label>
                <input name="gelar" value="{{ old('gelar', $teacher->gelar ?? '') }}" class="w-full rounded-2xl border-slate-200" placeholder="S.Pd.">
            </div>
            <div>
                <label class="mb-1 block text-sm font-bold text-slate-700">No. HP</label>
                <input name="no_hp" value="{{ old('no_hp', $teacher->no_hp ?? $student->no_hp ?? '') }}" class="w-full rounded-2xl border-slate-200">
            </div>
            <div>
                <label class="mb-1 block text-sm font-bold text-slate-700">Alamat</label>
                <input name="alamat" value="{{ old('alamat', $teacher->alamat ?? $student->alamat ?? '') }}" class="w-full rounded-2xl border-slate-200">
            </div>
        </div>
    </div>

    <div x-show="role === 'siswa'" class="rounded-3xl bg-amber-50 p-5">
        <h3 class="mb-4 font-extrabold text-amber-900">Profil Siswa</h3>
        <div class="grid gap-4 md:grid-cols-4">
            <div>
                <label class="mb-1 block text-sm font-bold text-slate-700">NIS</label>
                <input name="nis" value="{{ old('nis', $student->nis ?? '') }}" class="w-full rounded-2xl border-slate-200">
            </div>
            <div>
                <label class="mb-1 block text-sm font-bold text-slate-700">Kelas</label>
                <select name="class_id" class="w-full rounded-2xl border-slate-200">
                    <option value="">Pilih kelas</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" @selected((int) old('class_id', $student->class_id ?? 0) === $class->id)>{{ $class->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-bold text-slate-700">No. HP</label>
                <input name="no_hp" value="{{ old('no_hp', $student->no_hp ?? $teacher->no_hp ?? '') }}" class="w-full rounded-2xl border-slate-200">
            </div>
            <div>
                <label class="mb-1 block text-sm font-bold text-slate-700">Status Siswa</label>
                <select name="status_siswa" class="w-full rounded-2xl border-slate-200">
                    @foreach(['Aktif', 'Pindah', 'Keluar', 'Lulus/Alumni'] as $status)
                        <option value="{{ $status }}" @selected(old('status_siswa', $student->status_siswa ?? 'Aktif') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-bold text-slate-700">Alamat</label>
                <input name="alamat" value="{{ old('alamat', $student->alamat ?? $teacher->alamat ?? '') }}" class="w-full rounded-2xl border-slate-200">
            </div>
        </div>
    </div>

    <div class="flex gap-2">
        <button class="rounded-2xl bg-cyan-700 px-5 py-2.5 text-sm font-bold text-white hover:bg-cyan-800">Simpan</button>
        <a href="{{ route('admin.users.index') }}" class="rounded-2xl bg-white px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50">Batal</a>
    </div>
</div>
