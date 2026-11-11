<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Concerns\AuthorizesTeacherAssignments;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Grade;
use App\Models\Material;
use App\Models\Student;
use App\Services\AcademicYearService;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use AuthorizesTeacherAssignments;

    public function __invoke(AcademicYearService $academicYears): View
    {
        $teacher = $this->currentTeacher();
        $pairs = $this->assignedPairs($teacher);
        $classIds = $pairs->pluck('class_id')->unique();
        $activeYearId = $academicYears->activeId();

        $gradesQuery = Grade::with(['student', 'subject', 'schoolClass'])
            ->when($activeYearId, fn ($query) => $query->where('tahun_ajaran_id', $activeYearId));
        $rataKelasQuery = Grade::query()
            ->select('class_id', 'subject_id', DB::raw('AVG(nilai_akhir) as rata'))
            ->when($activeYearId, fn ($query) => $query->where('tahun_ajaran_id', $activeYearId));

        if ($pairs->isEmpty()) {
            $gradesQuery->whereRaw('1 = 0');
            $rataKelasQuery->whereRaw('1 = 0');
        } else {
            $gradesQuery->where(function ($query) use ($pairs): void {
                foreach ($pairs as $pair) {
                    $query->orWhere(fn ($item) => $item->where('subject_id', $pair->subject_id)->where('class_id', $pair->class_id));
                }
            });

            $rataKelasQuery->where(function ($query) use ($pairs): void {
                foreach ($pairs as $pair) {
                    $query->orWhere(fn ($item) => $item->where('subject_id', $pair->subject_id)->where('class_id', $pair->class_id));
                }
            });
        }

        $stats = [
            'kelas' => $classIds->count(),
            'siswa' => Student::whereIn('class_id', $classIds)->where('status_siswa', 'Aktif')->count(),
            'materi' => Material::where('teacher_id', $teacher->id)
                ->when($activeYearId, fn ($query) => $query->where(fn ($item) => $item->where('tahun_ajaran_id', $activeYearId)->orWhereNull('tahun_ajaran_id')))
                ->count(),
            'tugas' => Assignment::where('teacher_id', $teacher->id)
                ->when($activeYearId, fn ($query) => $query->where(fn ($item) => $item->where('tahun_ajaran_id', $activeYearId)->orWhereNull('tahun_ajaran_id')))
                ->count(),
            'rata_nilai' => round((float) (clone $gradesQuery)->whereNotNull('nilai_akhir')->avg('nilai_akhir'), 2),
        ];

        $topStudents = (clone $gradesQuery)->whereNotNull('nilai_akhir')->orderByDesc('nilai_akhir')->limit(5)->get();
        $lowStudents = (clone $gradesQuery)->whereNotNull('nilai_akhir')->orderBy('nilai_akhir')->limit(5)->get();

        $rataKelas = $rataKelasQuery
            ->whereNotNull('nilai_akhir')
            ->with(['schoolClass', 'subject'])
            ->groupBy('class_id', 'subject_id')
            ->get();

        $rataKelasChart = $rataKelas->map(fn ($item) => [
            'label' => ($item->schoolClass->nama_kelas ?? '-').' - '.($item->subject->nama_mapel ?? '-'),
            'value' => round((float) $item->rata, 2),
        ])->values();

        $latestAnnouncements = Announcement::with('schoolClass')
            ->visibleTo(auth()->user())
            ->when($activeYearId, fn ($query) => $query->where(fn ($item) => $item->where('tahun_ajaran_id', $activeYearId)->orWhereNull('tahun_ajaran_id')))
            ->latest()
            ->limit(4)
            ->get();

        return view('teacher.dashboard', compact('teacher', 'pairs', 'stats', 'topStudents', 'lowStudents', 'rataKelas', 'rataKelasChart', 'latestAnnouncements'));
    }
}
