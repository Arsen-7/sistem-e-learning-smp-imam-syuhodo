<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->replace('teacher_subjects', 'teacher_subjects_unique_per_year', 'teacher_subjects_unique_per_year_semester', ['teacher_id', 'subject_id', 'class_id', 'tahun_ajaran_id', 'semester_id']);
        $this->replace('grade_weights', 'grade_weights_unique_per_year', 'grade_weights_unique_per_year_semester', ['subject_id', 'class_id', 'tahun_ajaran_id', 'semester_id']);
        $this->replace('academic_analytics', 'analytics_unique_per_year', 'analytics_unique_per_year_semester', ['student_id', 'subject_id', 'class_id', 'tahun_ajaran_id', 'semester_id']);
    }

    public function down(): void
    {
        $this->replace('teacher_subjects', 'teacher_subjects_unique_per_year_semester', 'teacher_subjects_unique_per_year', ['teacher_id', 'subject_id', 'class_id', 'tahun_ajaran_id']);
        $this->replace('grade_weights', 'grade_weights_unique_per_year_semester', 'grade_weights_unique_per_year', ['subject_id', 'class_id', 'tahun_ajaran_id']);
        $this->replace('academic_analytics', 'analytics_unique_per_year_semester', 'analytics_unique_per_year', ['student_id', 'subject_id', 'class_id', 'tahun_ajaran_id']);
    }

    private function replace(string $table, string $old, string $new, array $columns): void
    {
        if ($this->exists($table, $old)) Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropIndex($old));
        if (! $this->exists($table, $new)) Schema::table($table, fn (Blueprint $blueprint) => $blueprint->unique($columns, $new));
    }

    private function exists(string $table, string $name): bool
    {
        return DB::getDriverName() === 'sqlite'
            ? collect(DB::select("PRAGMA index_list('{$table}')"))->contains(fn ($index) => $index->name === $name)
            : collect(DB::select("SHOW INDEX FROM `{$table}`"))->contains(fn ($index) => $index->Key_name === $name);
    }
};
