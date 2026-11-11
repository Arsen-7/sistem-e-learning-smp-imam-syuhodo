<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicAnalytics;
use App\Models\Grade;
use App\Services\AcademicYearService;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(AcademicYearService $academicYears): View
    {
        $student = auth()->user()->student;
        abort_unless($student, 403);

        $gradeQuery = Grade::with('subject')
            ->where('student_id', $student->id)
            ->whereNotNull('nilai_akhir');

        $academicYears->applyActiveYear($gradeQuery);

        $grades = $gradeQuery->orderBy('subject_id')->get();

        $analyticsQuery = AcademicAnalytics::where('student_id', $student->id);
        $academicYears->applyActiveYear($analyticsQuery);

        $analytics = $analyticsQuery->get()
            ->keyBy('subject_id');

        $studentAnalyticsChart = $grades->map(fn ($grade) => [
            'label' => $grade->subject->nama_mapel ?? '-',
            'nilai' => round((float) $grade->nilai_akhir, 2),
            'rata' => round((float) ($analytics->get($grade->subject_id)?->rata_rata_kelas ?? 0), 2),
        ])->values();

        return view('student.analytics.index', compact('student', 'grades', 'analytics', 'studentAnalyticsChart'));
    }
}
