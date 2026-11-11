<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Alumni;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentArchive;
use App\Models\StudentClassHistory;
use App\Models\TeacherSubject;
use App\Models\TeachingHistory;
use App\Services\AcademicYearService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AcademicSettingController extends Controller
{
    public function index(Request $request, AcademicYearService $academicYears): View
    {
        $yearSearch = trim((string) $request->query('tahun_ajaran', ''));
        $promotionFilters = [
            'year_id' => $request->integer('promotion_year_id') ?: null,
            'semester_id' => $request->integer('promotion_semester_id') ?: null,
            'class_id' => $request->integer('promotion_class_id') ?: null,
            'level' => trim((string) $request->query('promotion_level', '')),
            'search' => trim((string) $request->query('promotion_search', '')),
            'status' => trim((string) $request->query('promotion_status', '')),
            'sort' => in_array($request->query('promotion_sort'), ['asc', 'desc'], true) ? $request->query('promotion_sort') : 'asc',
        ];
        $yearOptions = AcademicYear::latest('id')->get(['id', 'nama']);
        $activeYear = $academicYears->active();
        $selectedPromotionYear = $promotionFilters['year_id']
            ? AcademicYear::find($promotionFilters['year_id'])
            : $activeYear;
        $promotionFilters['year_id'] = $selectedPromotionYear?->id;
        $semesterOptions = Semester::when($selectedPromotionYear, fn ($query) => $query->where('tahun_ajaran_id', $selectedPromotionYear->id))
            ->orderBy('nama')
            ->get(['id', 'nama', 'tahun_ajaran_id']);
        $years = AcademicYear::with('semesters')
            ->when($yearSearch !== '', fn ($query) => $query->where('nama', 'like', "%{$yearSearch}%"))
            ->latest('id')
            ->get();
        $classes = SchoolClass::withCount(['students', 'grades', 'assignments', 'materials'])
            ->with(['academicYear', 'waliKelas'])
            ->where('is_archived', false)
            ->when($activeYear, fn ($query) => $query->where(fn ($item) => $item->where('tahun_ajaran_id', $activeYear->id)->orWhereNull('tahun_ajaran_id')))
            ->ordered()
            ->get();
        $promotionClasses = SchoolClass::with('waliKelas')
            ->where('is_archived', false)
            ->when($selectedPromotionYear, fn ($query) => $query->where(fn ($item) => $item->where('tahun_ajaran_id', $selectedPromotionYear->id)->orWhereNull('tahun_ajaran_id')))
            ->ordered()
            ->get();
        $students = Student::with(['schoolClass', 'academicYear'])
            ->orderBy('nama')
            ->get();
        $teacherSubjects = TeacherSubject::with(['teacher', 'subject', 'schoolClass', 'academicYear'])
            ->whereHas('schoolClass', fn ($query) => $query->where('is_archived', false))
            ->when($activeYear, fn ($query) => $query->where(fn ($item) => $item->where('tahun_ajaran_id', $activeYear->id)->orWhereNull('tahun_ajaran_id')))
            ->latest()
            ->get();
        $graduationCandidates = $students
            ->where('status_siswa', 'Aktif')
            ->filter(fn (Student $student) => $student->schoolClass?->tingkat === 'IX')
            ->values();

        $promotionStudentsQuery = Student::with(['schoolClass.waliKelas', 'academicYear'])
            ->where('status_siswa', 'Aktif')
            ->when($selectedPromotionYear && ! $selectedPromotionYear->is_active, fn ($query) => $query->where(fn ($item) => $item
                ->where('students.tahun_ajaran_id', $selectedPromotionYear->id)
                ->orWhereNull('students.tahun_ajaran_id')))
            ->when($promotionFilters['class_id'], fn ($query, $classId) => $query->where('class_id', $classId))
            ->when($promotionFilters['level'] !== '', fn ($query) => $query->whereHas('schoolClass', fn ($classQuery) => $classQuery->where('tingkat', $promotionFilters['level'])))
            ->when($promotionFilters['search'] !== '', fn ($query) => $query->academicSearch($promotionFilters['search']))
            ->orderedForAcademic($promotionFilters['sort']);

        if ($promotionFilters['status'] === 'naik') {
            $promotionStudentsQuery->whereHas('schoolClass', fn ($query) => $query->whereIn('tingkat', ['VII', 'VIII', 'IX']));
        } elseif ($promotionFilters['status'] === 'tidak_naik') {
            $promotionStudentsQuery->where(function ($query) use ($activeYear): void {
                $query->whereDoesntHave('schoolClass')
                    ->orWhereHas('schoolClass', function ($classQuery) use ($activeYear): void {
                        $classQuery->whereNotIn('tingkat', ['IX'])
                            ->whereNotExists(function ($nextQuery) use ($activeYear): void {
                                $nextQuery->selectRaw('1')
                                    ->from('classes as next_classes')
                                    ->where(function ($levelQuery): void {
                                        $levelQuery
                                            ->where(fn ($item) => $item->whereColumn('classes.tingkat', 'VII')->where('next_classes.tingkat', 'VIII'))
                                            ->orWhere(fn ($item) => $item->whereColumn('classes.tingkat', 'VIII')->where('next_classes.tingkat', 'IX'));
                                    })
                                    ->when($activeYear, fn ($item) => $item->where(fn ($yearQuery) => $yearQuery->where('next_classes.tahun_ajaran_id', $activeYear->id)->orWhereNull('next_classes.tahun_ajaran_id')));
                            });
                    });
            });
        }

        $promotionTotal = (clone $promotionStudentsQuery)->count();
        $promotionRowsForSummary = (clone $promotionStudentsQuery)->get()->map(fn (Student $student) => $this->promotionPreviewRow($student, $activeYear));
        $promotionSummary = [
            'total' => $promotionTotal,
            'naik' => $promotionRowsForSummary->whereIn('status', ['Naik Kelas', 'Lulus'])->count(),
            'tidak_naik' => $promotionRowsForSummary->where('status', 'Tidak Naik')->count(),
            'belum_diproses' => $promotionRowsForSummary->where('status', 'Belum Diproses')->count(),
        ];
        $promotionPreview = $promotionStudentsQuery
            ->paginate(25, ['*'], 'promotion_page')
            ->withQueryString()
            ->through(fn (Student $student) => $this->promotionPreviewRow($student, $activeYear));

        return view('admin.academic-settings.index', compact(
            'years',
            'yearOptions',
            'semesterOptions',
            'yearSearch',
            'activeYear',
            'selectedPromotionYear',
            'classes',
            'promotionClasses',
            'students',
            'teacherSubjects',
            'graduationCandidates',
            'promotionPreview',
            'promotionFilters',
            'promotionTotal',
            'promotionSummary'
        ));
    }

    public function storeYear(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:50', 'unique:tahun_ajaran,nama'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ]);

        AcademicYear::create($validated + ['is_active' => false]);

        return back()->with('success', 'Tahun ajaran berhasil dibuat.');
    }

    public function storeSemester(Request $request, AcademicYearService $academicYears): RedirectResponse
    {
        $activeYear = $academicYears->active();
        abort_unless($activeYear, 422, 'Aktifkan tahun ajaran terlebih dahulu.');

        $validated = $request->validate([
            'nama' => ['required', 'in:Ganjil,Genap'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ]);

        Semester::updateOrCreate(
            ['tahun_ajaran_id' => $activeYear->id, 'nama' => $validated['nama']],
            $validated + ['is_active' => false]
        );

        return back()->with('success', 'Semester berhasil disimpan.');
    }

    public function activateSemester(Semester $semester): RedirectResponse
    {
        DB::transaction(function () use ($semester): void {
            Semester::where('tahun_ajaran_id', $semester->tahun_ajaran_id)
                ->whereKeyNot($semester->id)
                ->update(['is_active' => false]);
            $semester->update(['is_active' => true]);
        });

        return back()->with('success', 'Semester aktif berhasil diperbarui.');
    }

    public function graduateStudent(Student $student, AcademicYearService $academicYears): RedirectResponse
    {
        $activeYear = $academicYears->active();
        $this->archiveStudent($student, $student->tahun_ajaran_id);

        $student->update([
            'status_siswa' => 'Lulus/Alumni',
            'tahun_ajaran_id' => $activeYear?->id ?: $student->tahun_ajaran_id,
        ]);

        Alumni::updateOrCreate(
            ['student_id' => $student->id],
            ['tahun_ajaran_id' => $activeYear?->id ?: $student->tahun_ajaran_id, 'tanggal_lulus' => now()->toDateString()]
        );

        return back()->with('success', 'Siswa berhasil diproses menjadi alumni.');
    }

    public function activateYear(AcademicYear $academicYear): RedirectResponse
    {
        DB::transaction(function () use ($academicYear): void {
            AcademicYear::whereKeyNot($academicYear->id)->update(['is_active' => false]);
            $academicYear->update(['is_active' => true]);
        });

        return back()->with('success', 'Tahun ajaran aktif berhasil diperbarui.');
    }

    public function updateStudentStatus(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'status_siswa' => ['required', 'in:Aktif,Pindah,Keluar,Lulus/Alumni'],
        ]);

        $student->update($validated);
        $this->archiveStudent($student, $student->tahun_ajaran_id);

        if ($validated['status_siswa'] === 'Lulus/Alumni') {
            Alumni::updateOrCreate(
                ['student_id' => $student->id],
                ['tahun_ajaran_id' => $student->tahun_ajaran_id, 'tanggal_lulus' => now()->toDateString()]
            );
        }

        return back()->with('success', 'Status siswa berhasil diperbarui.');
    }

    public function processPromotion(Request $request, AcademicYearService $academicYears): RedirectResponse
    {
        $activeYear = $academicYears->active();

        abort_unless($activeYear, 422, 'Aktifkan tahun ajaran baru terlebih dahulu.');

        $validated = $request->validate([
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['integer', Rule::exists('students', 'id')->where('status_siswa', 'Aktif')],
            'select_all_filtered' => ['nullable', 'boolean'],
            'promotion_year_id' => ['nullable', 'integer', 'exists:tahun_ajaran,id'],
            'promotion_class_id' => ['nullable', 'integer', 'exists:classes,id'],
            'promotion_semester_id' => ['nullable', 'integer', 'exists:semesters,id'],
            'promotion_level' => ['nullable', 'in:VII,VIII,IX'],
            'promotion_search' => ['nullable', 'string', 'max:255'],
            'promotion_status' => ['nullable', 'in:naik,tidak_naik,belum_diproses'],
        ], [
            'student_ids.*.exists' => 'Data siswa yang dipilih tidak valid.',
        ]);

        $studentIds = $request->boolean('select_all_filtered')
            ? $this->promotionStudentIdsFromFilters($validated, $activeYear)
            : collect($validated['student_ids'] ?? [])->unique()->values();

        if ($studentIds->isEmpty()) {
            return back()->withErrors(['student_ids' => 'Pilih minimal satu siswa untuk diproses.'])->withInput();
        }

        $processed = 0;
        $skipped = 0;

        DB::transaction(function () use ($activeYear, $studentIds, &$processed, &$skipped): void {
            Student::with('schoolClass')
                ->whereIn('id', $studentIds)
                ->where('status_siswa', 'Aktif')
                ->orderBy('nama')
                ->get()
                ->each(function (Student $student) use ($activeYear, &$processed, &$skipped): void {
                if (! $student->schoolClass) {
                    $skipped++;

                    return;
                }

                if ($student->schoolClass?->tingkat === 'IX') {
                    $this->archiveStudent($student, $student->tahun_ajaran_id);
                    $student->update(['status_siswa' => 'Lulus/Alumni', 'tahun_ajaran_id' => $activeYear->id]);
                    Alumni::updateOrCreate(
                        ['student_id' => $student->id],
                        ['tahun_ajaran_id' => $activeYear->id, 'tanggal_lulus' => now()->toDateString()]
                    );
                    $processed++;

                    return;
                }

                $nextClass = $this->findNextClass($student->schoolClass, $activeYear);

                if ($nextClass) {
                    $this->archiveStudent($student, $student->tahun_ajaran_id);
                    $student->update(['class_id' => $nextClass->id, 'tahun_ajaran_id' => $activeYear->id]);
                    StudentClassHistory::updateOrCreate(
                        ['student_id' => $student->id, 'class_id' => $nextClass->id, 'tahun_ajaran_id' => $activeYear->id],
                        ['status_siswa' => 'Aktif']
                    );
                    $processed++;

                    return;
                }

                $skipped++;
            });
        });

        $message = "Kenaikan kelas massal selesai. {$processed} siswa diproses.";

        if ($skipped > 0) {
            $message .= " {$skipped} siswa belum memenuhi syarat dan tidak dipindahkan.";
        }

        return back()->with('success', $message.' Data lama tersimpan sebagai arsip.');
    }

    private function archiveStudent(Student $student, ?int $yearId): void
    {
        StudentArchive::create([
            'student_id' => $student->id,
            'class_id' => $student->class_id,
            'tahun_ajaran_id' => $yearId,
            'status_siswa' => $student->status_siswa ?? 'Aktif',
            'snapshot' => [
                'nama' => $student->nama,
                'nis' => $student->nis,
                'class_id' => $student->class_id,
                'status_siswa' => $student->status_siswa ?? 'Aktif',
            ],
        ]);

        StudentClassHistory::updateOrCreate(
            ['student_id' => $student->id, 'class_id' => $student->class_id, 'tahun_ajaran_id' => $yearId],
            ['status_siswa' => $student->status_siswa ?? 'Aktif']
        );
    }

    private function nextClassLabel(?SchoolClass $class): string
    {
        if (! $class) {
            return '-';
        }

        return match ($class->tingkat) {
            'VII' => str_replace('7', '8', str_replace('VII', 'VIII', $class->nama_kelas)),
            'VIII' => str_replace('8', '9', str_replace('VIII', 'IX', $class->nama_kelas)),
            'IX' => 'Lulus/Alumni',
            default => '-',
        };
    }

    private function findNextClass(?SchoolClass $class, AcademicYear $activeYear): ?SchoolClass
    {
        if (! $class) {
            return null;
        }

        $nextLevel = ['VII' => 'VIII', 'VIII' => 'IX'][$class->tingkat] ?? null;

        if (! $nextLevel) {
            return null;
        }

        $targetName = $this->nextClassLabel($class);

        $targetClasses = SchoolClass::where('tingkat', $nextLevel)
            ->where(function ($query) use ($activeYear): void {
                $query->where('tahun_ajaran_id', $activeYear->id)->orWhereNull('tahun_ajaran_id');
            })
            ->ordered()
            ->get();

        return $targetClasses->where('nama_kelas', $targetName)->values()->get(0)
            ?: $targetClasses->values()->get(0);
    }

    private function promotionStudentIdsFromFilters(array $filters, AcademicYear $activeYear)
    {
        $yearId = $filters['promotion_year_id'] ?? $activeYear->id;
        $classId = $filters['promotion_class_id'] ?? null;
        $level = $filters['promotion_level'] ?? null;
        $search = trim((string) ($filters['promotion_search'] ?? ''));

        $selectedYear = AcademicYear::find($yearId);

        return Student::where('status_siswa', 'Aktif')
            ->when($selectedYear && ! $selectedYear->is_active, fn ($query) => $query->where(fn ($item) => $item
                ->where('students.tahun_ajaran_id', $yearId)
                ->orWhereNull('students.tahun_ajaran_id')))
            ->when($classId, fn ($query) => $query->where('class_id', $classId))
            ->when($level, fn ($query) => $query->whereHas('schoolClass', fn ($classQuery) => $classQuery->where('tingkat', $level)))
            ->when($search !== '', fn ($query) => $query->academicSearch($search))
            ->pluck('id')
            ->unique()
            ->values();
    }

    private function promotionPreviewRow(Student $student, ?AcademicYear $activeYear): array
    {
        $from = $student->schoolClass;
        $targetLabel = $this->nextClassLabel($from);
        $status = 'Belum Diproses';
        $canProcess = true;

        if (! $from) {
            $status = 'Tidak Naik';
            $targetLabel = '-';
            $canProcess = false;
        } elseif ($from->tingkat !== 'IX' && $activeYear && ! $this->findNextClass($from, $activeYear)) {
            $status = 'Tidak Naik';
            $canProcess = false;
        } elseif ($from->tingkat === 'IX') {
            $status = 'Lulus';
        } elseif ($activeYear) {
            $status = 'Naik Kelas';
        }

        return [
            'student' => $student,
            'from' => $from,
            'wali_kelas' => $from?->waliKelas,
            'to_label' => $targetLabel,
            'status' => $status,
            'can_process' => $canProcess,
        ];
    }
}
