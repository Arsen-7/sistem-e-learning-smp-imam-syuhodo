<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSubmission extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'tanggal_kumpul' => 'datetime',
            'nilai' => 'decimal:2',
            'file_size' => 'integer',
        ];
    }

    public function fileName(): string
    {
        return $this->original_file_name ?: basename((string) $this->file_jawaban);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'tahun_ajaran_id');
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
