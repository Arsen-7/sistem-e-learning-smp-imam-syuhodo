<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Subject;
use App\Services\AcademicYearService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    public function index(Request $request, AcademicYearService $academicYears): View
    {
        $student = auth()->user()->student;
        abort_unless($student, 403);

        $selectedSubject = $request->integer('subject_id') ?: null;

        $query = Assignment::with(['teacher', 'subject', 'schoolClass', 'submissions' => fn ($query) => $query->where('student_id', $student->id)])
            ->where('class_id', $student->class_id)
            ->where('status_aktif', true)
            ->when($selectedSubject, fn ($item) => $item->where('subject_id', $selectedSubject));

        $academicYears->applyActiveYear($query);

        $assignments = $query->latest()->paginate(10)->withQueryString();

        $subjects = Subject::whereHas('assignments', fn ($query) => $query->where('class_id', $student->class_id))
            ->orderBy('nama_mapel')
            ->get();

        return view('student.assignments.index', compact('assignments', 'subjects', 'selectedSubject'));
    }

    public function show(Assignment $assignment): View
    {
        $student = auth()->user()->student;
        abort_unless($student && $assignment->class_id === $student->class_id && $assignment->status_aktif, 403);

        $assignment->load(['teacher', 'subject', 'schoolClass']);
        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->first();

        return view('student.assignments.show', compact('assignment', 'submission'));
    }

    public function submit(Request $request, Assignment $assignment, NotificationService $notifications, AcademicYearService $academicYears): RedirectResponse
    {
        $student = auth()->user()->student;
        abort_unless($student && $assignment->class_id === $student->class_id && $assignment->status_aktif, 403);

        $validated = $request->validate([
            'file_jawaban' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,zip,jpg,jpeg,png', 'max:5120'],
        ], [
            'file_jawaban.required' => 'File jawaban wajib diunggah.',
            'file_jawaban.file' => 'Jawaban harus berupa file.',
            'file_jawaban.mimes' => 'Format file jawaban tidak didukung.',
            'file_jawaban.max' => 'Ukuran file maksimal 5 MB.',
        ]);

        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->first();

        abort_if($submission?->nilai !== null, 422, 'Tugas ini sudah dinilai dan tidak dapat diubah.');

        if ($submission?->file_jawaban) {
            Storage::disk('public')->delete($submission->file_jawaban);
        }

        $path = $request->file('file_jawaban')->store('submissions', 'public');
        $uploadedFile = $request->file('file_jawaban');
        $now = Carbon::now();

        $submission = AssignmentSubmission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'student_id' => $student->id],
            [
                'tahun_ajaran_id' => $assignment->tahun_ajaran_id ?: $academicYears->activeId(),
                'semester_id' => $assignment->semester_id ?: $academicYears->activeSemesterId(),
                'file_jawaban' => $path,
                'original_file_name' => $uploadedFile->getClientOriginalName(),
                'file_mime' => $uploadedFile->getClientMimeType(),
                'file_size' => $uploadedFile->getSize(),
                'tanggal_kumpul' => $now,
                'status' => $now->greaterThan($assignment->deadline) ? 'terlambat' : 'terkumpul',
            ]
        );

        if ($assignment->teacher?->user) {
            $notifications->notifyUser(
                $assignment->teacher->user,
                'Pengumpulan tugas baru',
                $student->nama.' mengumpulkan '.$assignment->judul,
                route('teacher.assignments.show', $assignment, false),
                'submission',
                AssignmentSubmission::class,
                $submission->id
            );
        }

        return redirect()->route('student.assignments.show', $assignment)->with('success', 'Jawaban tugas berhasil dikumpulkan.');
    }
}
