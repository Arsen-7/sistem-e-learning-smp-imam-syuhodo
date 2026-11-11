# Dokumentasi Sistem E-Learning SMP Muhammadiyah Imam Syuhodo

## Ringkasan

Judul sistem:

Sistem E-Learning Berbasis Web Dengan Fitur Evaluasi Otomatis Dan Dashboard Analitik Akademik Siswa Di SMP Muhammadiyah Imam Syuhodo

Teknologi:

- Bahasa pemrograman: PHP
- Framework: Laravel
- Database: MySQL
- Arsitektur: MVC Laravel
- Metode pengembangan: Waterfall
- Pengujian: Black Box Testing

## Instalasi

1. Masuk ke folder project.

```bash
cd c:\laragon\www\elearning-skripsi
```

2. Install dependency PHP dan JavaScript.

```bash
composer install
npm install
```

3. Siapkan file environment dan application key.

```bash
copy .env.example .env
php artisan key:generate
```

4. Atur koneksi MySQL di `.env`.

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=elearning_skripsi
DB_USERNAME=root
DB_PASSWORD=
```

5. Jalankan migrasi, seeder, dan storage link.

```bash
php artisan migrate --seed
php artisan storage:link
```

6. Build asset dan jalankan server.

```bash
npm run build
php artisan serve
```

## Akun Contoh

Semua akun contoh menggunakan kata sandi: `password`.

| Role | Email |
| --- | --- |
| Admin | admin@imamsyuhodo.sch.id |
| Guru Matematika | guru.matematika@imamsyuhodo.sch.id |
| Guru IPA | guru.ipa@imamsyuhodo.sch.id |
| Siswa 1 | siswa1@imamsyuhodo.sch.id |
| Siswa 2 | siswa2@imamsyuhodo.sch.id |
| Siswa 3 | siswa3@imamsyuhodo.sch.id |

## Alur Penggunaan

Admin:

- Login sebagai admin.
- Kelola pengguna admin, guru, dan siswa.
- Kelola kelas, mata pelajaran, pengampu, dan bobot nilai.
- Buka laporan akademik untuk melihat nilai dan tugas berdasarkan filter.

Guru:

- Login sebagai guru.
- Kelola materi dan tugas sesuai kelas serta mata pelajaran yang diampu.
- Buka detail tugas untuk melihat pengumpulan siswa dan memberi nilai tugas.
- Buka menu nilai untuk input nilai tugas, kuis, UTS, dan UAS.
- Buka menu analitik untuk melihat grafik nilai, rata-rata kelas, nilai tertinggi, nilai terendah, dan tren.

Siswa:

- Login sebagai siswa.
- Lihat materi dan unduh file materi.
- Lihat tugas dan upload file jawaban.
- Lihat nilai komponen, nilai akhir, bobot, status kelulusan, dan grafik perkembangan akademik.

## Rumus Evaluasi Otomatis

Nilai Akhir = (Nilai Tugas x Bobot Tugas) + (Nilai Kuis x Bobot Kuis) + (Nilai UTS x Bobot UTS) + (Nilai UAS x Bobot UAS)

Bobot disimpan dalam persen. Contoh default:

- Tugas: 25%
- Kuis: 20%
- UTS: 25%
- UAS: 30%

Sistem memvalidasi total bobot harus tepat 100%. Jika tidak, nilai akhir tidak dihitung dan sistem menampilkan pesan error.

## Metode Waterfall

1. Analisis kebutuhan: menentukan aktor, hak akses, fitur materi, tugas, nilai, evaluasi otomatis, dashboard, dan laporan.
2. Desain sistem: merancang struktur database, relasi, MVC Laravel, dan tampilan role admin, guru, serta siswa.
3. Implementasi: membuat migration, model, controller, route, middleware role, blade view, seeder, upload file, dan Chart.js.
4. Pengujian: melakukan Black Box Testing berdasarkan skenario input dan output.
5. Pemeliharaan: perbaikan bug, penyesuaian bobot nilai, serta pengembangan laporan sesuai kebutuhan sekolah.

## Skenario Black Box Testing

| No | Fitur | Skenario | Data Uji | Hasil Diharapkan |
| --- | --- | --- | --- | --- |
| 1 | Login admin | Admin login dengan akun valid | admin@imamsyuhodo.sch.id / password | Sistem membuka dashboard admin |
| 2 | Login guru | Guru login dengan akun valid | guru.matematika@imamsyuhodo.sch.id / password | Sistem membuka dashboard guru |
| 3 | Login siswa | Siswa login dengan akun valid | siswa1@imamsyuhodo.sch.id / password | Sistem membuka dashboard siswa |
| 4 | Login gagal | Pengguna login dengan password salah | email valid / password salah | Sistem menolak login dan menampilkan pesan error |
| 5 | CRUD pengguna | Admin menambah guru baru | Data nama, email, NIP, jenis kelamin | Data guru tersimpan dan tampil di daftar pengguna |
| 6 | CRUD kelas | Admin menambah kelas baru | Nama kelas VII B, tingkat VII | Data kelas tersimpan dan tampil di daftar kelas |
| 7 | CRUD mata pelajaran | Admin menambah mapel baru | Bahasa Indonesia / BIN | Data mapel tersimpan dan tampil di daftar mapel |
| 8 | Relasi pengampu | Admin menghubungkan guru, kelas, mapel | Guru Matematika, VII A, Matematika | Guru dapat mengelola data pada kelas dan mapel tersebut |
| 9 | Upload materi | Guru mengunggah materi valid | File PDF maksimal 5 MB | Materi tersimpan dan dapat diunduh siswa |
| 10 | Upload materi gagal | Guru mengunggah file tidak valid | File `.exe` | Sistem menolak file dan menampilkan validasi |
| 11 | Pembuatan tugas | Guru membuat tugas dengan deadline | Judul, deskripsi, deadline | Tugas tampil pada daftar tugas siswa sesuai kelas |
| 12 | Pengumpulan tugas | Siswa upload jawaban tugas | File PDF/DOCX valid | Status pengumpulan menjadi terkumpul atau terlambat |
| 13 | Nilai tugas | Guru memberi nilai pengumpulan | Nilai 88 | Nilai tersimpan pada pengumpulan tugas |
| 14 | Input nilai | Guru mengisi nilai komponen | Tugas 88, kuis 82, UTS 86, UAS 90 | Nilai komponen tersimpan |
| 15 | Perhitungan otomatis | Guru menyimpan nilai dengan bobot 100% | Bobot 25/20/25/30 | Sistem menghitung dan menyimpan nilai akhir |
| 16 | Validasi nilai | Guru mengisi nilai lebih dari 100 | Nilai 120 | Sistem menolak input nilai |
| 17 | Validasi bobot | Admin/guru menyimpan bobot tidak 100% | 25/20/25/20 | Sistem menolak bobot dan menampilkan pesan error |
| 18 | Dashboard guru | Guru membuka analitik akademik | Data nilai tersedia | Grafik, rata-rata kelas, nilai tertinggi, dan nilai terendah tampil |
| 19 | Dashboard siswa | Siswa membuka analitik pribadi | Data nilai tersedia | Grafik nilai pribadi dan perbandingan rata-rata kelas tampil |
| 20 | Hak akses admin | Guru membuka URL admin | `/admin/dashboard` | Sistem menolak akses dengan status 403 |
| 21 | Hak akses siswa | Siswa membuka materi kelas lain | ID materi kelas lain | Sistem menolak akses dengan status 403 |
| 22 | Hak akses guru | Guru membuka tugas guru lain | ID tugas guru lain | Sistem menolak akses dengan status 403 |
| 23 | Logout | Pengguna klik logout | Sesi aktif | Sistem mengakhiri sesi dan kembali ke login |

## Batasan Sistem

- Sistem hanya berbasis web dan berjalan melalui browser.
- Sistem hanya mencakup materi, tugas, nilai, evaluasi otomatis, laporan, dan dashboard akademik.
- Sistem tidak mencakup video conference, chat real-time, payment, atau absensi.
- Dashboard menampilkan analisis deskriptif sederhana berupa grafik nilai dan rata-rata kelas.
- Aktor hanya terdiri dari admin, guru, dan siswa.
