<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Teacher;
use App\Models\TeacherSubject;
use App\Services\AcademicYearService;
use Illuminate\Support\Collection;

trait AuthorizesTeacherAssignments
{
    protected function currentTeacher(): Teacher
    {
        $teacher = auth()->user()?->teacher;
        abort_unless($teacher, 403, 'Akun guru belum memiliki profil guru.');

        return $teacher;
    }

    protected function assignedPairs(Teacher $teacher): Collection
    {
        $query = TeacherSubject::with(['subject', 'schoolClass'])
            ->where('teacher_id', $teacher->id)
            ->whereHas('schoolClass', fn ($class) => $class->where('is_archived', false));

        app(AcademicYearService::class)->applyActiveYear($query);

        return $query
            ->get()
            ->sortBy([
                fn (TeacherSubject $pair) => $this->tingkatOrder($pair->schoolClass?->tingkat),
                fn (TeacherSubject $pair) => $pair->schoolClass?->nama_kelas ?? '',
                fn (TeacherSubject $pair) => $pair->subject?->nama_mapel ?? '',
            ])
            ->values();
    }

    protected function ensureTeacherHandles(Teacher $teacher, int $subjectId, int $classId): void
    {
        $query = TeacherSubject::where('teacher_id', $teacher->id)
            ->where('subject_id', $subjectId)
            ->where('class_id', $classId);

        app(AcademicYearService::class)->applyActiveYear($query);

        $allowed = $query->exists();

        abort_unless($allowed, 403, 'Anda hanya dapat mengakses kelas dan mata pelajaran yang diampu.');
    }

    private function tingkatOrder(?string $tingkat): int
    {
        return ['VII' => 1, 'VIII' => 2, 'IX' => 3][$tingkat ?? ''] ?? 99;
    }
}
