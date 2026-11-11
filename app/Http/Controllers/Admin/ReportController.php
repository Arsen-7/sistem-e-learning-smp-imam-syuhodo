<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\AcademicYearService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request, AcademicYearService $academicYears): View
    {
        $activeYearId = $academicYears->activeId();

        $grades = Grade::with(['student', 'subject', 'schoolClass'])
            ->when($activeYearId, fn ($query) => $query->where('tahun_ajaran_id', $activeYearId))
            ->when($request->class_id, fn ($query) => $query->where('class_id', $request->class_id))
            ->when($request->subject_id, fn ($query) => $query->where('subject_id', $request->subject_id))
            ->when($request->student_id, fn ($query) => $query->where('student_id', $request->student_id))
            ->orderBy('class_id')
            ->orderBy('subject_id')
            ->paginate(15)
            ->withQueryString();

        $assignments = Assignment::withCount('submissions')
            ->with(['teacher', 'subject', 'schoolClass'])
            ->when($activeYearId, fn ($query) => $query->where(fn ($item) => $item->where('tahun_ajaran_id', $activeYearId)->orWhereNull('tahun_ajaran_id')))
            ->when($request->class_id, fn ($query) => $query->where('class_id', $request->class_id))
            ->when($request->subject_id, fn ($query) => $query->where('subject_id', $request->subject_id))
            ->when($request->teacher_id, fn ($query) => $query->where('teacher_id', $request->teacher_id))
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.reports.index', [
            'grades' => $grades,
            'assignments' => $assignments,
            'classes' => SchoolClass::where('is_archived', false)
                ->when($activeYearId, fn ($query) => $query->where(fn ($item) => $item->where('tahun_ajaran_id', $activeYearId)->orWhereNull('tahun_ajaran_id')))
                ->whereHas('students', fn ($query) => $query->where('status_siswa', 'Aktif'))
                ->ordered()
                ->get(),
            'subjects' => Subject::when($activeYearId, fn ($query) => $query->where(fn ($item) => $item->where('tahun_ajaran_id', $activeYearId)->orWhereNull('tahun_ajaran_id')))->orderBy('nama_mapel')->get(),
            'teachers' => Teacher::orderBy('nama')->get(),
            'students' => Student::where('status_siswa', 'Aktif')->orderBy('nama')->get(),
        ]);
    }
}
