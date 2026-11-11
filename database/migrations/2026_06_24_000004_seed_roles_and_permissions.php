<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('roles')) {
            foreach ([
                ['name' => 'admin', 'label' => 'Admin'],
                ['name' => 'guru', 'label' => 'Guru'],
                ['name' => 'wali_kelas', 'label' => 'Wali Kelas'],
                ['name' => 'siswa', 'label' => 'Siswa'],
            ] as $role) {
                DB::table('roles')->updateOrInsert(['name' => $role['name']], $role + ['created_at' => now(), 'updated_at' => now()]);
            }
        }

        if (Schema::hasTable('permissions')) {
            foreach ([
                ['name' => 'manage_users', 'label' => 'Kelola pengguna dan hak akses'],
                ['name' => 'manage_academic_settings', 'label' => 'Kelola setting akademik'],
                ['name' => 'view_academic_archive', 'label' => 'Lihat arsip akademik'],
                ['name' => 'manage_learning', 'label' => 'Kelola materi, tugas, nilai, dan pengumuman'],
                ['name' => 'view_student_learning', 'label' => 'Lihat pembelajaran siswa'],
            ] as $permission) {
                DB::table('permissions')->updateOrInsert(['name' => $permission['name']], $permission + ['created_at' => now(), 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->whereIn('name', ['manage_users', 'manage_academic_settings', 'view_academic_archive', 'manage_learning', 'view_student_learning'])->delete();
        }

        if (Schema::hasTable('roles')) {
            DB::table('roles')->whereIn('name', ['admin', 'guru', 'wali_kelas', 'siswa'])->delete();
        }
    }
};
