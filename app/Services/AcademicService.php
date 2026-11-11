<?php

namespace App\Services;

use App\Models\AcademicAnalytics;
use App\Models\Grade;
use App\Models\GradeWeight;

class AcademicService
{
    public function bobotValid(GradeWeight $weight): bool
    {
        return abs($weight->totalBobot() - 100) < 0.001;
    }

    public function hitungNilaiAkhir(Grade $grade, GradeWeight $weight): float
    {
        $components = [
            'nilai_tugas' => (float) $weight->bobot_tugas,
            'nilai_kuis' => (float) $weight->bobot_kuis,
            'nilai_uts' => (float) $weight->bobot_uts,
            'nilai_uas' => (float) $weight->bobot_uas,
        ];

        $availableWeight = collect($components)
            ->filter(fn (float $bobot, string $field) => $grade->{$field} !== null && $grade->{$field} !== '')
            ->sum();

        if ($availableWeight <= 0) {
            return 0;
        }

        $score = collect($components)
            ->sum(fn (float $bobot, string $field) => $grade->{$field} === null || $grade->{$field} === ''
                ? 0
                : ((float) $grade->{$field} * $bobot / 100));

        return round((float) $score, 2);
    }

    public function statusKelulusan(float $nilaiAkhir, GradeWeight $weight): string
    {
        return $nilaiAkhir >= (float) $weight->kkm ? 'Lulus' : 'Belum Lulus';
    }

    public function trenNilai(Grade $grade): string
    {
        $komponen = collect([
            $grade->nilai_tugas,
            $grade->nilai_kuis,
            $grade->nilai_uts,
            $grade->nilai_uas,
        ])->filter(fn ($nilai) => $nilai !== null)->map(fn ($nilai) => (float) $nilai)->values();

        if ($komponen->count() < 2) {
            return 'Data belum cukup';
        }

        $awal = $komponen->first();
        $akhir = $komponen->last();

        if ($akhir > $awal) {
            return 'Naik';
        }

        if ($akhir < $awal) {
            return 'Turun';
        }

        return 'Stabil';
    }

    public function sinkronAnalitikKelas(int $classId, int $subjectId, ?int $academicYearId = null, ?int $semesterId = null): void
    {
        $gradeQuery = Grade::where('class_id', $classId)
            ->where('subject_id', $subjectId);

        if ($academicYearId) {
            $gradeQuery->where('tahun_ajaran_id', $academicYearId);
        }
        if ($semesterId) {
            $gradeQuery->where('semester_id', $semesterId);
        }

        $rataRata = (clone $gradeQuery)
            ->whereNotNull('nilai_akhir')
            ->avg('nilai_akhir');

        $gradeQuery
            ->get()
            ->each(function (Grade $grade) use ($rataRata): void {
                AcademicAnalytics::updateOrCreate(
                    [
                        'student_id' => $grade->student_id,
                        'subject_id' => $grade->subject_id,
                        'class_id' => $grade->class_id,
                        'tahun_ajaran_id' => $grade->tahun_ajaran_id,
                        'semester_id' => $grade->semester_id,
                    ],
                    [
                        'rata_rata_kelas' => $rataRata ? round((float) $rataRata, 2) : null,
                        'tren_nilai' => $this->trenNilai($grade),
                    ]
                );
            });
    }

    public function hitungDanSimpan(Grade $grade, GradeWeight $weight): Grade
    {
        $nilaiAkhir = $this->hitungNilaiAkhir($grade, $weight);

        $grade->forceFill([
            'nilai_akhir' => $nilaiAkhir,
            'status_kelulusan' => $this->statusKelulusan($nilaiAkhir, $weight),
        ])->save();

        $this->sinkronAnalitikKelas($grade->class_id, $grade->subject_id, $grade->tahun_ajaran_id, $grade->semester_id);

        return $grade;
    }
}
