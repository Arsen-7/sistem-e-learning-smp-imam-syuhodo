<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'publish_at' => 'datetime',
            'expires_at' => 'datetime',
            'status_aktif' => 'boolean',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'tahun_ajaran_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status_aktif', true)
            ->where(fn (Builder $item) => $item->whereNull('publish_at')->orWhere('publish_at', '<=', now()))
            ->where(fn (Builder $item) => $item->whereNull('expires_at')->orWhere('expires_at', '>=', now()));
    }

    public function isPublishedNow(): bool
    {
        return $this->status_aktif
            && ($this->publish_at === null || $this->publish_at->lte(now()))
            && ($this->expires_at === null || $this->expires_at->gte(now()));
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where(function (Builder $item) use ($user): void {
            $item->where('audience', 'semua');

            if ($user->isGuru()) {
                $teacher = $user->teacher;
                $classIds = $teacher ? $teacher->teacherSubjects()->pluck('class_id')->unique()->all() : [];

                $item->orWhere('audience', 'guru')
                    ->orWhere('user_id', $user->id)
                    ->orWhere(fn (Builder $classQuery) => $classQuery
                        ->where('audience', 'kelas')
                        ->whereIn('class_id', $classIds));
            }

            if ($user->isSiswa()) {
                $classId = $user->student?->class_id;

                $item->orWhere('audience', 'siswa')
                    ->orWhere(fn (Builder $classQuery) => $classQuery
                        ->where('audience', 'kelas')
                        ->where('class_id', $classId));
            }
        });
    }

    public function scopeTeacherOwnedBy(Builder $query, User $user): Builder
    {
        $teacher = $user->teacher;

        if (! $teacher) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->where('user_id', $user->id)
            ->where('audience', 'kelas')
            ->whereExists(function ($subQuery) use ($teacher): void {
                $subQuery->selectRaw('1')
                    ->from('teacher_subjects')
                    ->whereColumn('teacher_subjects.class_id', 'announcements.class_id')
                    ->whereColumn('teacher_subjects.subject_id', 'announcements.subject_id')
                    ->where('teacher_subjects.teacher_id', $teacher->id)
                    ->where(function ($yearQuery): void {
                        $yearQuery
                            ->whereColumn('teacher_subjects.tahun_ajaran_id', 'announcements.tahun_ajaran_id')
                            ->orWhereNull('teacher_subjects.tahun_ajaran_id')
                            ->orWhereNull('announcements.tahun_ajaran_id');
                    });
            });
    }

    public function scopeForStudentClass(Builder $query, User $user): Builder
    {
        $student = $user->student;

        if (! $student) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->where('audience', 'kelas')
            ->where('class_id', $student->class_id)
            ->where(function (Builder $yearQuery) use ($student): void {
                $yearQuery
                    ->whereNull('tahun_ajaran_id')
                    ->orWhere('tahun_ajaran_id', $student->tahun_ajaran_id);
            });
    }

    public function audienceLabel(): string
    {
        return match ($this->audience) {
            'semua' => 'Semua Pengguna',
            'guru' => 'Guru',
            'siswa' => 'Siswa',
            'kelas' => 'Kelas '.($this->schoolClass->nama_kelas ?? '-'),
            default => 'Pengguna',
        };
    }
}
