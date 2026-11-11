<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Alumni;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Grade;
use App\Models\Material;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentArchive;
use App\Models\StudentClassHistory;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingHistory;
use App\Services\AcademicYearService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AcademicArchiveController extends Controller
{
    private const SECTIONS = [
        'summary' => 'Ringkasan Tahun Akademik',
        'teachers' => 'Guru',
        'students' => 'Siswa',
        'classes' => 'Kelas',
        'homerooms' => 'Wali Kelas',
        'subjects' => 'Mata Pelajaran',
        'teaching' => 'Pembagian Mengajar',
        'plotting' => 'Pembagian Kelas',
        'grades' => 'Nilai',
        'materials' => 'Materi Pembelajaran',
        'assignments' => 'Tugas',
        'submissions' => 'Pengumpulan Tugas',
        'announcements' => 'Pengumuman',
        'alumni' => 'Alumni',
        'schedules' => 'Jadwal',
    ];

    private const UNAVAILABLE_SECTIONS = ['schedules'];

    public function index(Request $request, AcademicYearService $academicYears): View
    {
        $years = AcademicYear::latest('id')->get();
        $activeYear = $academicYears->active();
        $selectedAcademicYear = $this->selectedAcademicYear($request, $years, $activeYear);
        $selectedYear = $selectedAcademicYear?->id;
        $activeTab = $this->activeTab($request);
        $filters = $this->filters($request);

        $classes = $this->classesForYear($selectedYear)->get();
        $subjects = $this->subjectsForYear($selectedYear)->get();
        $teachers = $this->teachersForYear($selectedYear)->get();
        $students = $this->studentsForYear($selectedYear)->get();
        $stats = $this->stats($selectedYear);
        $yearSummaries = $years->map(fn (AcademicYear $year) => $this->yearSummary($year));

        $archives = collect(self::SECTIONS)
            ->except(self::UNAVAILABLE_SECTIONS)
            ->mapWithKeys(function (string $label, string $section) use ($selectedYear, $filters) {
                $query = $this->sectionQuery($section, $selectedYear, $filters);

                return [
                    $section => $query
                        ? $query->paginate(10, ['*'], $section.'_page')->withQueryString()
                        : collect(),
                ];
            });

        return view('admin.academic-archives.index', [
            'years' => $years,
            'activeYear' => $activeYear,
            'selectedAcademicYear' => $selectedAcademicYear,
            'selectedYear' => $selectedYear,
            'activeTab' => $activeTab,
            'sections' => self::SECTIONS,
            'unavailableSections' => self::UNAVAILABLE_SECTIONS,
            'stats' => $stats,
            'yearSummaries' => $yearSummaries,
            'classes' => $classes,
            'subjects' => $subjects,
            'teachers' => $teachers,
            'students' => $students,
            'archives' => $archives,
            'filters' => $filters,
        ]);
    }

    public function export(Request $request, string $section, string $format): StreamedResponse|View
    {
        abort_unless(array_key_exists($section, self::SECTIONS), 404);
        abort_unless(in_array($format, ['excel', 'pdf'], true), 404);
        abort_if(in_array($section, self::UNAVAILABLE_SECTIONS, true), 404);

        $years = AcademicYear::latest('id')->get();
        $selectedAcademicYear = $this->selectedAcademicYear($request, $years, app(AcademicYearService::class)->active());
        $filters = $this->filters($request);
        $rows = $this->sectionQuery($section, $selectedAcademicYear?->id, $filters)?->get() ?? collect();
        $table = $this->tableFor($section, $rows);
        $fileName = Str::slug(self::SECTIONS[$section].' '.$selectedAcademicYear?->nama).'.csv';

        if ($format === 'pdf') {
            return view('admin.academic-archives.print', [
                'section' => $section,
                'sectionLabel' => self::SECTIONS[$section],
                'selectedAcademicYear' => $selectedAcademicYear,
                'table' => $table,
                'filters' => $filters,
            ]);
        }

        return response()->streamDownload(function () use ($table): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $table['headers']);

            foreach ($table['rows'] as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function selectedAcademicYear(Request $request, Collection $years, ?AcademicYear $activeYear): ?AcademicYear
    {
        if ($request->filled('tahun_ajaran_id')) {
            return $years->firstWhere('id', $request->integer('tahun_ajaran_id')) ?: $years->first();
        }

        return $activeYear ?: $years->first();
    }

    private function activeTab(Request $request): string
    {
        $tab = $request->input('tab', 'summary');

        return array_key_exists($tab, self::SECTIONS) ? $tab : 'summary';
    }

    private function filters(Request $request): array
    {
        return [
            'search' => trim((string) $request->input('search')),
            'class_id' => $request->integer('class_id') ?: null,
            'subject_id' => $request->integer('subject_id') ?: null,
            'teacher_id' => $request->integer('teacher_id') ?: null,
            'student_id' => $request->integer('student_id') ?: null,
        ];
    }

    private function stats(?int $yearId): array
    {
        return [
            'teachers' => $this->teachersForYear($yearId)->count(),
            'students' => $this->studentsForYear($yearId)->count(),
            'classes' => $this->classesForYear($yearId)->count(),
            'subjects' => $this->subjectsForYear($yearId)->count(),
            'alumni' => Alumni::when($yearId, fn (Builder $query) => $query->where('tahun_ajaran_id', $yearId))->count(),
        ];
    }

    private function yearSummary(AcademicYear $year): array
    {
        return [
            'year' => $year,
            'teachers' => $this->teachersForYear($year->id)->count(),
            'students' => $this->studentsForYear($year->id)->count(),
            'classes' => $this->classesForYear($year->id)->count(),
            'subjects' => $this->subjectsForYear($year->id)->count(),
            'grades' => Grade::where('tahun_ajaran_id', $year->id)->count(),
            'assignments' => Assignment::where('tahun_ajaran_id', $year->id)->count(),
            'alumni' => Alumni::where('tahun_ajaran_id', $year->id)->count(),
        ];
    }

    private function sectionQuery(string $section, ?int $yearId, array $filters): ?Builder
    {
        $query = match ($section) {
            'summary' => AcademicYear::query()->when($yearId, fn (Builder $query) => $query->whereKey($yearId)),
            'teachers' => $this->teachersForYear($yearId)->with('user'),
            'students' => $this->studentsForYear($yearId)->with(['user', 'schoolClass', 'academicYear']),
            'classes' => $this->classesForYear($yearId)->with(['waliKelas', 'academicYear'])->withCount('students'),
            'homerooms' => $this->classesForYear($yearId)->with(['waliKelas', 'academicYear'])->whereNotNull('wali_kelas_id'),
            'subjects' => $this->subjectsForYear($yearId)->with('academicYear'),
            'teaching' => TeachingHistory::with(['teacher', 'subject', 'schoolClass', 'academicYear'])->when($yearId, fn (Builder $query) => $query->where('tahun_ajaran_id', $yearId)),
            'plotting' => StudentClassHistory::with(['student', 'schoolClass', 'academicYear'])->when($yearId, fn (Builder $query) => $query->where('tahun_ajaran_id', $yearId)),
            'grades' => Grade::with(['student', 'subject', 'schoolClass', 'academicYear'])->when($yearId, fn (Builder $query) => $query->where('tahun_ajaran_id', $yearId)),
            'materials' => Material::with(['teacher', 'subject', 'schoolClass', 'academicYear'])->when($yearId, fn (Builder $query) => $query->where('tahun_ajaran_id', $yearId)),
            'assignments' => Assignment::with(['teacher', 'subject', 'schoolClass', 'academicYear'])->withCount('submissions')->when($yearId, fn (Builder $query) => $query->where('tahun_ajaran_id', $yearId)),
            'submissions' => AssignmentSubmission::with(['student', 'assignment.subject', 'assignment.schoolClass', 'academicYear'])->when($yearId, fn (Builder $query) => $query->where('tahun_ajaran_id', $yearId)),
            'announcements' => Announcement::with(['author', 'subject', 'schoolClass', 'academicYear'])->when($yearId, fn (Builder $query) => $query->where('tahun_ajaran_id', $yearId)),
            'alumni' => Alumni::with(['student.schoolClass', 'academicYear'])->when($yearId, fn (Builder $query) => $query->where('tahun_ajaran_id', $yearId)),
            default => null,
        };

        if (! $query) {
            return null;
        }

        $this->applyCommonFilters($query, $section, $filters);

        return match ($section) {
            'teachers', 'students' => $query->orderBy('nama'),
            'subjects' => $query->orderBy('nama_mapel'),
            'classes', 'homerooms' => $query->ordered(),
            'summary' => $query->latest('id'),
            default => $query->latest(),
        };
    }

    private function applyCommonFilters(Builder $query, string $section, array $filters): void
    {
        $search = $filters['search'];

        if ($search !== '') {
            match ($section) {
                'summary' => $query->where('nama', 'like', "%{$search}%"),
                'teachers' => $query->where(fn (Builder $item) => $item->where('nama', 'like', "%{$search}%")->orWhere('nip', 'like', "%{$search}%")),
                'students' => $query->where(fn (Builder $item) => $item->where('nama', 'like', "%{$search}%")->orWhere('nis', 'like', "%{$search}%")),
                'classes', 'homerooms' => $query->where(fn (Builder $item) => $item->where('nama_kelas', 'like', "%{$search}%")->orWhere('tingkat', 'like', "%{$search}%")->orWhereHas('waliKelas', fn (Builder $teacher) => $teacher->where('nama', 'like', "%{$search}%"))),
                'subjects' => $query->where(fn (Builder $item) => $item->where('nama_mapel', 'like', "%{$search}%")->orWhere('kode_mapel', 'like', "%{$search}%")),
                'teaching' => $query->where(fn (Builder $item) => $item->whereHas('teacher', fn (Builder $teacher) => $teacher->where('nama', 'like', "%{$search}%"))->orWhereHas('subject', fn (Builder $subject) => $subject->where('nama_mapel', 'like', "%{$search}%"))->orWhereHas('schoolClass', fn (Builder $class) => $class->where('nama_kelas', 'like', "%{$search}%"))),
                'plotting' => $query->where(fn (Builder $item) => $item->whereHas('student', fn (Builder $student) => $student->where('nama', 'like', "%{$search}%")->orWhere('nis', 'like', "%{$search}%"))->orWhereHas('schoolClass', fn (Builder $class) => $class->where('nama_kelas', 'like', "%{$search}%"))),
                'grades' => $query->where(fn (Builder $item) => $item->whereHas('student', fn (Builder $student) => $student->where('nama', 'like', "%{$search}%")->orWhere('nis', 'like', "%{$search}%"))->orWhereHas('subject', fn (Builder $subject) => $subject->where('nama_mapel', 'like', "%{$search}%"))->orWhere('status_kelulusan', 'like', "%{$search}%")),
                'materials', 'assignments' => $query->where(fn (Builder $item) => $item->where('judul', 'like', "%{$search}%")->orWhere('deskripsi', 'like', "%{$search}%")->orWhereHas('teacher', fn (Builder $teacher) => $teacher->where('nama', 'like', "%{$search}%"))->orWhereHas('subject', fn (Builder $subject) => $subject->where('nama_mapel', 'like', "%{$search}%"))),
                'submissions' => $query->where(fn (Builder $item) => $item->where('status', 'like', "%{$search}%")->orWhereHas('student', fn (Builder $student) => $student->where('nama', 'like', "%{$search}%")->orWhere('nis', 'like', "%{$search}%"))->orWhereHas('assignment', fn (Builder $assignment) => $assignment->where('judul', 'like', "%{$search}%"))),
                'announcements' => $query->where(fn (Builder $item) => $item->where('judul', 'like', "%{$search}%")->orWhere('isi', 'like', "%{$search}%")->orWhere('audience', 'like', "%{$search}%")),
                'alumni' => $query->whereHas('student', fn (Builder $student) => $student->where('nama', 'like', "%{$search}%")->orWhere('nis', 'like', "%{$search}%")),
                default => null,
            };
        }

        if ($filters['class_id']) {
            match ($section) {
                'students' => $query->where('class_id', $filters['class_id']),
                'classes', 'homerooms' => $query->whereKey($filters['class_id']),
                'teaching', 'plotting', 'grades', 'materials', 'assignments', 'announcements' => $query->where('class_id', $filters['class_id']),
                'submissions' => $query->whereHas('assignment', fn (Builder $assignment) => $assignment->where('class_id', $filters['class_id'])),
                default => null,
            };
        }

        if ($filters['subject_id']) {
            match ($section) {
                'subjects' => $query->whereKey($filters['subject_id']),
                'teaching', 'grades', 'materials', 'assignments', 'announcements' => $query->where('subject_id', $filters['subject_id']),
                'submissions' => $query->whereHas('assignment', fn (Builder $assignment) => $assignment->where('subject_id', $filters['subject_id'])),
                default => null,
            };
        }

        if ($filters['teacher_id']) {
            match ($section) {
                'teachers' => $query->whereKey($filters['teacher_id']),
                'classes', 'homerooms' => $query->where('wali_kelas_id', $filters['teacher_id']),
                'teaching', 'materials', 'assignments' => $query->where('teacher_id', $filters['teacher_id']),
                default => null,
            };
        }

        if ($filters['student_id']) {
            match ($section) {
                'students' => $query->whereKey($filters['student_id']),
                'plotting', 'grades', 'submissions', 'alumni' => $query->where('student_id', $filters['student_id']),
                default => null,
            };
        }
    }

    private function teachersForYear(?int $yearId): Builder
    {
        return Teacher::query()
            ->when($yearId, fn (Builder $query) => $query->where(function (Builder $item) use ($yearId): void {
                $item->whereIn('id', TeachingHistory::where('tahun_ajaran_id', $yearId)->select('teacher_id'))
                    ->orWhereHas('teacherSubjects', fn (Builder $relation) => $relation->where('tahun_ajaran_id', $yearId))
                    ->orWhereHas('homeroomClasses', fn (Builder $relation) => $relation->where('tahun_ajaran_id', $yearId))
                    ->orWhereHas('materials', fn (Builder $relation) => $relation->where('tahun_ajaran_id', $yearId))
                    ->orWhereHas('assignments', fn (Builder $relation) => $relation->where('tahun_ajaran_id', $yearId));
            }));
    }

    private function studentsForYear(?int $yearId): Builder
    {
        return Student::query()
            ->when($yearId, fn (Builder $query) => $query->where(function (Builder $item) use ($yearId): void {
                $item->where('tahun_ajaran_id', $yearId)
                    ->orWhereIn('id', StudentArchive::where('tahun_ajaran_id', $yearId)->select('student_id'))
                    ->orWhereIn('id', StudentClassHistory::where('tahun_ajaran_id', $yearId)->select('student_id'))
                    ->orWhereHas('grades', fn (Builder $relation) => $relation->where('tahun_ajaran_id', $yearId))
                    ->orWhereHas('submissions', fn (Builder $relation) => $relation->where('tahun_ajaran_id', $yearId));
            }));
    }

    private function classesForYear(?int $yearId): Builder
    {
        return SchoolClass::query()
            ->when($yearId, fn (Builder $query) => $query->where(function (Builder $item) use ($yearId): void {
                $item->where('tahun_ajaran_id', $yearId)
                    ->orWhereIn('id', StudentArchive::where('tahun_ajaran_id', $yearId)->select('class_id'))
                    ->orWhereIn('id', StudentClassHistory::where('tahun_ajaran_id', $yearId)->select('class_id'))
                    ->orWhereIn('id', TeachingHistory::where('tahun_ajaran_id', $yearId)->select('class_id'));
            }));
    }

    private function subjectsForYear(?int $yearId): Builder
    {
        return Subject::query()
            ->when($yearId, fn (Builder $query) => $query->where(function (Builder $item) use ($yearId): void {
                $item->where('tahun_ajaran_id', $yearId)
                    ->orWhereHas('teacherSubjects', fn (Builder $relation) => $relation->where('tahun_ajaran_id', $yearId))
                    ->orWhereHas('materials', fn (Builder $relation) => $relation->where('tahun_ajaran_id', $yearId))
                    ->orWhereHas('assignments', fn (Builder $relation) => $relation->where('tahun_ajaran_id', $yearId))
                    ->orWhereHas('grades', fn (Builder $relation) => $relation->where('tahun_ajaran_id', $yearId));
            }));
    }

    private function tableFor(string $section, Collection $rows): array
    {
        return match ($section) {
            'summary' => [
                'headers' => ['Tahun Ajaran', 'Tanggal Mulai', 'Tanggal Selesai', 'Status'],
                'rows' => $rows->map(fn (AcademicYear $year) => [$year->nama, $year->tanggal_mulai?->format('d/m/Y') ?: '-', $year->tanggal_selesai?->format('d/m/Y') ?: '-', $year->is_active ? 'Aktif' : 'Arsip']),
            ],
            'teachers' => [
                'headers' => ['NIP', 'Nama', 'Jenis Kelamin', 'No HP', 'Alamat'],
                'rows' => $rows->map(fn (Teacher $teacher) => [$teacher->nip ?: '-', $teacher->nama, $teacher->jenis_kelamin, $teacher->no_hp ?: '-', $teacher->alamat ?: '-']),
            ],
            'students' => [
                'headers' => ['NIS', 'Nama', 'Kelas', 'Status', 'No HP', 'Alamat'],
                'rows' => $rows->map(fn (Student $student) => [$student->nis, $student->nama, $student->schoolClass->nama_kelas ?? '-', $student->status_siswa ?? '-', $student->no_hp ?: '-', $student->alamat ?: '-']),
            ],
            'classes', 'homerooms' => [
                'headers' => ['Kelas', 'Tingkat', 'Wali Kelas', 'Tahun Ajaran'],
                'rows' => $rows->map(fn (SchoolClass $class) => [$class->nama_kelas, $class->tingkat, $class->waliKelas->nama ?? '-', $class->academicYear->nama ?? '-']),
            ],
            'subjects' => [
                'headers' => ['Kode', 'Mata Pelajaran', 'Tahun Ajaran'],
                'rows' => $rows->map(fn (Subject $subject) => [$subject->kode_mapel, $subject->nama_mapel, $subject->academicYear->nama ?? '-']),
            ],
            'teaching' => [
                'headers' => ['Guru', 'Mata Pelajaran', 'Kelas', 'Tahun Ajaran'],
                'rows' => $rows->map(fn (TeachingHistory $history) => [$history->teacher->nama ?? '-', $history->subject->nama_mapel ?? '-', $history->schoolClass->nama_kelas ?? '-', $history->academicYear->nama ?? '-']),
            ],
            'plotting' => [
                'headers' => ['Siswa', 'NIS', 'Kelas', 'Status', 'Tahun Ajaran'],
                'rows' => $rows->map(fn (StudentClassHistory $history) => [$history->student->nama ?? '-', $history->student->nis ?? '-', $history->schoolClass->nama_kelas ?? '-', $history->status_siswa, $history->academicYear->nama ?? '-']),
            ],
            'grades' => [
                'headers' => ['Siswa', 'Kelas', 'Mata Pelajaran', 'Tugas', 'Kuis', 'UTS', 'UAS', 'Akhir', 'Status'],
                'rows' => $rows->map(fn (Grade $grade) => [$grade->student->nama ?? '-', $grade->schoolClass->nama_kelas ?? '-', $grade->subject->nama_mapel ?? '-', $grade->nilai_tugas ?? '-', $grade->nilai_kuis ?? '-', $grade->nilai_uts ?? '-', $grade->nilai_uas ?? '-', $grade->nilai_akhir ?? '-', $grade->status_kelulusan ?? '-']),
            ],
            'materials' => [
                'headers' => ['Judul', 'Guru', 'Mata Pelajaran', 'Kelas', 'Deskripsi', 'File'],
                'rows' => $rows->map(fn (Material $material) => [$material->judul, $material->teacher->nama ?? '-', $material->subject->nama_mapel ?? '-', $material->schoolClass->nama_kelas ?? '-', $material->deskripsi ?: '-', $material->file ?: '-']),
            ],
            'assignments' => [
                'headers' => ['Judul', 'Guru', 'Mata Pelajaran', 'Kelas', 'Deadline', 'Status', 'Pengumpulan'],
                'rows' => $rows->map(fn (Assignment $assignment) => [$assignment->judul, $assignment->teacher->nama ?? '-', $assignment->subject->nama_mapel ?? '-', $assignment->schoolClass->nama_kelas ?? '-', $assignment->deadline?->format('d/m/Y H:i') ?: '-', $assignment->status_aktif ? 'Aktif' : 'Nonaktif', $assignment->submissions_count ?? $assignment->submissions->count()]),
            ],
            'submissions' => [
                'headers' => ['Siswa', 'Tugas', 'Tanggal Kumpul', 'Status', 'Nilai', 'Catatan'],
                'rows' => $rows->map(fn (AssignmentSubmission $submission) => [$submission->student->nama ?? '-', $submission->assignment->judul ?? '-', $submission->tanggal_kumpul?->format('d/m/Y H:i') ?: '-', $submission->status, $submission->nilai ?? '-', $submission->catatan ?: '-']),
            ],
            'announcements' => [
                'headers' => ['Judul', 'Audiens', 'Kelas', 'Mata Pelajaran', 'Penulis', 'Publish', 'Status'],
                'rows' => $rows->map(fn (Announcement $announcement) => [$announcement->judul, $announcement->audienceLabel(), $announcement->schoolClass->nama_kelas ?? '-', $announcement->subject->nama_mapel ?? '-', $announcement->author->name ?? '-', $announcement->publish_at?->format('d/m/Y H:i') ?: '-', $announcement->status_aktif ? 'Aktif' : 'Nonaktif']),
            ],
            'alumni' => [
                'headers' => ['NIS', 'Nama', 'Kelas Terakhir', 'Tahun Lulus', 'Tanggal Lulus'],
                'rows' => $rows->map(fn (Alumni $alumni) => [$alumni->student->nis ?? '-', $alumni->student->nama ?? '-', $alumni->student->schoolClass->nama_kelas ?? '-', $alumni->academicYear->nama ?? '-', $alumni->tanggal_lulus?->format('d/m/Y') ?: '-']),
            ],
            default => ['headers' => [], 'rows' => collect()],
        };
    }
}
