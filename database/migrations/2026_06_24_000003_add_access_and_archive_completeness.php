<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('label');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('label');
                $table->timestamps();
            });
        }

        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'status_akun')) {
                $table->string('status_akun')->default('Aktif')->after('role');
            }
        });

        Schema::table('subjects', function (Blueprint $table): void {
            if (! Schema::hasColumn('subjects', 'tahun_ajaran_id')) {
                $table->foreignId('tahun_ajaran_id')->nullable()->after('kode_mapel')->constrained('tahun_ajaran')->nullOnDelete();
            }
        });

        Schema::table('assignment_submissions', function (Blueprint $table): void {
            if (! Schema::hasColumn('assignment_submissions', 'tahun_ajaran_id')) {
                $table->foreignId('tahun_ajaran_id')->nullable()->after('assignment_id')->constrained('tahun_ajaran')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('assignment_submissions', 'tahun_ajaran_id')) {
            Schema::table('assignment_submissions', fn (Blueprint $table) => $table->dropConstrainedForeignId('tahun_ajaran_id'));
        }

        if (Schema::hasColumn('subjects', 'tahun_ajaran_id')) {
            Schema::table('subjects', fn (Blueprint $table) => $table->dropConstrainedForeignId('tahun_ajaran_id'));
        }

        if (Schema::hasColumn('users', 'status_akun')) {
            Schema::table('users', fn (Blueprint $table) => $table->dropColumn('status_akun'));
        }

        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
