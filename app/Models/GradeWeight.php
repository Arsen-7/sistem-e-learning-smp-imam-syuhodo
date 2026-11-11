<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeWeight extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'bobot_tugas' => 'decimal:2',
            'bobot_kuis' => 'decimal:2',
            'bobot_uts' => 'decimal:2',
            'bobot_uas' => 'decimal:2',
            'kkm' => 'decimal:2',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'tahun_ajaran_id');
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function totalBobot(): float
    {
        return (float) $this->bobot_tugas + (float) $this->bobot_kuis + (float) $this->bobot_uts + (float) $this->bobot_uas;
    }
}
