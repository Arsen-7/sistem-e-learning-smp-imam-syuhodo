<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Database\Eloquent\Builder;

class AcademicYearService
{
    public function active(): ?AcademicYear
    {
        return AcademicYear::active()->first();
    }

    public function activeId(): ?int
    {
        return $this->active()?->id;
    }

    public function activeSemester(): ?Semester
    {
        $yearId = $this->activeId();

        return Semester::where('is_active', true)
            ->when($yearId, fn (Builder $query) => $query->where('tahun_ajaran_id', $yearId))
            ->first();
    }

    public function activeSemesterId(): ?int
    {
        return $this->activeSemester()?->id;
    }

    public function applyActiveYear(Builder $query, string $column = 'tahun_ajaran_id'): Builder
    {
        $activeId = $this->activeId();

        if (! $activeId) {
            return $query;
        }

        return $query->where(function (Builder $item) use ($column, $activeId): void {
            $item->where($column, $activeId)->orWhereNull($column);
        });
    }
}
