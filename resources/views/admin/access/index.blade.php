<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Hak Akses</h1>
            <p class="text-sm text-slate-500">Atur role dan status akun pengguna dalam satu menu.</p>
        </div>
    </x-slot>

    <form method="GET" class="mb-5 grid gap-3 rounded-2xl bg-white/90 p-4 shadow-sm md:grid-cols-[1fr_180px_auto]">
        <input name="q" value="{{ request('q') }}" class="rounded-xl border-slate-200 text-sm" placeholder="Cari nama atau email">
        <select name="role" class="rounded-xl border-slate-200 text-sm">
            <option value="">Semua Role</option>
            @foreach(['admin' => 'Admin', 'guru' => 'Guru', 'wali_kelas' => 'Wali Kelas', 'siswa' => 'Siswa'] as $key => $label)
                <option value="{{ $key }}" @selected(request('role') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="rounded-xl bg-cyan-700 px-5 py-2 text-sm font-bold text-white">Filter</button>
    </form>

    <div class="overflow-hidden rounded-3xl bg-white/90 shadow-sm">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                <tr>
                    <th class="px-5 py-3">Nama Pengguna</th>
                    <th class="px-5 py-3">Username/Email</th>
                    <th class="px-5 py-3">Role</th>
                    <th class="px-5 py-3">Status Akun</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $user)
                    <tr>
                        <td class="px-5 py-4 font-semibold text-slate-900">{{ $user->name }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ $user->email }}</td>
                        <td class="px-5 py-4">
                            <form method="POST" action="{{ route('admin.access.update', $user) }}" class="flex flex-wrap items-center gap-2 justify-end">
                                @csrf
                                @method('PUT')
                                <select name="role" class="rounded-xl border-slate-200 text-xs">
                                    @foreach(['admin' => 'Admin', 'guru' => 'Guru', 'wali_kelas' => 'Wali Kelas', 'siswa' => 'Siswa'] as $key => $label)
                                        <option value="{{ $key }}" @selected($user->role === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                        </td>
                        <td class="px-5 py-4">
                                <select name="status_akun" class="rounded-xl border-slate-200 text-xs">
                                    @foreach(['Aktif', 'Nonaktif'] as $status)
                                        <option value="{{ $status }}" @selected(($user->status_akun ?? 'Aktif') === $status)>{{ $status }}</option>
                                    @endforeach
                                </select>
                        </td>
                        <td class="px-5 py-4 text-right">
                                <button class="app-action-button app-action-button--edit">Simpan</button>
                                <a href="{{ route('admin.users.edit', $user) }}" class="app-action-button app-action-button--detail">Detail/Edit</a>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-slate-500">Belum ada pengguna.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-4">{{ $users->links() }}</div>
    </div>
</x-app-layout>
