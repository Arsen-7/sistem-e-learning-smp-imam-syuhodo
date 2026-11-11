<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tahun_ajaran')) {
            return;
        }

        $activeYearId = DB::table('tahun_ajaran')->where('is_active', true)->value('id')
            ?: DB::table('tahun_ajaran')->orderByDesc('id')->value('id');

        if (! $activeYearId) {
            return;
        }

        foreach ([
            'classes',
            'students',
            'teacher_subjects',
            'grade_weights',
            'grades',
            'academic_analytics',
            'materials',
            'assignments',
            'assignment_submissions',
            'announcements',
            'subjects',
        ] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'tahun_ajaran_id')) {
                DB::table($table)->whereNull('tahun_ajaran_id')->update(['tahun_ajaran_id' => $activeYearId]);
            }
        }
    }

    public function down(): void
    {
        //
    }
};
