<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tahun_ajaran') || DB::table('tahun_ajaran')->exists()) {
            return;
        }

        $now = now();

        DB::table('tahun_ajaran')->insert([
            [
                'nama' => '2024/2025',
                'tanggal_mulai' => '2024-07-01',
                'tanggal_selesai' => '2025-06-30',
                'is_active' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nama' => '2025/2026',
                'tanggal_mulai' => '2025-07-01',
                'tanggal_selesai' => '2026-06-30',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('tahun_ajaran')) {
            return;
        }

        DB::table('tahun_ajaran')
            ->whereIn('nama', ['2024/2025', '2025/2026'])
            ->delete();
    }
};
