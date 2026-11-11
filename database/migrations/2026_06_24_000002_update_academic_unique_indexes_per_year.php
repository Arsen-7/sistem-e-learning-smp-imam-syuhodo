<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfMissing('teacher_subjects', 'teacher_subjects_teacher_id_index', ['teacher_id']);
        $this->dropIndexIfExists('teacher_subjects', 'teacher_subjects_teacher_id_subject_id_class_id_unique');
        $this->addUniqueIfMissing('teacher_subjects', 'teacher_subjects_unique_per_year', ['teacher_id', 'subject_id', 'class_id', 'tahun_ajaran_id']);

        $this->addIndexIfMissing('grade_weights', 'grade_weights_subject_id_index', ['subject_id']);
        $this->addIndexIfMissing('grade_weights', 'grade_weights_class_id_index', ['class_id']);
        $this->dropIndexIfExists('grade_weights', 'grade_weights_subject_id_class_id_unique');
        $this->addUniqueIfMissing('grade_weights', 'grade_weights_unique_per_year', ['subject_id', 'class_id', 'tahun_ajaran_id']);

        $this->addIndexIfMissing('grades', 'grades_student_id_index', ['student_id']);
        $this->dropIndexIfExists('grades', 'grades_student_id_subject_id_class_id_unique');
        $this->addUniqueIfMissing('grades', 'grades_unique_per_year', ['student_id', 'subject_id', 'class_id', 'tahun_ajaran_id']);

        $this->addIndexIfMissing('academic_analytics', 'academic_analytics_student_id_index', ['student_id']);
        $this->dropIndexIfExists('academic_analytics', 'academic_analytics_student_id_subject_id_class_id_unique');
        $this->addUniqueIfMissing('academic_analytics', 'analytics_unique_per_year', ['student_id', 'subject_id', 'class_id', 'tahun_ajaran_id']);
    }

    public function down(): void
    {
        $this->dropIndexIfExists('academic_analytics', 'analytics_unique_per_year');
        $this->addUniqueIfMissing('academic_analytics', 'academic_analytics_student_id_subject_id_class_id_unique', ['student_id', 'subject_id', 'class_id']);

        $this->dropIndexIfExists('grades', 'grades_unique_per_year');
        $this->addUniqueIfMissing('grades', 'grades_student_id_subject_id_class_id_unique', ['student_id', 'subject_id', 'class_id']);

        $this->dropIndexIfExists('grade_weights', 'grade_weights_unique_per_year');
        $this->addUniqueIfMissing('grade_weights', 'grade_weights_subject_id_class_id_unique', ['subject_id', 'class_id']);

        $this->dropIndexIfExists('teacher_subjects', 'teacher_subjects_unique_per_year');
        $this->addUniqueIfMissing('teacher_subjects', 'teacher_subjects_teacher_id_subject_id_class_id_unique', ['teacher_id', 'subject_id', 'class_id']);
    }

    private function addIndexIfMissing(string $table, string $name, array $columns): void
    {
        if ($this->indexExists($table, $name)) {
            return;
        }

        Schema::table($table, fn ($blueprint) => $blueprint->index($columns, $name));
    }

    private function addUniqueIfMissing(string $table, string $name, array $columns): void
    {
        if ($this->indexExists($table, $name)) {
            return;
        }

        Schema::table($table, fn ($blueprint) => $blueprint->unique($columns, $name));
    }

    private function dropIndexIfExists(string $table, string $name): void
    {
        if (! $this->indexExists($table, $name)) {
            return;
        }

        Schema::table($table, fn ($blueprint) => $blueprint->dropIndex($name));
    }

    private function indexExists(string $table, string $name): bool
    {
        if (DB::getDriverName() === 'sqlite') {
            return collect(DB::select("PRAGMA index_list('{$table}')"))
                ->contains(fn ($index) => $index->name === $name);
        }

        return collect(DB::select("SHOW INDEX FROM `{$table}`"))
            ->contains(fn ($index) => $index->Key_name === $name);
    }
};
