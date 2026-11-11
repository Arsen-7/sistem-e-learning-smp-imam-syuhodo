<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Concerns\AuthorizesTeacherAssignments;
use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Grade;
use App\Models\GradeWeight;
use App\Models\Student;
use App\Models\User;
use App\Services\AcademicService;
use App\Services\AcademicYearService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    use AuthorizesTeacherAssignments;

    public function index(AcademicYearService $academicYears): View
    {
        $teacher = $this->currentTeacher();
        $query = Assignment::with(['subject', 'schoolClass'])
            ->withCount('submissions')
            ->where('teacher_id', $teacher->id);

        $academicYears->applyActiveYear($query);

        $assignments = $query->latest()->paginate(10);

        return view('teacher.assignments.index', compact('assignments'));
    }

    public function create(): View
    {
        $teacher = $this->currentTeacher();

        return view('teacher.assignments.create', ['pairs' => $this->assignedPairs($teacher)]);
    }

    public function store(Request $request, NotificationService $notifications, AcademicYearService $academicYears): RedirectResponse
    {
        $teacher = $this->currentTeacher();
        $validated = $request->validate($this->rules(), $this->messages());
        $this->ensureTeacherHandles($teacher, (int) $validated['subject_id'], (int) $validated['class_id']);

        if ($request->hasFile('file')) {
            $validated['file'] = $request->file('file')->store('assignments', 'public');
        }

        $validated['teacher_id'] = $teacher->id;
        $validated['tahun_ajaran_id'] = $academicYears->activeId();
        $validated['semester_id'] = $academicYears->activeSemesterId();
        $validated['status_aktif'] = $request->boolean('status_aktif', true);
        $assignment = Assignment::create($validated);

        if ($assignment->status_aktif) {
            $students = User::whereHas('student', fn ($query) => $query->where('class_id', $assignment->class_id))->get();
            $notifications->notifyUsers(
                $students,
                'Tugas baru: '.$assignment->judul,
                ($assignment->subject->nama_mapel ?? 'Mata pelajaran').' - deadline '.$assignment->deadline->format('d M Y H:i'),
                route('student.assignments.show', $assignment, false),
                'assignment',
                Assignment::class,
                $assignment->id
            );
        }

        return redirect()->route('teacher.assignments.index')->with('success', 'Tugas berhasil dibuat.');
    }

    public function show(Request $request, Assignment $assignment): View
    {
        $teacher = $this->currentTeacher();
        abort_unless($assignment->teacher_id === $teacher->id, 403);

        $assignment->load(['subject', 'schoolClass']);
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'status' => $request->query('status'),
        ];

        $studentQuery = Student::query()
            ->where(function ($query) use ($assignment): void {
                // Keep historical submissions visible after a student changes class or status.
                $query->where(fn ($item) => $item->where('class_id', $assignment->class_id)->where('status_siswa', 'Aktif'))
                    ->orWhereHas('submissions', fn ($item) => $item->where('assignment_id', $assignment->id));
            })
            ->when($filters['search'] !== '', fn ($query) => $query->academicSearch($filters['search']))
            ->when($filters['status'] === 'submitted', fn ($query) => $query->whereHas('submissions', fn ($item) => $item->where('assignment_id', $assignment->id)))
            ->when($filters['status'] === 'missing', fn ($query) => $query->whereDoesntHave('submissions', fn ($item) => $item->where('assignment_id', $assignment->id)))
            ->orderBy('nama');

        $students = $studentQuery->paginate(15)->withQueryString();
        $submissions = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->whereIn('student_id', $students->getCollection()->pluck('id'))
            ->get()
            ->keyBy('student_id');
        $totalStudents = Student::query()
            ->where(fn ($query) => $query->where('class_id', $assignment->class_id)->where('status_siswa', 'Aktif'))
            ->orWhereHas('submissions', fn ($query) => $query->where('assignment_id', $assignment->id))
            ->count();
        $submittedCount = AssignmentSubmission::where('assignment_id', $assignment->id)->count();

        return view('teacher.assignments.show', compact('assignment', 'students', 'submissions', 'totalStudents', 'submittedCount', 'filters'));
    }

    public function submissions(Request $request, AcademicYearService $academicYears): View
    {
        $teacher = $this->currentTeacher();
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'status' => $request->query('status'),
        ];

        $query = AssignmentSubmission::with(['assignment.subject', 'assignment.schoolClass', 'student'])
            ->whereHas('assignment', function ($assignmentQuery) use ($teacher, $academicYears): void {
                $assignmentQuery->where('teacher_id', $teacher->id);
                $academicYears->applyActiveYear($assignmentQuery);
            })
            ->when($filters['search'] !== '', function ($submissionQuery) use ($filters): void {
                $search = mb_strtolower($filters['search']);

                $submissionQuery->where(function ($item) use ($search): void {
                    $item
                        ->whereHas('student', fn ($student) => $student->academicSearch($search))
                        ->orWhereHas('assignment', fn ($assignment) => $assignment->whereRaw('LOWER(judul) like ?', ["%{$search}%"]));
                });
            })
            ->when($filters['status'] === 'graded', fn ($submissionQuery) => $submissionQuery->whereNotNull('nilai'))
            ->when($filters['status'] === 'ungraded', fn ($submissionQuery) => $submissionQuery->whereNull('nilai'))
            ->latest('tanggal_kumpul');

        $academicYears->applyActiveYear($query);

        $submissions = $query->paginate(15)->withQueryString();

        return view('teacher.assignments.submissions', compact('submissions', 'filters'));
    }

    public function gradeReport(Request $request, Assignment $assignment): View|StreamedResponse
    {
        $teacher = $this->currentTeacher();
        abort_unless($assignment->teacher_id === $teacher->id, 403);

        $assignment->load(['subject', 'schoolClass']);
        $submissions = AssignmentSubmission::with('student')
            ->where('assignment_id', $assignment->id)
            ->orderBy('tanggal_kumpul')
            ->get();

        if ($request->query('export') === 'csv') {
            $fileName = 'laporan-nilai-tugas-'.str($assignment->judul)->slug().'.csv';

            return response()->streamDownload(function () use ($assignment, $submissions): void {
                $output = fopen('php://output', 'w');
                fputcsv($output, ['Nama Siswa', 'Kelas', 'Mata Pelajaran', 'Nama Tugas', 'Nilai', 'Status Pengumpulan', 'Feedback', 'Tanggal Pengumpulan']);
                foreach ($submissions as $submission) {
                    fputcsv($output, [
                        $submission->student?->nama ?? '-',
                        $assignment->schoolClass?->nama_kelas ?? '-',
                        $assignment->subject?->nama_mapel ?? '-',
                        $assignment->judul,
                        $submission->nilai ?? 'Belum dinilai',
                        $submission->nilai !== null ? 'Sudah Dinilai' : 'Sudah Dikumpulkan',
                        $submission->catatan ?? '-',
                        optional($submission->tanggal_kumpul)->format('d-m-Y H:i') ?? '-',
                    ]);
                }
                fclose($output);
            }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        return view('teacher.assignments.grade-report', compact('assignment', 'submissions'));
    }

    public function edit(Assignment $assignment): View
    {
        $teacher = $this->currentTeacher();
        abort_unless($assignment->teacher_id === $teacher->id, 403);

        return view('teacher.assignments.edit', ['assignment' => $assignment, 'pairs' => $this->assignedPairs($teacher)]);
    }

    public function update(Request $request, Assignment $assignment): RedirectResponse
    {
        $teacher = $this->currentTeacher();
        abort_unless($assignment->teacher_id === $teacher->id, 403);

        $validated = $request->validate($this->rules(), $this->messages());
        $this->ensureTeacherHandles($teacher, (int) $validated['subject_id'], (int) $validated['class_id']);

        if ($request->hasFile('file')) {
            if ($assignment->file) {
                Storage::disk('public')->delete($assignment->file);
            }

            $validated['file'] = $request->file('file')->store('assignments', 'public');
        }

        $validated['status_aktif'] = $request->boolean('status_aktif');
        $assignment->update($validated);

        return redirect()->route('teacher.assignments.index')->with('success', 'Tugas berhasil diperbarui.');
    }

    public function destroy(Assignment $assignment): RedirectResponse
    {
        $teacher = $this->currentTeacher();
        abort_unless($assignment->teacher_id === $teacher->id, 403);

        if ($assignment->file) {
            Storage::disk('public')->delete($assignment->file);
        }

        $assignment->delete();

        return redirect()->route('teacher.assignments.index')->with('success', 'Tugas berhasil dihapus.');
    }

    public function gradeSubmission(Request $request, Assignment $assignment, AssignmentSubmission $submission, AcademicService $service, NotificationService $notifications, AcademicYearService $academicYears): RedirectResponse
    {
        $teacher = $this->currentTeacher();
        abort_unless($assignment->teacher_id === $teacher->id && $submission->assignment_id === $assignment->id, 403);

        $validated = $request->validate([
            'nilai' => ['required', 'numeric', 'min:0', 'max:100'],
            'catatan' => ['nullable', 'string'],
        ], [
            'nilai.required' => 'Nilai tugas wajib diisi.',
            'nilai.numeric' => 'Nilai tugas harus berupa angka.',
            'nilai.min' => 'Nilai minimal 0.',
            'nilai.max' => 'Nilai maksimal 100.',
        ]);

        $submission->update($validated + ['status' => 'dinilai']);

        $yearId = $assignment->tahun_ajaran_id ?: $academicYears->activeId();
        $semesterId = $academicYears->activeSemesterId();

        $avgTugas = AssignmentSubmission::whereHas('assignment', function ($query) use ($assignment, $yearId, $semesterId): void {
            $query->where('subject_id', $assignment->subject_id)
                ->where('class_id', $assignment->class_id)
                ->where('tahun_ajaran_id', $yearId)
                ->when($semesterId, fn ($item) => $item->where('semester_id', $semesterId));
        })
            ->where('student_id', $submission->student_id)
            ->whereNotNull('nilai')
            ->avg('nilai');

        $grade = Grade::firstOrCreate(
            ['student_id' => $submission->student_id, 'subject_id' => $assignment->subject_id, 'class_id' => $assignment->class_id, 'tahun_ajaran_id' => $yearId, 'semester_id' => $semesterId],
            ['tahun_ajaran_id' => $yearId, 'semester_id' => $semesterId]
        );
        $grade->update([
            'nilai_tugas' => $avgTugas ? round((float) $avgTugas, 2) : null,
            'tahun_ajaran_id' => $yearId,
            'semester_id' => $semesterId,
        ]);

        $weight = GradeWeight::firstOrCreate(
            ['subject_id' => $assignment->subject_id, 'class_id' => $assignment->class_id, 'tahun_ajaran_id' => $assignment->tahun_ajaran_id ?: $academicYears->activeId(), 'semester_id' => $assignment->semester_id ?: $academicYears->activeSemesterId()],
            ['bobot_tugas' => 25, 'bobot_kuis' => 20, 'bobot_uts' => 25, 'bobot_uas' => 30, 'kkm' => 75]
        );

        if ($service->bobotValid($weight)) {
            $service->hitungDanSimpan($grade->fresh(), $weight);
        }

        if ($submission->student?->user) {
            $notifications->notifyUser(
                $submission->student->user,
                'Nilai tugas sudah diberikan',
                $assignment->judul.' mendapat nilai '.$submission->nilai,
                route('student.assignments.show', $assignment, false),
                'grade',
                AssignmentSubmission::class,
                $submission->id
            );
        }

        return back()->with('success', 'Nilai pengumpulan tugas berhasil disimpan.');
    }

    private function rules(): array
    {
        return [
            'subject_id' => ['required', 'exists:subjects,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,jpg,jpeg,png', 'max:5120'],
            'deadline' => ['required', 'date'],
        ];
    }

    private function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'exists' => ':attribute tidak ditemukan.',
            'date' => ':attribute harus berupa tanggal yang valid.',
            'file' => ':attribute harus berupa file.',
            'mimes' => 'Format file tidak didukung.',
            'max' => ':attribute maksimal 5 MB.',
        ];
    }
}
