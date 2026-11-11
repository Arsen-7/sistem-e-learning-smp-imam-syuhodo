<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Grade;
use App\Models\Material;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\AcademicYearService;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(AcademicYearService $academicYears): View
    {
        $activeYearId = $academicYears->activeId();

        $stats = [
            'guru' => Teacher::count(),
            'siswa' => Student::where('status_siswa', 'Aktif')->count(),
            'kelas' => SchoolClass::where('is_archived', false)->whereHas('students', fn ($query) => $query->where('status_siswa', 'Aktif'))->count(),
            'mapel' => Subject::when($activeYearId, fn ($query) => $query->where(fn ($item) => $item->where('tahun_ajaran_id', $activeYearId)->orWhereNull('tahun_ajaran_id')))->count(),
            'materi' => Material::when($activeYearId, fn ($query) => $query->where(fn ($item) => $item->where('tahun_ajaran_id', $activeYearId)->orWhereNull('tahun_ajaran_id')))->count(),
            'tugas' => Assignment::when($activeYearId, fn ($query) => $query->where(fn ($item) => $item->where('tahun_ajaran_id', $activeYearId)->orWhereNull('tahun_ajaran_id')))->count(),
            'rata_nilai' => round((float) Grade::whereNotNull('nilai_akhir')->when($activeYearId, fn ($query) => $query->where('tahun_ajaran_id', $activeYearId))->avg('nilai_akhir'), 2),
        ];

        $rataKelas = Grade::query()
            ->select('class_id', DB::raw('AVG(nilai_akhir) as rata'))
            ->whereNotNull('nilai_akhir')
            ->when($activeYearId, fn ($query) => $query->where('tahun_ajaran_id', $activeYearId))
            ->with('schoolClass')
            ->groupBy('class_id')
            ->get();

        $rataKelasChart = $rataKelas->map(fn ($item) => [
            'label' => ($item->schoolClass->nama_kelas ?? 'Kelas').' - '.round((float) $item->rata, 2),
            'value' => round((float) $item->rata, 2),
        ])->values();

        $classSummaries = SchoolClass::where('is_archived', false)
            ->whereHas('students', fn ($query) => $query->where('status_siswa', 'Aktif'))
            ->withCount(['students' => fn ($query) => $query->where('status_siswa', 'Aktif')])
            ->get()
            ->groupBy('tingkat')
            ->map(fn ($items, $tingkat) => [
                'tingkat' => $tingkat,
                'jumlah_kelas' => $items->count(),
                'jumlah_siswa' => $items->sum('students_count'),
            ])
            ->sortBy(fn ($item) => ['VII' => 1, 'VIII' => 2, 'IX' => 3][$item['tingkat']] ?? 99)
            ->values();

        $aktivitasNilai = Grade::with(['student', 'subject', 'schoolClass'])
            ->when($activeYearId, fn ($query) => $query->where('tahun_ajaran_id', $activeYearId))
            ->latest()
            ->limit(6)
            ->get();

        $latestAnnouncements = Announcement::with(['author', 'schoolClass'])
            ->when($activeYearId, fn ($query) => $query->where(fn ($item) => $item->where('tahun_ajaran_id', $activeYearId)->orWhereNull('tahun_ajaran_id')))
            ->latest()
            ->limit(4)
            ->get();

        return view('admin.dashboard', compact('stats', 'rataKelas', 'rataKelasChart', 'classSummaries', 'aktivitasNilai', 'latestAnnouncements'));
    }
}
