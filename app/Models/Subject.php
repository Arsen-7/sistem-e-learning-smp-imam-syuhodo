<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $guarded = [];

    public const OFFICIAL_SUBJECTS = [
        'BIND' => 'Bahasa Indonesia',
        'OLG' => 'Olahraga',
        'INF' => 'Informatika',
        'ALISLAM' => 'Al Islam',
        'BARAB' => 'Bahasa Arab',
        'BTA' => 'BTA',
        'BING' => 'Bahasa Inggris',
        'SBD' => 'Seni Budaya',
        'PKN' => 'PKN',
        'IPS' => 'IPS',
        'MTK' => 'Matematika',
        'IPA' => 'IPA',
        'BK' => 'BK',
        'BJW' => 'Bahasa Jawa',
    ];

    public static function officialCodes(): array
    {
        return array_keys(self::OFFICIAL_SUBJECTS);
    }

    public static function officialNames(): array
    {
        return array_values(self::OFFICIAL_SUBJECTS);
    }

    public static function officialNameForCode(?string $code): ?string
    {
        return self::OFFICIAL_SUBJECTS[$code] ?? null;
    }

    public function teacherSubjects(): HasMany
    {
        return $this->hasMany(TeacherSubject::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'tahun_ajaran_id');
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'teacher_subjects')
            ->withPivot('class_id')
            ->withTimestamps();
    }

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'teacher_subjects', 'subject_id', 'class_id')
            ->withPivot('teacher_id')
            ->withTimestamps();
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function gradeWeights(): HasMany
    {
        return $this->hasMany(GradeWeight::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }
}
