<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_weights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->decimal('bobot_tugas', 5, 2)->default(25);
            $table->decimal('bobot_kuis', 5, 2)->default(20);
            $table->decimal('bobot_uts', 5, 2)->default(25);
            $table->decimal('bobot_uas', 5, 2)->default(30);
            $table->decimal('kkm', 5, 2)->default(75);
            $table->timestamps();

            $table->unique(['subject_id', 'class_id']);
        });

        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->decimal('nilai_tugas', 5, 2)->nullable();
            $table->decimal('nilai_kuis', 5, 2)->nullable();
            $table->decimal('nilai_uts', 5, 2)->nullable();
            $table->decimal('nilai_uas', 5, 2)->nullable();
            $table->decimal('nilai_akhir', 5, 2)->nullable();
            $table->string('status_kelulusan')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'class_id']);
        });

        Schema::create('academic_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->decimal('rata_rata_kelas', 5, 2)->nullable();
            $table->string('tren_nilai')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_analytics');
        Schema::dropIfExists('grades');
        Schema::dropIfExists('grade_weights');
    }
};
