<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\GradeWeight;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Services\AcademicYearService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GradeController extends Controller
{
    public function index(Request $request, AcademicYearService $academicYears): View
    {
        $student = auth()->user()->student;
        abort_unless($student, 403);

        $query = Grade::with(['subject', 'schoolClass', 'academicYear', 'semester'])
            ->where('student_id', $student->id);

        $years = AcademicYear::whereIn('id', (clone $query)->whereNotNull('tahun_ajaran_id')->pluck('tahun_ajaran_id')->unique())->orderByDesc('tanggal_mulai')->get();
        $yearId = $request->integer('tahun_ajaran_id') ?: $years->firstWhere('is_active', true)?->id;
        $semesters = $yearId ? Semester::where('tahun_ajaran_id', $yearId)->orderBy('nama')->get() : collect();
        $semesterId = $request->integer('semester_id') ?: null;
        $grades = $query->when($yearId, fn ($item) => $item->where('tahun_ajaran_id', $yearId))
            ->when($semesterId, fn ($item) => $item->where('semester_id', $semesterId))
            ->latest('tahun_ajaran_id')->orderBy('semester_id')->orderBy('subject_id')->get();

        $overallAverage = $grades->whereNotNull('nilai_akhir')->avg('nilai_akhir');

        $weights = GradeWeight::where('class_id', $student->class_id)->get()
            ->keyBy('subject_id');

        return view('student.grades.index', compact('student', 'grades', 'weights', 'overallAverage', 'years', 'semesters', 'yearId', 'semesterId'));
    }
}
