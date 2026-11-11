<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Concerns\AuthorizesTeacherAssignments;
use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\TeacherSubject;
use App\Services\AcademicYearService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    use AuthorizesTeacherAssignments;

    public function index(Request $request, AcademicYearService $academicYears): View
    {
        $teacher = $this->currentTeacher();
        $pairs = $this->assignedPairs($teacher);
        $selectedPair = $this->selectedPair($pairs, $request);

        $grades = collect();
        $rataKelas = 0;
        $topStudents = collect();
        $lowStudents = collect();

        if ($selectedPair) {
            $academicYearId = $selectedPair->tahun_ajaran_id ?: $academicYears->activeId();

            $grades = Grade::with(['student', 'subject', 'schoolClass'])
                ->where('subject_id', $selectedPair->subject_id)
                ->where('class_id', $selectedPair->class_id)
                ->where('tahun_ajaran_id', $academicYearId)
                ->whereNotNull('nilai_akhir')
                ->orderByDesc('nilai_akhir')
                ->get();

            $rataKelas = round((float) $grades->avg('nilai_akhir'), 2);
            $topStudents = $grades->take(5);
            $lowStudents = $grades->sortBy('nilai_akhir')->take(5)->values();
        }

        $teacherGradesChart = $grades->map(fn ($grade) => [
            'name' => $grade->student->nama ?? '-',
            'akhir' => round((float) $grade->nilai_akhir, 2),
            'components' => [
                round((float) ($grade->nilai_tugas ?? 0), 2),
                round((float) ($grade->nilai_kuis ?? 0), 2),
                round((float) ($grade->nilai_uts ?? 0), 2),
                round((float) ($grade->nilai_uas ?? 0), 2),
            ],
        ])->values();

        return view('teacher.analytics.index', compact('pairs', 'selectedPair', 'grades', 'rataKelas', 'topStudents', 'lowStudents', 'teacherGradesChart'));
    }

    private function selectedPair($pairs, Request $request): ?TeacherSubject
    {
        if ($request->subject_id && $request->class_id) {
            return $pairs->first(fn ($pair) => (int) $pair->subject_id === (int) $request->subject_id && (int) $pair->class_id === (int) $request->class_id);
        }

        return $pairs->first();
    }
}
