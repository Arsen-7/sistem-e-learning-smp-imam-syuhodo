<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tahun_ajaran')) {
            Schema::create('tahun_ajaran', function (Blueprint $table) {
                $table->id();
                $table->string('nama')->unique();
                $table->date('tanggal_mulai')->nullable();
                $table->date('tanggal_selesai')->nullable();
                $table->boolean('is_active')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('semesters')) {
            Schema::create('semesters', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
                $table->string('nama');
                $table->date('tanggal_mulai')->nullable();
                $table->date('tanggal_selesai')->nullable();
                $table->boolean('is_active')->default(false);
                $table->timestamps();
            });
        }

        Schema::table('classes', function (Blueprint $table) {
            if (! Schema::hasColumn('classes', 'tahun_ajaran_id')) {
                $table->foreignId('tahun_ajaran_id')->nullable()->after('wali_kelas_id')->constrained('tahun_ajaran')->nullOnDelete();
            }

            if (! Schema::hasColumn('classes', 'is_archived')) {
                $table->boolean('is_archived')->default(false)->after('tahun_ajaran_id');
            }
        });

        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'tahun_ajaran_id')) {
                $table->foreignId('tahun_ajaran_id')->nullable()->after('class_id')->constrained('tahun_ajaran')->nullOnDelete();
            }

            if (! Schema::hasColumn('students', 'status_siswa')) {
                $table->string('status_siswa')->default('Aktif')->after('tahun_ajaran_id');
            }
        });

        foreach (['teacher_subjects', 'materials', 'assignments', 'grade_weights', 'grades', 'academic_analytics', 'announcements'] as $tableName) {
            if (! Schema::hasColumn($tableName, 'tahun_ajaran_id')) {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajaran')->nullOnDelete();
                });
            }
        }

        Schema::table('announcements', function (Blueprint $table) {
            if (! Schema::hasColumn('announcements', 'subject_id')) {
                $table->foreignId('subject_id')->nullable()->after('class_id')->constrained('subjects')->nullOnDelete();
            }
        });

        Schema::table('assignment_submissions', function (Blueprint $table) {
            if (! Schema::hasColumn('assignment_submissions', 'original_file_name')) {
                $table->string('original_file_name')->nullable()->after('file_jawaban');
            }

            if (! Schema::hasColumn('assignment_submissions', 'file_mime')) {
                $table->string('file_mime')->nullable()->after('original_file_name');
            }

            if (! Schema::hasColumn('assignment_submissions', 'file_size')) {
                $table->unsignedBigInteger('file_size')->nullable()->after('file_mime');
            }
        });

        if (! Schema::hasTable('riwayat_kelas_siswa')) {
            Schema::create('riwayat_kelas_siswa', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
                $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajaran')->nullOnDelete();
                $table->string('status_siswa')->default('Aktif');
                $table->timestamps();

                $table->unique(['student_id', 'class_id', 'tahun_ajaran_id']);
            });
        }

        if (! Schema::hasTable('riwayat_mengajar_guru')) {
            Schema::create('riwayat_mengajar_guru', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
                $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
                $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
                $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajaran')->nullOnDelete();
                $table->timestamps();

                $table->unique(['teacher_id', 'subject_id', 'class_id', 'tahun_ajaran_id'], 'unique_teacher_subject_class_year');
            });
        }

        if (! Schema::hasTable('arsip_siswa')) {
            Schema::create('arsip_siswa', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
                $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajaran')->nullOnDelete();
                $table->string('status_siswa')->default('Aktif');
                $table->json('snapshot')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('alumni')) {
            Schema::create('alumni', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->unique()->constrained('students')->cascadeOnDelete();
                $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajaran')->nullOnDelete();
                $table->date('tanggal_lulus')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('alumni');
        Schema::dropIfExists('arsip_siswa');
        Schema::dropIfExists('riwayat_mengajar_guru');
        Schema::dropIfExists('riwayat_kelas_siswa');

        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->dropColumn(['original_file_name', 'file_mime', 'file_size']);
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subject_id');
        });

        foreach (['announcements', 'academic_analytics', 'grades', 'grade_weights', 'assignments', 'materials', 'teacher_subjects'] as $tableName) {
            if ($tableName === 'academic_analytics') {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->dropUnique('analytics_unique_per_year');
                    $table->unique(['student_id', 'subject_id', 'class_id']);
                });
            }

            if ($tableName === 'grades') {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->dropUnique('grades_unique_per_year');
                    $table->unique(['student_id', 'subject_id', 'class_id']);
                });
            }

            if ($tableName === 'grade_weights') {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->dropUnique('grade_weights_unique_per_year');
                    $table->unique(['subject_id', 'class_id']);
                });
            }

            if ($tableName === 'teacher_subjects') {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->dropUnique('teacher_subjects_unique_per_year');
                    $table->unique(['teacher_id', 'subject_id', 'class_id']);
                });
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropConstrainedForeignId('tahun_ajaran_id');
            });
        }

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('status_siswa');
            $table->dropConstrainedForeignId('tahun_ajaran_id');
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn('is_archived');
            $table->dropConstrainedForeignId('tahun_ajaran_id');
        });

        Schema::dropIfExists('semesters');
        Schema::dropIfExists('tahun_ajaran');
    }
};
