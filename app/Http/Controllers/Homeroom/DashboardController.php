<?php

namespace App\Http\Controllers\Homeroom;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View { return view('homeroom.dashboard', $this->data($request)); }
    public function students(Request $request): View { return view('homeroom.students', $this->data($request)); }
    public function grades(Request $request): View { return view('homeroom.grades', $this->data($request)); }
    public function submissions(Request $request): View { return view('homeroom.submissions', $this->data($request)); }

    public function report(Request $request): View|StreamedResponse
    {
        $data = $this->data($request);
        $type = $request->query('type', 'students');
        abort_unless(in_array($type, ['students', 'grades', 'submissions', 'progress'], true), 404);
        if ($request->query('format') === 'csv') {
            return response()->streamDownload(function () use ($data, $type): void {
                $out = fopen('php://output', 'w');
                if ($type === 'students') {
                    fputcsv($out, ['NIS', 'NISN', 'Nama Siswa', 'Kelas', 'Tahun Pelajaran', 'Status']);
                    foreach ($data['students'] as $student) fputcsv($out, [$student->nis, $student->nisn ?? '-', $student->nama, $data['selectedClass']?->nama_kelas, $data['selectedYear']?->nama, $student->status_siswa]);
                } elseif ($type === 'grades' || $type === 'progress') {
                    fputcsv($out, ['Nama Siswa', 'Mata Pelajaran', 'Tugas', 'Kuis', 'UTS', 'UAS', 'Nilai Akhir']);
                    foreach ($data['grades'] as $grade) fputcsv($out, [$grade->student?->nama, $grade->subject?->nama_mapel, $grade->nilai_tugas, $grade->nilai_kuis, $grade->nilai_uts, $grade->nilai_uas, $grade->nilai_akhir]);
                } else {
                    fputcsv($out, ['Nama Siswa', 'Mata Pelajaran', 'Tugas', 'Deadline', 'Status Pengumpulan', 'Status Penilaian', 'Nilai']);
                    foreach ($data['submissionRows'] as $row) fputcsv($out, [$row['student']->nama, $row['assignment']->subject?->nama_mapel, $row['assignment']->judul, optional($row['assignment']->deadline)->format('d-m-Y H:i'), $row['collection_status'], $row['grading_status'], $row['submission']?->nilai]);
                }
                fclose($out);
            }, "laporan-wali-kelas-{$type}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
        }
        return view('homeroom.report', $data + compact('type'));
    }

    private function data(Request $request): array
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403, 'Akun wali kelas belum memiliki profil guru.');
        $years = AcademicYear::orderByDesc('tanggal_mulai')->orderByDesc('id')->get();
        $yearId = $request->integer('tahun_ajaran_id') ?: $years->firstWhere('is_active', true)?->id;
        $classes = SchoolClass::with('academicYear')->where('wali_kelas_id', $teacher->id)->when($yearId, fn ($query) => $query->where('tahun_ajaran_id', $yearId))->ordered()->get();
        $selectedClass = $classes->firstWhere('id', $request->integer('class_id')) ?: $classes->first();
        $selectedYear = $selectedClass?->academicYear ?: $years->firstWhere('id', $yearId);
        $semesters = $selectedYear ? Semester::where('tahun_ajaran_id', $selectedYear->id)->orderBy('nama')->get() : collect();
        $semesterId = $request->integer('semester_id') ?: $semesters->firstWhere('is_active', true)?->id;
        $selectedSemester = $semesters->firstWhere('id', $semesterId);
        $subjectId = $request->integer('subject_id') ?: null;
        $students = $this->studentsFor($selectedClass, $selectedYear?->id);
        $studentIds = $students->pluck('id');
        $grades = Grade::with(['student', 'subject', 'academicYear', 'semester'])->where('class_id', $selectedClass?->id)->whereIn('student_id', $studentIds)
            ->when($selectedYear, fn ($query) => $query->where('tahun_ajaran_id', $selectedYear->id))->when($semesterId, fn ($query) => $query->where('semester_id', $semesterId))->when($subjectId, fn ($query) => $query->where('subject_id', $subjectId))->orderBy('student_id')->orderBy('subject_id')->get();
        $assignments = Assignment::with(['subject', 'schoolClass'])->where('class_id', $selectedClass?->id)
            ->when($selectedYear, fn ($query) => $query->where('tahun_ajaran_id', $selectedYear->id))->when($semesterId, fn ($query) => $query->where('semester_id', $semesterId))->when($subjectId, fn ($query) => $query->where('subject_id', $subjectId))->latest('deadline')->get();
        $subjects = Subject::whereIn('id', $grades->pluck('subject_id')->merge($assignments->pluck('subject_id'))->unique())->orderBy('nama_mapel')->get();
        $submissionRows = $this->submissionRows($students, $assignments);
        $graded = $grades->whereNotNull('nilai_akhir');
        $summary = ['students' => $students->count(), 'average' => $graded->avg('nilai_akhir'), 'good' => $graded->where('nilai_akhir', '>=', 75)->pluck('student_id')->unique()->count(), 'attention' => $graded->where('nilai_akhir', '<', 75)->pluck('student_id')->unique()->count(), 'submitted_percent' => $submissionRows->count() ? round($submissionRows->where('submission', '!==', null)->count() / $submissionRows->count() * 100, 1) : 0, 'graded_percent' => $submissionRows->count() ? round($submissionRows->filter(fn ($row) => $row['submission']?->nilai !== null)->count() / $submissionRows->count() * 100, 1) : 0];
        $subjectAverages = $grades->whereNotNull('nilai_akhir')->groupBy('subject_id')->map(fn ($items) => ['name' => $items->first()->subject?->nama_mapel ?? '-', 'average' => $items->avg('nilai_akhir')])->values();
        return compact('years', 'classes', 'selectedClass', 'selectedYear', 'semesters', 'selectedSemester', 'semesterId', 'subjects', 'subjectId', 'students', 'grades', 'assignments', 'submissionRows', 'summary', 'subjectAverages');
    }

    private function studentsFor(?SchoolClass $class, ?int $yearId): Collection
    {
        if (! $class) return collect();
        return Student::where(function ($query) use ($class, $yearId): void {
            $query->where(fn ($item) => $item->where('class_id', $class->id)->when($yearId, fn ($current) => $current->where('tahun_ajaran_id', $yearId)))->orWhereHas('classHistories', fn ($history) => $history->where('class_id', $class->id)->when($yearId, fn ($item) => $item->where('tahun_ajaran_id', $yearId)));
        })->orderBy('nama')->get();
    }

    private function submissionRows(Collection $students, Collection $assignments): Collection
    {
        $submissions = AssignmentSubmission::whereIn('assignment_id', $assignments->pluck('id'))->whereIn('student_id', $students->pluck('id'))->get()->keyBy(fn ($item) => $item->assignment_id.'-'.$item->student_id);
        return $assignments->flatMap(fn ($assignment) => $students->map(function ($student) use ($assignment, $submissions) {
            $submission = $submissions->get($assignment->id.'-'.$student->id);
            return ['student' => $student, 'assignment' => $assignment, 'submission' => $submission, 'collection_status' => ! $submission ? 'Belum Mengumpulkan' : ($submission->status === 'terlambat' ? 'Terlambat' : 'Sudah Mengumpulkan'), 'grading_status' => $submission?->nilai !== null ? 'Sudah Dinilai' : 'Belum Dinilai'];
        }));
    }
}
