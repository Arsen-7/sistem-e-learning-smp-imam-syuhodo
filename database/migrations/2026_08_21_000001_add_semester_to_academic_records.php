<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('roles')) {
            DB::table('roles')->updateOrInsert(
                ['name' => 'wali_kelas'],
                ['label' => 'Wali Kelas', 'created_at' => now(), 'updated_at' => now()]
            );
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE assignment_submissions MODIFY status ENUM('terkumpul', 'terlambat', 'dinilai') NOT NULL DEFAULT 'terkumpul'");
        }

        Schema::table('grades', function (Blueprint $table): void {
            if (! Schema::hasColumn('grades', 'semester_id')) {
                $table->foreignId('semester_id')->nullable()->after('tahun_ajaran_id')->constrained('semesters')->nullOnDelete();
            }
        });

        Schema::table('riwayat_kelas_siswa', function (Blueprint $table): void {
            if (! Schema::hasColumn('riwayat_kelas_siswa', 'semester_id')) {
                $table->foreignId('semester_id')->nullable()->after('tahun_ajaran_id')->constrained('semesters')->nullOnDelete();
            }
        });

        $this->dropIndexIfExists('grades', 'grades_unique_per_year');
        $this->addUniqueIfMissing('grades', 'grades_unique_per_year_semester', ['student_id', 'subject_id', 'class_id', 'tahun_ajaran_id', 'semester_id']);
    }

    public function down(): void
    {
        if (Schema::hasTable('roles')) {
            DB::table('roles')->where('name', 'wali_kelas')->delete();
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE assignment_submissions SET status = 'terkumpul' WHERE status = 'dinilai'");
            DB::statement("ALTER TABLE assignment_submissions MODIFY status ENUM('terkumpul', 'terlambat') NOT NULL DEFAULT 'terkumpul'");
        }
        $this->dropIndexIfExists('grades', 'grades_unique_per_year_semester');
        $this->addUniqueIfMissing('grades', 'grades_unique_per_year', ['student_id', 'subject_id', 'class_id', 'tahun_ajaran_id']);

        Schema::table('riwayat_kelas_siswa', function (Blueprint $table): void {
            if (Schema::hasColumn('riwayat_kelas_siswa', 'semester_id')) {
                $table->dropConstrainedForeignId('semester_id');
            }
        });

        Schema::table('grades', function (Blueprint $table): void {
            if (Schema::hasColumn('grades', 'semester_id')) {
                $table->dropConstrainedForeignId('semester_id');
            }
        });
    }

    private function addUniqueIfMissing(string $table, string $name, array $columns): void
    {
        if (! $this->indexExists($table, $name)) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->unique($columns, $name));
        }
    }

    private function dropIndexIfExists(string $table, string $name): void
    {
        if ($this->indexExists($table, $name)) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropIndex($name));
        }
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
