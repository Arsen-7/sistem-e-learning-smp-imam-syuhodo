<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Setting Akademik</h1>
            <p class="text-sm text-slate-500">Tahun ajaran, kelas aktif, status siswa, kenaikan kelas, kelulusan, dan arsip akademik.</p>
        </div>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-3xl bg-white/90 p-6 shadow-sm lg:col-span-2">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-extrabold text-slate-900">Tahun Ajaran</h2>
                <div class="text-sm font-bold text-cyan-800">Aktif: {{ $activeYear->nama ?? 'Belum diatur' }}</div>
            </div>

            <form method="POST" action="{{ route('admin.academic-settings.years.store') }}" class="mb-5 grid gap-3 md:grid-cols-[1fr_160px_160px_auto]">
                @csrf
                <input name="nama" class="rounded-xl border-slate-200 text-sm" placeholder="2026/2027" required>
                <input type="date" name="tanggal_mulai" class="rounded-xl border-slate-200 text-sm">
                <input type="date" name="tanggal_selesai" class="rounded-xl border-slate-200 text-sm">
                <button class="rounded-xl bg-cyan-700 px-4 py-2 text-sm font-bold text-white">Tambah</button>
            </form>

            <form method="GET" action="{{ route('admin.academic-settings.index') }}" class="mb-4 grid gap-3 md:grid-cols-[1fr_auto_auto]">
                <div>
                    <label for="tahun_ajaran_filter" class="sr-only">Cari tahun ajaran</label>
                    <input
                        id="tahun_ajaran_filter"
                        name="tahun_ajaran"
                        list="tahun_ajaran_options"
                        value="{{ $yearSearch }}"
                        class="w-full rounded-xl border-slate-200 text-sm"
                        placeholder="Cari / pilih tahun ajaran"
                        autocomplete="off"
                    >
                    <datalist id="tahun_ajaran_options">
                        @foreach($yearOptions as $yearOption)
                            <option value="{{ $yearOption->nama }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white">Cari</button>
                @if($yearSearch !== '')
                    <a href="{{ route('admin.academic-settings.index') }}" class="rounded-xl bg-white px-4 py-2 text-center text-sm font-bold text-slate-600 ring-1 ring-slate-200">Reset</a>
                @endif
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                        <tr><th class="px-4 py-3">Tahun Ajaran</th><th class="px-4 py-3">Periode</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Aksi</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($years as $year)
                            <tr>
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $year->nama }}</td>
                                <td class="px-4 py-3">{{ $year->tanggal_mulai?->format('d M Y') ?? '-' }} - {{ $year->tanggal_selesai?->format('d M Y') ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $year->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $year->is_active ? 'Aktif' : 'Arsip' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @unless($year->is_active)
                                        <form method="POST" action="{{ route('admin.academic-settings.years.activate', $year) }}">
                                            @csrf
                                            @method('PUT')
                                            <button class="app-action-button app-action-button--edit">Aktifkan</button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">{{ $yearSearch !== '' ? 'Tahun ajaran tidak ditemukan.' : 'Belum ada tahun ajaran.' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-3xl bg-white/90 p-6 shadow-sm">
            <h2 class="text-lg font-extrabold text-slate-900">Ringkasan Akademik</h2>
            <div class="mt-4 grid gap-3">
                <div class="rounded-2xl bg-cyan-50 p-4">
                    <div class="text-sm font-semibold text-cyan-800">Kelas</div>
                    <div class="text-3xl font-black text-cyan-900">{{ $classes->where('is_archived', false)->count() }}</div>
                </div>
                <div class="rounded-2xl bg-emerald-50 p-4">
                    <div class="text-sm font-semibold text-emerald-800">Siswa Aktif</div>
                    <div class="text-3xl font-black text-emerald-900">{{ $students->where('status_siswa', 'Aktif')->count() }}</div>
                </div>
                <div class="rounded-2xl bg-amber-50 p-4">
                    <div class="text-sm font-semibold text-amber-800">Pindah/Lulus</div>
                    <div class="text-3xl font-black text-amber-900">{{ $students->where('status_siswa', '!=', 'Aktif')->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6">
        <div class="rounded-3xl bg-white/90 p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-extrabold text-slate-900">Kelas Aktif</h2>
                <a href="{{ route('admin.classes.create') }}" class="app-action-button app-action-button--edit">Tambah Kelas</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                        <tr><th class="px-4 py-3">Kelas</th><th class="px-4 py-3">Tingkat</th><th class="px-4 py-3">Jumlah Siswa</th><th class="px-4 py-3">Wali Kelas</th><th class="px-4 py-3 text-right">Aksi</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($classes as $class)
                            <tr class="align-top">
                                <td class="px-4 py-3 font-extrabold text-slate-900">{{ $class->nama_kelas }}</td>
                                <td class="px-4 py-3">{{ $class->tingkat }}</td>
                                <td class="px-4 py-3">{{ $class->students_count }}</td>
                                <td class="px-4 py-3">{{ $class->waliKelas->nama ?? '-' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="app-action-bar">
                                        <a href="{{ route('admin.classes.show', $class) }}" class="app-action-button app-action-button--detail">Detail</a>
                                        <a href="{{ route('admin.classes.edit', $class) }}" class="app-action-button app-action-button--edit">Edit</a>
                                        <form method="POST" action="{{ route('admin.classes.destroy', $class) }}" data-confirm-title="Konfirmasi Hapus" data-confirm="Yakin ingin menghapus data ini?" data-confirm-yes="Hapus" data-confirm-no="Batal">
                                            @csrf
                                            @method('DELETE')
                                            <button class="app-action-button app-action-button--delete">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada kelas aktif.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-6 rounded-3xl bg-white/90 p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900">Guru Mengajar</h2>
                <p class="text-sm text-slate-500">Guru, mata pelajaran, dan kelas yang diampu pada tahun ajaran aktif.</p>
            </div>
            <a href="{{ route('admin.teacher-subjects.create') }}" class="app-action-button app-action-button--edit">Tambah Pengampu</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                    <tr><th class="px-4 py-3">Guru</th><th class="px-4 py-3">Mata Pelajaran</th><th class="px-4 py-3">Kelas</th><th class="px-4 py-3">Tahun Ajaran</th><th class="px-4 py-3 text-right">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($teacherSubjects as $item)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $item->teacher->nama ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $item->subject->nama_mapel ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $item->schoolClass->nama_kelas ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $item->academicYear->nama ?? $activeYear->nama ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="app-action-bar">
                                    <a href="{{ route('admin.teacher-subjects.edit', $item) }}" class="app-action-button app-action-button--edit">Edit</a>
                                    <form method="POST" action="{{ route('admin.teacher-subjects.destroy', $item) }}" data-confirm-title="Konfirmasi Hapus" data-confirm="Yakin ingin menghapus data ini?" data-confirm-yes="Hapus" data-confirm-no="Batal">
                                        @csrf
                                        @method('DELETE')
                                        <button class="app-action-button app-action-button--delete">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada data guru mengajar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 rounded-3xl bg-white/90 p-6 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900">Cek Kenaikan Kelas</h2>
                <p class="text-sm text-slate-500">Menampilkan seluruh siswa aktif pada tahun ajaran yang dipilih, termasuk yang belum memenuhi syarat.</p>
            </div>
            <div class="text-sm font-bold text-cyan-800">
                Total siswa aktif: {{ $promotionTotal }}
            </div>
        </div>

        <div class="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg bg-cyan-50 p-4">
                <div class="text-xs font-bold uppercase text-cyan-700">Total Siswa</div>
                <div class="mt-1 text-2xl font-black text-cyan-900">{{ $promotionSummary['total'] }}</div>
            </div>
            <div class="rounded-lg bg-emerald-50 p-4">
                <div class="text-xs font-bold uppercase text-emerald-700">Naik/Lulus</div>
                <div class="mt-1 text-2xl font-black text-emerald-900">{{ $promotionSummary['naik'] }}</div>
            </div>
            <div class="rounded-lg bg-rose-50 p-4">
                <div class="text-xs font-bold uppercase text-rose-700">Tidak Naik</div>
                <div class="mt-1 text-2xl font-black text-rose-900">{{ $promotionSummary['tidak_naik'] }}</div>
            </div>
            <div class="rounded-lg bg-slate-50 p-4">
                <div class="text-xs font-bold uppercase text-slate-600">Belum Diproses</div>
                <div class="mt-1 text-2xl font-black text-slate-900">{{ $promotionSummary['belum_diproses'] }}</div>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.academic-settings.index') }}" class="mb-4 grid gap-3 lg:grid-cols-4 xl:grid-cols-8">
            <select name="promotion_year_id" class="rounded-xl border-slate-200 text-sm">
                @foreach($yearOptions as $yearOption)
                    <option value="{{ $yearOption->id }}" @selected((int) ($promotionFilters['year_id'] ?? 0) === $yearOption->id)>{{ $yearOption->nama }}</option>
                @endforeach
            </select>
            <select name="promotion_semester_id" class="rounded-xl border-slate-200 text-sm">
                <option value="">Semua semester</option>
                @foreach($semesterOptions as $semesterOption)
                    <option value="{{ $semesterOption->id }}" @selected((int) ($promotionFilters['semester_id'] ?? 0) === $semesterOption->id)>{{ $semesterOption->nama }}</option>
                @endforeach
            </select>
            <select name="promotion_class_id" class="rounded-xl border-slate-200 text-sm">
                <option value="">Semua kelas</option>
                @foreach($promotionClasses as $class)
                    <option value="{{ $class->id }}" @selected((int) ($promotionFilters['class_id'] ?? 0) === $class->id)>{{ $class->nama_kelas }}</option>
                @endforeach
            </select>
            <select name="promotion_level" class="rounded-xl border-slate-200 text-sm">
                <option value="">Semua tingkat</option>
                @foreach(['VII', 'VIII', 'IX'] as $level)
                    <option value="{{ $level }}" @selected(($promotionFilters['level'] ?? '') === $level)>Tingkat {{ $level }}</option>
                @endforeach
            </select>
            <select name="promotion_status" class="rounded-xl border-slate-200 text-sm">
                <option value="">Semua status</option>
                <option value="naik" @selected(($promotionFilters['status'] ?? '') === 'naik')>Naik Kelas/Lulus</option>
                <option value="tidak_naik" @selected(($promotionFilters['status'] ?? '') === 'tidak_naik')>Tidak Naik</option>
                <option value="belum_diproses" @selected(($promotionFilters['status'] ?? '') === 'belum_diproses')>Belum Diproses</option>
            </select>
            <input name="promotion_search" value="{{ $promotionFilters['search'] ?? '' }}" class="rounded-xl border-slate-200 text-sm xl:col-span-2" placeholder="Cari nama siswa atau NIS/NISN">
            <input type="hidden" name="promotion_sort" value="{{ $promotionFilters['sort'] ?? 'asc' }}">
            <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white">Terapkan Filter</button>
            <a href="{{ route('admin.academic-settings.index') }}" class="rounded-xl bg-white px-4 py-2 text-center text-sm font-bold text-slate-600 ring-1 ring-slate-200">Reset</a>
        </form>

        <form method="POST" action="{{ route('admin.academic-settings.promotions.process') }}" data-confirm-title="Proses Kenaikan Kelas Massal" data-confirm="Proses kenaikan kelas untuk seluruh siswa yang dipilih?" data-confirm-yes="Proses" data-confirm-no="Batal">
            @csrf
            <input type="hidden" name="promotion_year_id" value="{{ $promotionFilters['year_id'] }}">
            <input type="hidden" name="promotion_semester_id" value="{{ $promotionFilters['semester_id'] }}">
            <input type="hidden" name="promotion_class_id" value="{{ $promotionFilters['class_id'] }}">
            <input type="hidden" name="promotion_level" value="{{ $promotionFilters['level'] }}">
            <input type="hidden" name="promotion_search" value="{{ $promotionFilters['search'] }}">
            <input type="hidden" name="promotion_status" value="{{ $promotionFilters['status'] }}">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                <label class="inline-flex items-center gap-2 text-sm font-bold text-slate-700">
                    <input id="promotion-select-all" type="checkbox" name="select_all_filtered" value="1" class="rounded border-slate-300 text-cyan-700">
                    Pilih Semua Sesuai Filter
                </label>
                <button type="button" id="promotion-clear-selection" class="rounded-xl bg-white px-4 py-2 text-sm font-bold text-slate-600 ring-1 ring-slate-200">Batalkan Pilihan</button>
                <button class="rounded-xl bg-cyan-700 px-4 py-2 text-sm font-bold text-white" @disabled(! $activeYear)>Proses Kenaikan Kelas Massal</button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="sticky top-0 z-10 bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                        <tr>
                            @php
                                $promotionSortQuery = request()->except('promotion_page');
                                $promotionSortQuery['promotion_sort'] = ($promotionFilters['sort'] ?? 'asc') === 'asc' ? 'desc' : 'asc';
                            @endphp
                            <th class="px-4 py-3 text-center">Pilih</th>
                            <th class="px-4 py-3">NIS/NISN</th>
                            <th class="px-4 py-3"><a href="{{ route('admin.academic-settings.index', $promotionSortQuery) }}">Nama Siswa {{ ($promotionFilters['sort'] ?? 'asc') === 'asc' ? '↑' : '↓' }}</a></th>
                            <th class="px-4 py-3">Kelas</th>
                            <th class="px-4 py-3">Wali Kelas</th>
                            <th class="px-4 py-3">Tujuan</th>
                            <th class="px-4 py-3">Status Kenaikan</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($promotionPreview as $row)
                            <tr class="odd:bg-white even:bg-slate-50/70">
                                <td class="px-4 py-3 text-center">
                                    <input
                                        name="student_ids[]"
                                        value="{{ $row['student']->id }}"
                                        type="checkbox"
                                        class="promotion-student-checkbox rounded border-slate-300 text-cyan-700"
                                        @disabled(! $row['can_process'])
                                    >
                                </td>
                                <td class="px-4 py-3">{{ $row['student']->nis }}</td>
                                <td class="px-4 py-3 text-left font-semibold text-slate-900">{{ $row['student']->nama }}</td>
                                <td class="px-4 py-3">{{ $row['from']->nama_kelas ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $row['wali_kelas']->nama ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $row['to_label'] }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $row['status'] === 'Tidak Naik' ? 'bg-rose-50 text-rose-700' : ($row['status'] === 'Belum Diproses' ? 'bg-slate-100 text-slate-700' : 'bg-emerald-50 text-emerald-700') }}">
                                        {{ $row['status'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    {{ $row['can_process'] ? 'Siap diproses' : 'Periksa kelas tujuan' }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-8 text-center text-slate-500">Tidak ada siswa aktif sesuai filter.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex flex-col gap-3 text-sm text-slate-600 md:flex-row md:items-center md:justify-between">
                <div>Menampilkan {{ $promotionPreview->firstItem() ?? 0 }}-{{ $promotionPreview->lastItem() ?? 0 }} dari {{ $promotionPreview->total() }} siswa.</div>
                <div>{{ $promotionPreview->links() }}</div>
            </div>
        </form>
    </div>

    <div class="mt-6 rounded-3xl bg-white/90 p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-extrabold text-slate-900">Kelulusan</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                    <tr><th class="px-4 py-3">Siswa Kelas Akhir</th><th class="px-4 py-3">NIS/NISN</th><th class="px-4 py-3">Kelas</th><th class="px-4 py-3 text-right">Proses</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($graduationCandidates as $student)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $student->nama }}</td>
                            <td class="px-4 py-3">{{ $student->nis }}</td>
                            <td class="px-4 py-3">{{ $student->schoolClass->nama_kelas ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('admin.academic-settings.graduation.process', $student) }}" data-confirm-title="Proses Kelulusan" data-confirm="Jadikan siswa ini sebagai alumni?" data-confirm-yes="Proses" data-confirm-no="Batal">
                                    @csrf
                                    <button class="app-action-button app-action-button--edit">Luluskan</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Belum ada siswa kelas akhir aktif.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 rounded-3xl bg-white/90 p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-extrabold text-slate-900">Status Siswa</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                    <tr><th class="px-4 py-3">Siswa</th><th class="px-4 py-3">Kelas</th><th class="px-4 py-3">Tahun Ajaran</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Ubah</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($students as $student)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $student->nama }}</td>
                            <td class="px-4 py-3">{{ $student->schoolClass->nama_kelas ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $student->academicYear->nama ?? $activeYear->nama ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $student->status_siswa ?? 'Aktif' }}</td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('admin.academic-settings.students.status', $student) }}" class="inline-flex gap-2">
                                    @csrf
                                    @method('PUT')
                                    <select name="status_siswa" class="rounded-xl border-slate-200 text-xs">
                                        @foreach(['Aktif', 'Pindah', 'Keluar', 'Lulus/Alumni'] as $status)
                                            <option value="{{ $status }}" @selected(($student->status_siswa ?? 'Aktif') === $status)>{{ $status }}</option>
                                        @endforeach
                                    </select>
                                    <button class="app-action-button app-action-button--edit">Simpan</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const selectAll = document.getElementById('promotion-select-all');
                const clearSelection = document.getElementById('promotion-clear-selection');
                const checkboxes = Array.from(document.querySelectorAll('.promotion-student-checkbox:not(:disabled)'));

                if (! selectAll) {
                    return;
                }

                selectAll.addEventListener('change', () => {
                    checkboxes.forEach((checkbox) => {
                        checkbox.checked = selectAll.checked;
                    });
                });

                checkboxes.forEach((checkbox) => {
                    checkbox.addEventListener('change', () => {
                        selectAll.checked = checkboxes.length > 0 && checkboxes.every((item) => item.checked);
                    });
                });

                clearSelection?.addEventListener('click', () => {
                    selectAll.checked = false;
                    checkboxes.forEach((checkbox) => {
                        checkbox.checked = false;
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
