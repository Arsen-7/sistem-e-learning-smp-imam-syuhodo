<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Additive migration: existing academic records remain intact and nullable. */
    public function up(): void
    {
        foreach (['teacher_subjects', 'materials', 'assignments', 'grade_weights', 'academic_analytics', 'assignment_submissions'] as $tableName) {
            if (! Schema::hasColumn($tableName, 'semester_id')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->foreignId('semester_id')->nullable()->constrained('semesters')->nullOnDelete();
                });
            }
        }

        if (! Schema::hasColumn('assignment_submissions', 'tahun_ajaran_id')) {
            Schema::table('assignment_submissions', function (Blueprint $table): void {
                $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajaran')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (['teacher_subjects', 'materials', 'assignments', 'grade_weights', 'academic_analytics', 'assignment_submissions'] as $tableName) {
            if (Schema::hasColumn($tableName, 'semester_id')) {
                Schema::table($tableName, fn (Blueprint $table) => $table->dropConstrainedForeignId('semester_id'));
            }
        }

        if (Schema::hasColumn('assignment_submissions', 'tahun_ajaran_id')) {
            Schema::table('assignment_submissions', fn (Blueprint $table) => $table->dropConstrainedForeignId('tahun_ajaran_id'));
        }
    }
};
