<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">{{ in_array($role, ['guru', 'wali_kelas']) ? 'Data '.($role === 'wali_kelas' ? 'Wali Kelas' : 'Guru') : 'Kelola Pengguna' }}</h1>
                <p class="text-sm text-slate-500">{{ $role === 'guru' ? 'Daftar guru dan mata pelajaran yang diampu.' : 'Tambah, edit, hapus, dan lihat data admin, guru, serta siswa.' }}</p>
            </div>
            <a href="{{ route('admin.users.create', ['role' => $role ?? 'siswa']) }}" class="inline-flex items-center justify-center rounded-lg bg-cyan-700 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-cyan-800">Tambah Pengguna</a>
        </div>
    </x-slot>

    <div class="mb-5 flex flex-wrap gap-2">
        <a href="{{ route('admin.users.index') }}" class="rounded-full px-4 py-2 text-sm font-semibold shadow-sm {{ $role ? 'bg-white text-slate-600' : 'bg-cyan-700 text-white' }}">Semua</a>
        @foreach(['admin' => 'Admin', 'guru' => 'Guru', 'wali_kelas' => 'Wali Kelas', 'siswa' => 'Siswa'] as $key => $label)
            <a href="{{ route('admin.users.index', ['role' => $key]) }}" class="rounded-full px-4 py-2 text-sm font-semibold shadow-sm {{ $role === $key ? 'bg-cyan-700 text-white' : 'bg-white text-slate-600' }}">{{ $label }}</a>
        @endforeach
    </div>

    @if($role === 'siswa')
        @php
            $filterQuery = request()->except(['page', 'export']);
            $sortDirection = ($filters['sort'] ?? 'asc') === 'asc' ? 'desc' : 'asc';
        @endphp
        <form method="GET" action="{{ route('admin.users.index', ['role' => 'siswa']) }}" class="mb-5 grid gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4 xl:grid-cols-8">
            <select name="tahun_ajaran_id" class="rounded-lg border-slate-200 text-sm">
                <option value="">Semua tahun</option>
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}" @selected(($filters['tahun_ajaran_id'] ?? null) === $year->id)>{{ $year->nama }}</option>
                @endforeach
            </select>
            <select name="tingkat" class="rounded-lg border-slate-200 text-sm">
                <option value="">Semua tingkat</option>
                @foreach(['VII' => '7', 'VIII' => '8', 'IX' => '9'] as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['tingkat'] ?? '') === $value)>Tingkat {{ $label }}</option>
                @endforeach
            </select>
            <select name="class_id" class="rounded-lg border-slate-200 text-sm">
                <option value="">Semua kelas</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" @selected(($filters['class_id'] ?? null) === $class->id)>{{ $class->nama_kelas }}</option>
                @endforeach
            </select>
            <select name="status_siswa" class="rounded-lg border-slate-200 text-sm">
                <option value="">Semua status</option>
                @foreach(['Aktif', 'Pindah', 'Keluar', 'Lulus/Alumni'] as $status)
                    <option value="{{ $status }}" @selected(($filters['status_siswa'] ?? '') === $status)>{{ $status }}</option>
                @endforeach
            </select>
            <select name="jenis_kelamin" class="rounded-lg border-slate-200 text-sm">
                <option value="">Semua JK</option>
                <option value="L" @selected(($filters['jenis_kelamin'] ?? '') === 'L')>Laki-laki</option>
                <option value="P" @selected(($filters['jenis_kelamin'] ?? '') === 'P')>Perempuan</option>
            </select>
            <input name="search" value="{{ $filters['search'] ?? '' }}" class="rounded-lg border-slate-200 text-sm md:col-span-2" placeholder="Nama siswa, NIS, atau NISN">
            <input type="hidden" name="sort" value="{{ $filters['sort'] ?? 'asc' }}">
            <div class="flex flex-wrap gap-2">
                <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white">Terapkan</button>
                <a href="{{ route('admin.users.index', ['role' => 'siswa']) }}" class="rounded-lg bg-white px-4 py-2 text-sm font-bold text-slate-600 ring-1 ring-slate-200">Reset</a>
            </div>
            <div class="flex flex-wrap gap-2 md:col-span-4 xl:col-span-8">
                <a href="{{ route('admin.users.index', array_merge(['role' => 'siswa'], $filterQuery, ['export' => 'excel'])) }}" class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-bold text-white">Export Excel</a>
                <a href="{{ route('admin.users.index', array_merge(['role' => 'siswa'], $filterQuery, ['export' => 'pdf'])) }}" class="rounded-lg bg-rose-700 px-4 py-2 text-sm font-bold text-white">Export PDF</a>
            </div>
        </form>
    @endif

    <div class="space-y-3 md:hidden">
        @forelse($users as $user)
            @php
                $subjects = $user->teacher?->mataPelajaranDiampu() ?? '-';
            @endphp
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="truncate text-base font-extrabold text-slate-900">{{ $user->name }}</div>
                        <div class="mt-1 truncate text-sm text-slate-500">{{ $role === 'guru' ? ($user->teacher->gelar ?? '-') : $user->email }}</div>
                    </div>
                    <span class="shrink-0 rounded-full bg-cyan-50 px-3 py-1 text-xs font-bold text-cyan-700">{{ $user->roleLabel() }}</span>
                </div>

                <dl class="mt-4 grid gap-3 text-sm">
                    @if($role === 'guru')
                        <div class="rounded-lg bg-slate-50 px-3 py-2">
                            <dt class="font-semibold text-slate-500">Mata Pelajaran yang Diampu</dt>
                            <dd class="mt-1 font-bold text-slate-800">{{ $subjects }}</dd>
                        </div>
                    @else
                    <div class="flex items-center justify-between gap-3 rounded-lg bg-slate-50 px-3 py-2">
                        <dt class="font-semibold text-slate-500">Identitas</dt>
                        <dd class="font-bold text-slate-800">{{ $user->teacher->nip ?? $user->student->nis ?? '-' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 rounded-lg bg-slate-50 px-3 py-2">
                        <dt class="font-semibold text-slate-500">Kelas</dt>
                        <dd class="font-bold text-slate-800">{{ $user->student->schoolClass->nama_kelas ?? '-' }}</dd>
                    </div>
                    @endif
                </dl>

                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('admin.users.edit', $user) }}" class="app-action-button app-action-button--edit">Edit</a>
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-confirm-title="Konfirmasi Hapus" data-confirm="Hapus pengguna ini?" data-confirm-yes="Hapus" data-confirm-no="Tidak">
                        @csrf
                        @method('DELETE')
                        <button class="app-action-button app-action-button--delete">Hapus</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="rounded-lg border border-slate-200 bg-white p-6 text-center shadow-sm">
                <div class="mx-auto grid h-12 w-12 place-items-center rounded-lg bg-cyan-50 text-cyan-700">
                    <x-sidebar-icon name="users" class="h-6 w-6" />
                </div>
                <div class="mt-4 font-extrabold text-slate-900">Belum ada data pengguna</div>
                <p class="mt-1 text-sm text-slate-500">Data akan tampil di sini setelah pengguna ditambahkan.</p>
            </div>
        @endforelse

        <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-sm">{{ $users->links() }}</div>
    </div>

    <div class="hidden overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm md:block">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="sticky top-0 z-10 bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                    @if($role === 'guru')
                        <tr>
                            <th class="w-20 px-5 py-3">No</th>
                            <th class="px-5 py-3">Nama Guru</th>
                            <th class="px-5 py-3">Gelar</th>
                            <th class="px-5 py-3">Mata Pelajaran yang Diampu</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    @else
                        <tr>
                            <th class="px-5 py-3">
                                @if($role === 'siswa')
                                    <a href="{{ route('admin.users.index', array_merge(['role' => 'siswa'], $filterQuery, ['sort' => $sortDirection])) }}">Nama {{ ($filters['sort'] ?? 'asc') === 'asc' ? '↑' : '↓' }}</a>
                                @else
                                    Nama
                                @endif
                            </th>
                            <th class="px-5 py-3">Email</th>
                            <th class="px-5 py-3">Role</th>
                            <th class="px-5 py-3">Identitas</th>
                            <th class="px-5 py-3">Kelas</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    @endif
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        @php
                            $subjects = $user->teacher?->mataPelajaranDiampu() ?? '-';
                        @endphp
                        <tr class="odd:bg-white even:bg-slate-50/70">
                            @if($role === 'guru')
                                <td class="px-5 py-4 font-semibold text-slate-500">{{ $users->firstItem() + $loop->index }}</td>
                                <td class="px-5 py-4 font-semibold text-slate-800">{{ $user->teacher->nama ?? $user->name }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $user->teacher->gelar ?? '-' }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $subjects }}</td>
                            @else
                                <td class="px-5 py-4 text-left font-semibold text-slate-800">{{ $user->name }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $user->email }}</td>
                                <td class="px-5 py-4"><span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-bold text-cyan-700">{{ $user->roleLabel() }}</span></td>
                                <td class="px-5 py-4 text-slate-600">{{ $user->teacher->nip ?? $user->student->nis ?? '-' }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $user->student->schoolClass->nama_kelas ?? '-' }}</td>
                            @endif
                            <td class="px-5 py-4 text-right">
                                <div class="app-action-bar">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="app-action-button app-action-button--edit">Edit</a>
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-confirm-title="Konfirmasi Hapus" data-confirm="Hapus pengguna ini?" data-confirm-yes="Hapus" data-confirm-no="Tidak">
                                        @csrf
                                        @method('DELETE')
                                        <button class="app-action-button app-action-button--delete">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $role === 'guru' ? 5 : 6 }}" class="px-5 py-10 text-center text-slate-500">
                                <div class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-lg bg-cyan-50 text-cyan-700">
                                    <x-sidebar-icon name="users" class="h-6 w-6" />
                                </div>
                                <div class="font-extrabold text-slate-900">Belum ada data pengguna</div>
                                <div class="mt-1 text-sm text-slate-500">Data akan tampil di sini setelah pengguna ditambahkan.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="flex flex-col gap-3 border-t border-slate-100 px-5 py-4 text-sm text-slate-600 md:flex-row md:items-center md:justify-between">
            <div>Menampilkan {{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} data.</div>
            <div>{{ $users->links() }}</div>
        </div>
    </div>
</x-app-layout>
