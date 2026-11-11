<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Announcement;
use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\GradeWeight;
use App\Models\Material;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubject;
use App\Models\User;
use App\Services\AcademicService;
use App\Services\NotificationService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $service = app(AcademicService::class);
        $notificationService = app(NotificationService::class);

        $previousYear = AcademicYear::updateOrCreate(
            ['nama' => '2024/2025'],
            ['tanggal_mulai' => '2024-07-01', 'tanggal_selesai' => '2025-06-30', 'is_active' => false]
        );
        $activeYear = AcademicYear::updateOrCreate(
            ['nama' => '2025/2026'],
            ['tanggal_mulai' => '2025-07-01', 'tanggal_selesai' => '2026-06-30', 'is_active' => true]
        );
        AcademicYear::whereKeyNot($activeYear->id)->update(['is_active' => false]);

        foreach ([$previousYear, $activeYear] as $year) {
            Semester::updateOrCreate(
                ['tahun_ajaran_id' => $year->id, 'nama' => 'Ganjil'],
                ['tanggal_mulai' => $year->tanggal_mulai, 'tanggal_selesai' => $year->tanggal_mulai?->copy()->addMonths(5), 'is_active' => $year->is_active]
            );
            Semester::updateOrCreate(
                ['tahun_ajaran_id' => $year->id, 'nama' => 'Genap'],
                ['tanggal_mulai' => $year->tanggal_mulai?->copy()->addMonths(6), 'tanggal_selesai' => $year->tanggal_selesai, 'is_active' => false]
            );
        }

        $admin = User::updateOrCreate(
            ['email' => 'admin@imamsyuhodo.sch.id'],
            [
                'name' => 'Admin Sekolah',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        $teacherRows = [
            ['key' => 'bind', 'email' => 'guru.puthut@imamsyuhodo.sch.id', 'nama' => 'Puthut Dwi Hendratmo', 'gelar' => 'S.Pd.', 'nip' => 'G-001', 'jk' => 'L', 'no_hp' => '081234567001', 'alamat' => 'Sukoharjo'],
            ['key' => 'pjok_inf', 'email' => 'guru.ridwan@imamsyuhodo.sch.id', 'nama' => 'Ridwan Raharjo Putro', 'gelar' => 'S.Pd., Gr.', 'nip' => 'G-002', 'jk' => 'L', 'no_hp' => '081234567002', 'alamat' => 'Sukoharjo'],
            ['key' => 'alislam', 'email' => 'guru.fadli@imamsyuhodo.sch.id', 'nama' => 'Muh. Fadli Hasyim', 'gelar' => 'S.Pd.', 'nip' => 'G-003', 'jk' => 'L', 'no_hp' => '081234567003', 'alamat' => 'Sukoharjo'],
            ['key' => 'barab_bta', 'email' => 'guru.fikri@imamsyuhodo.sch.id', 'nama' => 'Muh. Fikri Ath Thoriq', 'gelar' => 'S.Pd.', 'nip' => 'G-004', 'jk' => 'L', 'no_hp' => '081234567004', 'alamat' => 'Sukoharjo'],
            ['key' => 'bing_sbd', 'email' => 'guru.ambar@imamsyuhodo.sch.id', 'nama' => 'Ambar Sari', 'gelar' => 'M.Pd., Gr.', 'nip' => 'G-005', 'jk' => 'P', 'no_hp' => '081234567005', 'alamat' => 'Sukoharjo'],
            ['key' => 'pkn_ips', 'email' => 'guru.nurul@imamsyuhodo.sch.id', 'nama' => 'Nurul Nur Khasanah', 'gelar' => 'M.Pd., Gr.', 'nip' => 'G-006', 'jk' => 'P', 'no_hp' => '081234567006', 'alamat' => 'Sukoharjo'],
            ['key' => 'mtk', 'email' => 'guru.sribut@imamsyuhodo.sch.id', 'nama' => 'Sribut Nurjayanti', 'gelar' => 'S.Pd., Gr.', 'nip' => 'G-007', 'jk' => 'P', 'no_hp' => '081234567007', 'alamat' => 'Sukoharjo'],
            ['key' => 'ipa', 'email' => 'guru.shofia@imamsyuhodo.sch.id', 'nama' => 'Shofia Nur Mutmainnah', 'gelar' => 'S.Pd., Gr.', 'nip' => 'G-008', 'jk' => 'P', 'no_hp' => '081234567008', 'alamat' => 'Sukoharjo'],
            ['key' => 'bk_bjw', 'email' => 'guru.yunika@imamsyuhodo.sch.id', 'nama' => 'Yunika Putri Pratiwi', 'gelar' => 'S.Pd., Gr.', 'nip' => 'G-009', 'jk' => 'P', 'no_hp' => '081234567009', 'alamat' => 'Sukoharjo'],
        ];

        User::where('role', 'guru')
            ->whereIn('email', [
                'guru.matematika@imamsyuhodo.sch.id',
                'guru.ipa@imamsyuhodo.sch.id',
                'guru.pai@imamsyuhodo.sch.id',
                'guru.ppkn@imamsyuhodo.sch.id',
                'guru.bahasaindonesia@imamsyuhodo.sch.id',
                'guru.ips@imamsyuhodo.sch.id',
                'guru.bahasainggris@imamsyuhodo.sch.id',
                'guru.pjok@imamsyuhodo.sch.id',
                'guru.informatika@imamsyuhodo.sch.id',
                'guru.senibudaya@imamsyuhodo.sch.id',
                'guru.prakarya@imamsyuhodo.sch.id',
                'guru.bahasajawa@imamsyuhodo.sch.id',
            ])
            ->delete();

        $teachers = collect($teacherRows)->mapWithKeys(function (array $row) {
            $user = User::updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['nama'],
                    'password' => Hash::make('password'),
                    'role' => 'guru',
                ]
            );

            $teacher = Teacher::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nip' => $row['nip'],
                    'nama' => $row['nama'],
                    'gelar' => $row['gelar'],
                    'jenis_kelamin' => $row['jk'],
                    'no_hp' => $row['no_hp'],
                    'alamat' => $row['alamat'],
                ]
            );

            return [$row['key'] => $teacher];
        });

        $classRows = [
            ['key' => '7A', 'nama' => 'VII A', 'tingkat' => 'VII', 'wali' => 'bind'],
            ['key' => '7B', 'nama' => 'VII B', 'tingkat' => 'VII', 'wali' => 'pjok_inf'],
            ['key' => '7C', 'nama' => 'VII C', 'tingkat' => 'VII', 'wali' => 'alislam'],
            ['key' => '8A', 'nama' => 'VIII A', 'tingkat' => 'VIII', 'wali' => 'barab_bta'],
            ['key' => '8B', 'nama' => 'VIII B', 'tingkat' => 'VIII', 'wali' => 'bing_sbd'],
            ['key' => '8C', 'nama' => 'VIII C', 'tingkat' => 'VIII', 'wali' => 'pkn_ips'],
            ['key' => '9A', 'nama' => 'IX A', 'tingkat' => 'IX', 'wali' => 'mtk'],
            ['key' => '9B', 'nama' => 'IX B', 'tingkat' => 'IX', 'wali' => 'ipa'],
            ['key' => '9C', 'nama' => 'IX C', 'tingkat' => 'IX', 'wali' => 'bk_bjw'],
        ];

        $classes = collect($classRows)->mapWithKeys(fn (array $row) => [
            $row['key'] => SchoolClass::updateOrCreate(
                ['nama_kelas' => $row['nama']],
                ['tingkat' => $row['tingkat'], 'wali_kelas_id' => $teachers[$row['wali']]->id, 'tahun_ajaran_id' => $activeYear->id]
            ),
        ]);

        $kelas7A = $classes['7A'];
        $kelas8A = $classes['8A'];
        $kelas9A = $classes['9A'];

        $subjectTeachers = [
            'BIND' => 'bind',
            'OLG' => 'pjok_inf',
            'INF' => 'pjok_inf',
            'ALISLAM' => 'alislam',
            'BARAB' => 'barab_bta',
            'BTA' => 'barab_bta',
            'BING' => 'bing_sbd',
            'SBD' => 'bing_sbd',
            'PKN' => 'pkn_ips',
            'IPS' => 'pkn_ips',
            'MTK' => 'mtk',
            'IPA' => 'ipa',
            'BK' => 'bk_bjw',
            'BJW' => 'bk_bjw',
        ];

        $subjectRows = collect(Subject::OFFICIAL_SUBJECTS)
            ->map(fn (string $name, string $code) => [
                'kode' => $code,
                'nama' => $name,
                'teacher' => $subjectTeachers[$code],
            ])
            ->values()
            ->all();

        $subjects = collect($subjectRows)->mapWithKeys(fn (array $row) => [
            $row['kode'] => Subject::updateOrCreate(
                ['kode_mapel' => $row['kode']],
                ['nama_mapel' => $row['nama']]
            ),
        ]);

        Subject::whereNotIn('kode_mapel', Subject::officialCodes())->delete();
        TeacherSubject::whereIn('teacher_id', $teachers->pluck('id'))->delete();

        foreach ($subjectRows as $subjectRow) {
            foreach ($classes as $class) {
                $subject = $subjects[$subjectRow['kode']];
                $teacher = $teachers[$subjectRow['teacher']];

                TeacherSubject::updateOrCreate(
                    ['teacher_id' => $teacher->id, 'subject_id' => $subject->id, 'class_id' => $class->id],
                    ['tahun_ajaran_id' => $activeYear->id]
                );

                GradeWeight::updateOrCreate(
                    ['subject_id' => $subject->id, 'class_id' => $class->id, 'tahun_ajaran_id' => $activeYear->id],
                    [
                        'bobot_tugas' => 25,
                        'bobot_kuis' => 20,
                        'bobot_uts' => 25,
                        'bobot_uas' => 30,
                        'kkm' => 75,
                    ]
                );
            }
        }

        $allowedSubjectIdsByTeacher = collect($subjectRows)
            ->groupBy('teacher')
            ->map(fn ($rows) => $rows->pluck('kode')->map(fn (string $code) => $subjects[$code]->id)->values());

        foreach ($teachers as $key => $teacher) {
            $allowedSubjectIds = $allowedSubjectIdsByTeacher->get($key, collect())->all();

            Material::where('teacher_id', $teacher->id)
                ->whereNotIn('subject_id', $allowedSubjectIds)
                ->delete();

            Assignment::where('teacher_id', $teacher->id)
                ->whereNotIn('subject_id', $allowedSubjectIds)
                ->delete();
        }

        $studentRows = [
            ['email' => 'siswa1@imamsyuhodo.sch.id', 'nama' => 'Bima Pratama', 'nis' => 'S-001', 'jk' => 'L', 'class' => $kelas7A],
            ['email' => 'siswa2@imamsyuhodo.sch.id', 'nama' => 'Nadia Aulia', 'nis' => 'S-002', 'jk' => 'P', 'class' => $kelas7A],
            ['email' => 'siswa3@imamsyuhodo.sch.id', 'nama' => 'Rafi Maulana', 'nis' => 'S-003', 'jk' => 'L', 'class' => $kelas8A],
            ['email' => 'siswa4@imamsyuhodo.sch.id', 'nama' => 'Alya Zahra', 'nis' => 'S-004', 'jk' => 'P', 'class' => $kelas7A],
            ['email' => 'siswa5@imamsyuhodo.sch.id', 'nama' => 'Farel Ramadhan', 'nis' => 'S-005', 'jk' => 'L', 'class' => $kelas7A],
            ['email' => 'siswa6@imamsyuhodo.sch.id', 'nama' => 'Intan Permatasari', 'nis' => 'S-006', 'jk' => 'P', 'class' => $kelas7A],
            ['email' => 'siswa7@imamsyuhodo.sch.id', 'nama' => 'Dimas Saputra', 'nis' => 'S-007', 'jk' => 'L', 'class' => $kelas8A],
            ['email' => 'siswa8@imamsyuhodo.sch.id', 'nama' => 'Putri Lestari', 'nis' => 'S-008', 'jk' => 'P', 'class' => $kelas8A],
            ['email' => 'siswa9@imamsyuhodo.sch.id', 'nama' => 'Rizky Firmansyah', 'nis' => 'S-009', 'jk' => 'L', 'class' => $kelas8A],
            ['email' => 'siswa10@imamsyuhodo.sch.id', 'nama' => 'Salma Khairunnisa', 'nis' => 'S-010', 'jk' => 'P', 'class' => $kelas8A],
            ['email' => 'siswa11@imamsyuhodo.sch.id', 'nama' => 'Ilham Akbar', 'nis' => 'S-011', 'jk' => 'L', 'class' => $kelas9A],
            ['email' => 'siswa12@imamsyuhodo.sch.id', 'nama' => 'Kirana Safitri', 'nis' => 'S-012', 'jk' => 'P', 'class' => $kelas9A],
            ['email' => 'siswa13@imamsyuhodo.sch.id', 'nama' => 'Yoga Prakoso', 'nis' => 'S-013', 'jk' => 'L', 'class' => $kelas9A],
            ['email' => 'siswa14@imamsyuhodo.sch.id', 'nama' => 'Aditya Nugraha', 'nis' => 'S-014', 'jk' => 'L', 'class' => $classes['7B']],
            ['email' => 'siswa15@imamsyuhodo.sch.id', 'nama' => 'Hana Maharani', 'nis' => 'S-015', 'jk' => 'P', 'class' => $classes['7B']],
            ['email' => 'siswa16@imamsyuhodo.sch.id', 'nama' => 'Farhan Maulana', 'nis' => 'S-016', 'jk' => 'L', 'class' => $classes['7B']],
            ['email' => 'siswa17@imamsyuhodo.sch.id', 'nama' => 'Zahwa Nabila', 'nis' => 'S-017', 'jk' => 'P', 'class' => $classes['7B']],
            ['email' => 'siswa18@imamsyuhodo.sch.id', 'nama' => 'Arkan Wijaya', 'nis' => 'S-018', 'jk' => 'L', 'class' => $classes['7B']],
            ['email' => 'siswa19@imamsyuhodo.sch.id', 'nama' => 'Naufal Hidayat', 'nis' => 'S-019', 'jk' => 'L', 'class' => $classes['7C']],
            ['email' => 'siswa20@imamsyuhodo.sch.id', 'nama' => 'Citra Azzahra', 'nis' => 'S-020', 'jk' => 'P', 'class' => $classes['7C']],
            ['email' => 'siswa21@imamsyuhodo.sch.id', 'nama' => 'Gilang Ramadhan', 'nis' => 'S-021', 'jk' => 'L', 'class' => $classes['7C']],
            ['email' => 'siswa22@imamsyuhodo.sch.id', 'nama' => 'Safira Putri', 'nis' => 'S-022', 'jk' => 'P', 'class' => $classes['7C']],
            ['email' => 'siswa23@imamsyuhodo.sch.id', 'nama' => 'Reza Alfarizi', 'nis' => 'S-023', 'jk' => 'L', 'class' => $classes['7C']],
            ['email' => 'siswa24@imamsyuhodo.sch.id', 'nama' => 'Hafiz Al Ghifari', 'nis' => 'S-024', 'jk' => 'L', 'class' => $classes['8B']],
            ['email' => 'siswa25@imamsyuhodo.sch.id', 'nama' => 'Tiara Maharani', 'nis' => 'S-025', 'jk' => 'P', 'class' => $classes['8B']],
            ['email' => 'siswa26@imamsyuhodo.sch.id', 'nama' => 'Bagas Saputra', 'nis' => 'S-026', 'jk' => 'L', 'class' => $classes['8B']],
            ['email' => 'siswa27@imamsyuhodo.sch.id', 'nama' => 'Aisyah Fitri', 'nis' => 'S-027', 'jk' => 'P', 'class' => $classes['8B']],
            ['email' => 'siswa28@imamsyuhodo.sch.id', 'nama' => 'Kevin Pratama', 'nis' => 'S-028', 'jk' => 'L', 'class' => $classes['8B']],
            ['email' => 'siswa29@imamsyuhodo.sch.id', 'nama' => 'Nabila Rahma', 'nis' => 'S-029', 'jk' => 'P', 'class' => $classes['8C']],
            ['email' => 'siswa30@imamsyuhodo.sch.id', 'nama' => 'Arya Mahendra', 'nis' => 'S-030', 'jk' => 'L', 'class' => $classes['8C']],
            ['email' => 'siswa31@imamsyuhodo.sch.id', 'nama' => 'Fania Putri', 'nis' => 'S-031', 'jk' => 'P', 'class' => $classes['8C']],
            ['email' => 'siswa32@imamsyuhodo.sch.id', 'nama' => 'Wildan Fauzan', 'nis' => 'S-032', 'jk' => 'L', 'class' => $classes['8C']],
            ['email' => 'siswa33@imamsyuhodo.sch.id', 'nama' => 'Meisya Larasati', 'nis' => 'S-033', 'jk' => 'P', 'class' => $classes['8C']],
            ['email' => 'siswa34@imamsyuhodo.sch.id', 'nama' => 'Zahra Nuraini', 'nis' => 'S-034', 'jk' => 'P', 'class' => $kelas9A],
            ['email' => 'siswa35@imamsyuhodo.sch.id', 'nama' => 'Rafif Aditya', 'nis' => 'S-035', 'jk' => 'L', 'class' => $kelas9A],
            ['email' => 'siswa36@imamsyuhodo.sch.id', 'nama' => 'Febriansyah', 'nis' => 'S-036', 'jk' => 'L', 'class' => $classes['9B']],
            ['email' => 'siswa37@imamsyuhodo.sch.id', 'nama' => 'Laila Khumaira', 'nis' => 'S-037', 'jk' => 'P', 'class' => $classes['9B']],
            ['email' => 'siswa38@imamsyuhodo.sch.id', 'nama' => 'Akmal Rizqullah', 'nis' => 'S-038', 'jk' => 'L', 'class' => $classes['9B']],
            ['email' => 'siswa39@imamsyuhodo.sch.id', 'nama' => 'Najwa Shabrina', 'nis' => 'S-039', 'jk' => 'P', 'class' => $classes['9B']],
            ['email' => 'siswa40@imamsyuhodo.sch.id', 'nama' => 'Pandu Wicaksana', 'nis' => 'S-040', 'jk' => 'L', 'class' => $classes['9B']],
            ['email' => 'siswa41@imamsyuhodo.sch.id', 'nama' => 'Nanda Ayu', 'nis' => 'S-041', 'jk' => 'P', 'class' => $classes['9C']],
            ['email' => 'siswa42@imamsyuhodo.sch.id', 'nama' => 'Faris Setiawan', 'nis' => 'S-042', 'jk' => 'L', 'class' => $classes['9C']],
            ['email' => 'siswa43@imamsyuhodo.sch.id', 'nama' => 'Saskia Ramadhani', 'nis' => 'S-043', 'jk' => 'P', 'class' => $classes['9C']],
            ['email' => 'siswa44@imamsyuhodo.sch.id', 'nama' => 'Vino Alamsyah', 'nis' => 'S-044', 'jk' => 'L', 'class' => $classes['9C']],
            ['email' => 'siswa45@imamsyuhodo.sch.id', 'nama' => 'Melati Anggraini', 'nis' => 'S-045', 'jk' => 'P', 'class' => $classes['9C']],
        ];

        $students = collect($studentRows)->map(function (array $row) {
            $user = User::updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['nama'],
                    'password' => Hash::make('password'),
                    'role' => 'siswa',
                ]
            );

            return Student::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nis' => $row['nis'],
                    'nama' => $row['nama'],
                    'jenis_kelamin' => $row['jk'],
                    'class_id' => $row['class']->id,
                    'tahun_ajaran_id' => $activeYear->id,
                    'no_hp' => '08123456'.str_pad((string) ((int) substr($row['nis'], -3)), 4, '0', STR_PAD_LEFT),
                    'alamat' => 'Sukoharjo',
                ]
            );
        });

        $topicsByLevel = [
            'VII' => [
                'materi_mtk' => 'Bilangan Bulat dan Pecahan',
                'materi_ipa' => 'Klasifikasi Makhluk Hidup',
                'tugas' => 'Latihan Bilangan Bulat',
                'deskripsi_tugas' => 'Kerjakan 10 soal latihan bilangan bulat pada buku paket halaman 24.',
            ],
            'VIII' => [
                'materi_mtk' => 'Persamaan Linear Dua Variabel',
                'materi_ipa' => 'Sistem Pencernaan Manusia',
                'tugas' => 'Latihan Persamaan Linear',
                'deskripsi_tugas' => 'Kerjakan soal latihan persamaan linear dua variabel dan unggah jawabanmu.',
            ],
            'IX' => [
                'materi_mtk' => 'Bangun Ruang Sisi Lengkung',
                'materi_ipa' => 'Listrik Dinamis',
                'tugas' => 'Latihan Bangun Ruang',
                'deskripsi_tugas' => 'Hitung luas permukaan dan volume bangun ruang, lalu unggah jawabanmu.',
            ],
        ];

        $assignmentsByClass = collect();

        foreach ($classes as $class) {
            $topic = $topicsByLevel[$class->tingkat] ?? $topicsByLevel['VII'];

            Material::updateOrCreate(
                ['judul' => $topic['materi_mtk'], 'subject_id' => $subjects['MTK']->id, 'class_id' => $class->id],
                [
                    'teacher_id' => $teachers['mtk']->id,
                    'deskripsi' => 'Materi Matematika untuk '.$class->nama_kelas.'.',
                    'file' => null,
                    'tahun_ajaran_id' => $activeYear->id,
                ]
            );

            Material::updateOrCreate(
                ['judul' => $topic['materi_ipa'], 'subject_id' => $subjects['IPA']->id, 'class_id' => $class->id],
                [
                    'teacher_id' => $teachers['ipa']->id,
                    'deskripsi' => 'Materi IPA untuk '.$class->nama_kelas.'.',
                    'file' => null,
                    'tahun_ajaran_id' => $activeYear->id,
                ]
            );

            $assignment = Assignment::updateOrCreate(
                ['judul' => $topic['tugas'], 'subject_id' => $subjects['MTK']->id, 'class_id' => $class->id],
                [
                    'teacher_id' => $teachers['mtk']->id,
                    'deskripsi' => $topic['deskripsi_tugas'],
                    'file' => null,
                    'deadline' => Carbon::now()->addDays(7),
                    'status_aktif' => true,
                    'tahun_ajaran_id' => $activeYear->id,
                ]
            );

            $assignmentsByClass->put($class->id, $assignment);
        }

        foreach ($classes as $class) {
            $assignment = $assignmentsByClass->get($class->id);

            foreach ($students->where('class_id', $class->id)->take(2) as $student) {
                AssignmentSubmission::updateOrCreate(
                    ['assignment_id' => $assignment->id, 'student_id' => $student->id],
                    [
                        'file_jawaban' => 'submissions/contoh-jawaban-'.$assignment->id.'-'.$student->id.'.pdf',
                        'tanggal_kumpul' => Carbon::now()->subDay(),
                        'status' => 'terkumpul',
                        'tahun_ajaran_id' => $activeYear->id,
                        'nilai' => match ($student->nis) {
                            'S-001' => 88,
                            'S-002' => 76,
                            default => 78 + ((int) substr($student->nis, -3) % 12),
                        },
                        'catatan' => 'Contoh nilai awal dari seeder.',
                    ]
                );
            }
        }

        foreach ($students as $student) {
            $nisNumber = (int) substr($student->nis, -3);

            foreach ($subjectRows as $index => $subjectRow) {
                $subject = $subjects[$subjectRow['kode']];
                $subjectAdjustment = ($index % 6) - 2;
                $baseScore = 74 + ($nisNumber % 13) + $subjectAdjustment;
                $weight = GradeWeight::where('subject_id', $subject->id)->where('class_id', $student->class_id)->where('tahun_ajaran_id', $activeYear->id)->first();

                $grade = Grade::updateOrCreate(
                    ['student_id' => $student->id, 'subject_id' => $subject->id, 'class_id' => $student->class_id, 'tahun_ajaran_id' => $activeYear->id],
                    [
                        'nilai_tugas' => min(98, max(65, $baseScore + 4)),
                        'nilai_kuis' => min(98, max(65, $baseScore + 1)),
                        'nilai_uts' => min(98, max(65, $baseScore + 2)),
                        'nilai_uas' => min(98, max(65, $baseScore + 5)),
                    ]
                );

                $service->hitungDanSimpan($grade, $weight);
            }
        }

        $generalAnnouncement = Announcement::updateOrCreate(
            ['judul' => 'Selamat Datang di E-Learning Imam Syuhodo'],
            [
                'user_id' => $admin->id,
                'audience' => 'semua',
                'class_id' => null,
                'isi' => 'Gunakan portal ini untuk mengakses materi, tugas, pengumuman, dan nilai akademik.',
                'publish_at' => Carbon::now()->subDay(),
                'expires_at' => null,
                'status_aktif' => true,
                'tahun_ajaran_id' => $activeYear->id,
            ]
        );

        $classAnnouncement = Announcement::updateOrCreate(
            ['judul' => 'Persiapan Tugas Matematika Kelas VII A'],
            [
                'user_id' => $teachers['mtk']->user_id,
                'audience' => 'kelas',
                'class_id' => $kelas7A->id,
                'isi' => 'Siswa VII A diminta membaca materi bilangan bulat sebelum mengerjakan tugas latihan.',
                'publish_at' => Carbon::now(),
                'expires_at' => Carbon::now()->addWeeks(2),
                'status_aktif' => true,
                'tahun_ajaran_id' => $activeYear->id,
            ]
        );

        $notificationService->notifyAnnouncement($generalAnnouncement);
        $notificationService->notifyAnnouncement($classAnnouncement);
    }
}
