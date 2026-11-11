<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function teacherSubjects(): HasMany
    {
        return $this->hasMany(TeacherSubject::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'teacher_subjects')
            ->withPivot('class_id')
            ->withTimestamps();
    }

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'teacher_subjects', 'teacher_id', 'class_id')
            ->withPivot('subject_id')
            ->withTimestamps();
    }

    public function homeroomClasses(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'wali_kelas_id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function mataPelajaranDiampu(): string
    {
        $order = Subject::officialNames();

        $subjects = $this->subjects
            ->pluck('nama_mapel')
            ->unique()
            ->sortBy(function (string $subject) use ($order): int {
                $position = array_search($subject, $order, true);

                return $position === false ? PHP_INT_MAX : $position;
            })
            ->values();

        if ($subjects->isEmpty()) {
            return '-';
        }

        if ($subjects->count() === 2) {
            return $subjects->implode(' dan ');
        }

        return $subjects->implode(', ');
    }
}
