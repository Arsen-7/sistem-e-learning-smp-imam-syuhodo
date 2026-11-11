<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Student extends Model
{
    protected $guarded = [];

    public function scopeOrderedForAcademic(Builder $query, string $direction = 'asc'): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return $query
            ->orderByRaw("case (select tingkat from classes where classes.id = students.class_id limit 1) when 'VII' then 1 when 'VIII' then 2 when 'IX' then 3 when '7' then 1 when '8' then 2 when '9' then 3 else 99 end {$direction}")
            ->orderByRaw("(select nama_kelas from classes where classes.id = students.class_id limit 1) {$direction}")
            ->orderBy('students.nama', $direction);
    }

    public function scopeAcademicSearch(Builder $query, string $search): Builder
    {
        $search = mb_strtolower(trim($search));

        if ($search === '') {
            return $query;
        }

        return $query->where(function (Builder $item) use ($search): void {
            $item
                ->whereRaw('LOWER(students.nama) like ?', ["%{$search}%"])
                ->orWhereRaw('LOWER(students.nis) like ?', ["%{$search}%"]);

            if (Schema::hasColumn('students', 'nisn')) {
                $item->orWhereRaw('LOWER(COALESCE(students.nisn, \'\')) like ?', ["%{$search}%"]);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'tahun_ajaran_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function analytics(): HasMany
    {
        return $this->hasMany(AcademicAnalytics::class);
    }

    public function classHistories(): HasMany
    {
        return $this->hasMany(StudentClassHistory::class);
    }
}
