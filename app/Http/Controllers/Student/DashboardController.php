<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicAnalytics;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Grade;
use App\Models\Material;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $student = auth()->user()->student;
        abort_unless($student, 403, 'Akun siswa belum memiliki profil siswa.');

        $grades = Grade::with('subject')
            ->where('student_id', $student->id)
            ->whereNotNull('nilai_akhir')
            ->orderBy('subject_id')
            ->get();

        $stats = [
            'materi' => Material::where('class_id', $student->class_id)->count(),
            'tugas' => Assignment::where('class_id', $student->class_id)->where('status_aktif', true)->count(),
            'mapel' => $grades->count(),
            'rata_nilai' => round((float) $grades->avg('nilai_akhir'), 2),
        ];

        $analytics = AcademicAnalytics::where('student_id', $student->id)->get()->keyBy('subject_id');
        $studentDashboardChart = $grades->map(fn ($grade) => [
            'label' => $grade->subject->nama_mapel ?? '-',
            'nilai' => round((float) $grade->nilai_akhir, 2),
            'rata' => round((float) ($analytics->get($grade->subject_id)?->rata_rata_kelas ?? 0), 2),
        ])->values();

        $latestAnnouncements = Announcement::with(['author', 'schoolClass'])
            ->published()
            ->visibleTo(auth()->user())
            ->latest()
            ->limit(4)
            ->get();

        $latestNotifications = auth()->user()
            ->appNotifications()
            ->latest()
            ->limit(4)
            ->get();

        return view('student.dashboard', compact('student', 'grades', 'stats', 'analytics', 'studentDashboardChart', 'latestAnnouncements', 'latestNotifications'));
    }
}
