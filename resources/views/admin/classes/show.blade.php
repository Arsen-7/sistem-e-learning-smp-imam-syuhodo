<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">Detail Kelas {{ $schoolClass->nama_kelas }}</h1>
                <p class="text-sm text-slate-500">Daftar siswa, guru pengampu, materi, dan tugas kelas.</p>
            </div>
            <a href="{{ route('admin.classes.index') }}" class="rounded-2xl bg-white px-4 py-2 text-sm font-bold text-slate-600 shadow-sm">Kembali</a>
        </div>
    </x-slot>

    <div class="grid gap-4 md:grid-cols-4">
        @foreach([
            ['label' => 'Tingkat', 'value' => $schoolClass->tingkat],
            ['label' => 'Wali Kelas', 'value' => $schoolClass->waliKelas->nama ?? '-'],
            ['label' => 'Jumlah Siswa', 'value' => $students->count()],
            ['label' => 'Mapel Diampu', 'value' => $schoolClass->teacherSubjects->count()],
        ] as $item)
            <div class="rounded-3xl bg-white/90 p-5 shadow-sm">
                <div class="text-sm font-semibold text-slate-500">{{ $item['label'] }}</div>
                <div class="mt-2 text-2xl font-black text-cyan-800">{{ $item['value'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="rounded-3xl bg-white/90 p-6 shadow-sm lg:col-span-2">
            <h2 class="mb-4 text-lg font-extrabold text-slate-900">Daftar Siswa</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-3">No</th>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">NIS</th>
                            <th class="px-4 py-3">JK</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Rata Nilai</th>
                            <th class="px-4 py-3">Tugas Terkumpul</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($students as $student)
                            <tr>
                                <td class="px-4 py-3 font-bold text-slate-500">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $student->nama }}</td>
                                <td class="px-4 py-3">{{ $student->nis }}</td>
                                <td class="px-4 py-3">{{ $student->jenis_kelamin }}</td>
                                <td class="px-4 py-3">{{ $student->user->email ?? '-' }}</td>
                                <td class="px-4 py-3 font-bold text-cyan-800">{{ $student->rata_nilai ?: '-' }}</td>
                                <td class="px-4 py-3">{{ $student->jumlah_tugas_terkumpul }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">Belum ada siswa.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-3xl bg-white/90 p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-extrabold text-slate-900">Guru Pengampu</h2>
                <div class="space-y-2">
                    @forelse($schoolClass->teacherSubjects as $item)
                        <div class="rounded-2xl bg-slate-50 p-3">
                            <div class="font-bold text-slate-800">{{ $item->subject->nama_mapel ?? '-' }}</div>
                            <div class="text-xs text-slate-500">{{ $item->teacher->nama ?? '-' }}</div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada guru pengampu.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl bg-white/90 p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-extrabold text-slate-900">Materi dan Tugas</h2>
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-2xl bg-cyan-50 p-4">
                        <div class="text-sm font-semibold text-cyan-900">Materi</div>
                        <div class="mt-1 text-2xl font-black text-cyan-800">{{ $schoolClass->materials->count() }}</div>
                    </div>
                    <div class="rounded-2xl bg-amber-50 p-4">
                        <div class="text-sm font-semibold text-amber-900">Tugas</div>
                        <div class="mt-1 text-2xl font-black text-amber-700">{{ $schoolClass->assignments->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
