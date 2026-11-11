<?php

use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\AcademicArchiveController as AdminAcademicArchiveController;
use App\Http\Controllers\Admin\AcademicSettingController as AdminAcademicSettingController;
use App\Http\Controllers\Admin\AccessController as AdminAccessController;
use App\Http\Controllers\Admin\AlumniController as AdminAlumniController;
use App\Http\Controllers\Admin\ClassController as AdminClassController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GradeWeightController as AdminGradeWeightController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SubjectController as AdminSubjectController;
use App\Http\Controllers\Admin\TeacherSubjectController as AdminTeacherSubjectController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AnnouncementRedirectController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Homeroom\DashboardController as HomeroomDashboardController;
use App\Http\Controllers\Student\AnnouncementController as StudentAnnouncementController;
use App\Http\Controllers\Student\AnalyticsController as StudentAnalyticsController;
use App\Http\Controllers\Student\AssignmentController as StudentAssignmentController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\GradeController as StudentGradeController;
use App\Http\Controllers\Student\MaterialController as StudentMaterialController;
use App\Http\Controllers\Teacher\AnnouncementController as TeacherAnnouncementController;
use App\Http\Controllers\Teacher\AnalyticsController as TeacherAnalyticsController;
use App\Http\Controllers\Teacher\AssignmentController as TeacherAssignmentController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\GradeController as TeacherGradeController;
use App\Http\Controllers\Teacher\GradeWeightController as TeacherGradeWeightController;
use App\Http\Controllers\Teacher\MaterialController as TeacherMaterialController;
use App\Http\Controllers\Teacher\TeachingArchiveController as TeacherTeachingArchiveController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/dashboard', DashboardRedirectController::class)
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifikasi/baca-semua', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('/notifikasi/baca-semua', [NotificationController::class, 'markAllRead']);
    Route::get('/notifikasi/{appNotification}/buka', [NotificationController::class, 'open'])->name('notifications.open');
    Route::get('/pengumuman/{announcement}/buka', AnnouncementRedirectController::class)->name('announcements.redirect');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
    Route::get('/hak-akses', [AdminAccessController::class, 'index'])->name('access.index');
    Route::put('/hak-akses/{user}', [AdminAccessController::class, 'update'])->name('access.update');
    Route::resource('pengumuman', AdminAnnouncementController::class)->parameters(['pengumuman' => 'announcement'])->names('announcements')->except(['show']);

    Route::get('/pengguna/{role?}', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/pengguna-tambah', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/pengguna', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/pengguna/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/pengguna/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/pengguna/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    Route::resource('kelas', AdminClassController::class)->parameters(['kelas' => 'schoolClass'])->names('classes');
    Route::resource('mata-pelajaran', AdminSubjectController::class)->parameters(['mata-pelajaran' => 'subject'])->names('subjects')->except(['show']);
    Route::resource('pengampu', AdminTeacherSubjectController::class)->parameters(['pengampu' => 'teacherSubject'])->names('teacher-subjects')->except(['show']);
    Route::resource('bobot-nilai', AdminGradeWeightController::class)->parameters(['bobot-nilai' => 'gradeWeight'])->names('grade-weights')->except(['show']);

    Route::get('/laporan-akademik', [AdminReportController::class, 'index'])->name('reports.index');
    Route::get('/setting-akademik', [AdminAcademicSettingController::class, 'index'])->name('academic-settings.index');
    Route::post('/setting-akademik/tahun-ajaran', [AdminAcademicSettingController::class, 'storeYear'])->name('academic-settings.years.store');
    Route::put('/setting-akademik/tahun-ajaran/{academicYear}/aktifkan', [AdminAcademicSettingController::class, 'activateYear'])->name('academic-settings.years.activate');
    Route::post('/setting-akademik/semester', [AdminAcademicSettingController::class, 'storeSemester'])->name('academic-settings.semesters.store');
    Route::put('/setting-akademik/semester/{semester}/aktifkan', [AdminAcademicSettingController::class, 'activateSemester'])->name('academic-settings.semesters.activate');
    Route::post('/setting-akademik/kenaikan-kelas', [AdminAcademicSettingController::class, 'processPromotion'])->name('academic-settings.promotions.process');
    Route::post('/setting-akademik/kelulusan/{student}', [AdminAcademicSettingController::class, 'graduateStudent'])->name('academic-settings.graduation.process');
    Route::put('/setting-akademik/siswa/{student}/status', [AdminAcademicSettingController::class, 'updateStudentStatus'])->name('academic-settings.students.status');
    Route::get('/arsip-akademik', [AdminAcademicArchiveController::class, 'index'])->name('academic-archives.index');
    Route::get('/arsip-akademik/export/{section}/{format}', [AdminAcademicArchiveController::class, 'export'])->name('academic-archives.export');
    Route::get('/alumni', [AdminAlumniController::class, 'index'])->name('alumni.index');
    Route::get('/alumni/{alumni}', [AdminAlumniController::class, 'show'])->name('alumni.show');
});

Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('teacher.')->group(function () {
    Route::get('/dashboard', TeacherDashboardController::class)->name('dashboard');
    Route::resource('pengumuman', TeacherAnnouncementController::class)->parameters(['pengumuman' => 'announcement'])->names('announcements')->except(['show']);
    Route::resource('materi', TeacherMaterialController::class)->parameters(['materi' => 'material'])->names('materials')->except(['show']);
    Route::get('/pengumpulan-tugas', [TeacherAssignmentController::class, 'submissions'])->name('assignments.submissions.index');
    Route::resource('tugas', TeacherAssignmentController::class)->parameters(['tugas' => 'assignment'])->names('assignments');
    Route::put('/tugas/{assignment}/pengumpulan/{submission}', [TeacherAssignmentController::class, 'gradeSubmission'])->name('assignments.submissions.grade');
    Route::get('/tugas/{assignment}/laporan-penilaian', [TeacherAssignmentController::class, 'gradeReport'])->name('assignments.grade-report');
    Route::get('/nilai', [TeacherGradeController::class, 'index'])->name('grades.index');
    Route::put('/nilai', [TeacherGradeController::class, 'update'])->name('grades.update');
    Route::get('/analitik-akademik', [TeacherAnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/arsip-mengajar', [TeacherTeachingArchiveController::class, 'index'])->name('teaching-archives.index');
    Route::get('/bobot-nilai', [TeacherGradeWeightController::class, 'index'])->name('grade-weights.index');
    Route::get('/bobot-nilai/{gradeWeight}/edit', [TeacherGradeWeightController::class, 'edit'])->name('grade-weights.edit');
    Route::put('/bobot-nilai/{gradeWeight}', [TeacherGradeWeightController::class, 'update'])->name('grade-weights.update');
});

Route::middleware(['auth', 'role:guru,wali_kelas'])->prefix('wali-kelas')->name('homeroom.')->group(function () {
    Route::get('/dashboard', HomeroomDashboardController::class)->name('dashboard');
    Route::get('/siswa', [HomeroomDashboardController::class, 'students'])->name('students.index');
    Route::get('/nilai', [HomeroomDashboardController::class, 'grades'])->name('grades.index');
    Route::get('/pengumpulan-tugas', [HomeroomDashboardController::class, 'submissions'])->name('submissions.index');
    Route::get('/laporan', [HomeroomDashboardController::class, 'report'])->name('reports.index');
});

Route::middleware(['auth', 'role:siswa'])->prefix('siswa')->name('student.')->group(function () {
    Route::get('/dashboard', StudentDashboardController::class)->name('dashboard');
    Route::get('/pengumuman', [StudentAnnouncementController::class, 'index'])->name('announcements.index');
    Route::resource('materi', StudentMaterialController::class)->parameters(['materi' => 'material'])->names('materials')->only(['index', 'show']);
    Route::resource('tugas', StudentAssignmentController::class)->parameters(['tugas' => 'assignment'])->names('assignments')->only(['index', 'show']);
    Route::post('/tugas/{assignment}/kumpulkan', [StudentAssignmentController::class, 'submit'])->name('assignments.submit');
    Route::get('/nilai', [StudentGradeController::class, 'index'])->name('grades.index');
    Route::get('/analitik-akademik', [StudentAnalyticsController::class, 'index'])->name('analytics.index');
});

require __DIR__.'/auth.php';
