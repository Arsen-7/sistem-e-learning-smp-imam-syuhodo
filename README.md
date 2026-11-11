# Sistem E-Learning SMP Muhammadiyah Imam Syuhodo

Project Laravel untuk:

Sistem E-Learning Berbasis Web Dengan Fitur Evaluasi Otomatis Dan Dashboard Analitik Akademik Siswa Di SMP Muhammadiyah Imam Syuhodo.

## Fitur Utama

- Autentikasi dan otorisasi role admin, guru, dan siswa.
- CRUD pengguna, kelas, mata pelajaran, relasi pengampu, dan bobot nilai.
- Manajemen materi pembelajaran dengan upload file.
- Manajemen tugas, upload jawaban siswa, dan penilaian tugas.
- Input nilai tugas, kuis, UTS, UAS, serta perhitungan nilai akhir otomatis.
- Dashboard analitik akademik menggunakan Chart.js.
- Laporan akademik dengan filter kelas, mapel, guru, dan siswa.
- Seeder akun contoh dan data awal sekolah.

## Instalasi Singkat

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Atur koneksi MySQL di `.env` sebelum menjalankan migrasi.

## Akun Contoh

Semua akun memakai password `password`.

- Admin: `admin@imamsyuhodo.sch.id`
- Guru Matematika: `guru.matematika@imamsyuhodo.sch.id`
- Guru IPA: `guru.ipa@imamsyuhodo.sch.id`
- Siswa 1: `siswa1@imamsyuhodo.sch.id`
- Siswa 2: `siswa2@imamsyuhodo.sch.id`
- Siswa 3: `siswa3@imamsyuhodo.sch.id`

Dokumentasi lengkap instalasi, penggunaan, metode Waterfall, dan skenario Black Box Testing tersedia di `docs/INSTALLASI_DAN_PENGUJIAN.md`.
