<?php

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('academic:sync-master-students {file? : Path file Excel master siswa} {--dry-run : Tampilkan rencana perubahan tanpa menyimpan}', function () {
    $file = $this->argument('file') ?: 'C:\\Users\\thega\\Downloads\\daftar_pd-SMP MUHAMMADIYAH IMAM SYUHODO-2025-08-27 11_09_29 (1).xlsx';

    if (! is_file($file)) {
        $this->error("File Excel tidak ditemukan: {$file}");

        return 1;
    }

    $rows = readStudentMasterRows($file);

    if ($rows->isEmpty()) {
        $this->error('Tidak ada data siswa valid pada file Excel.');

        return 1;
    }

    $duplicateNis = $rows->groupBy('nis')->filter(fn ($items) => $items->count() > 1)->keys();
    if ($duplicateNis->isNotEmpty()) {
        $this->error('NIS/NISN duplikat pada Excel: '.$duplicateNis->implode(', '));

        return 1;
    }

    $activeYear = AcademicYear::where('is_active', true)->first() ?? AcademicYear::latest('id')->first();
    $usedEmails = User::where('role', '!=', 'siswa')->pluck('email')->filter()->map(fn ($email) => strtolower($email))->flip();
    $classCache = collect();
    $created = 0;
    $updated = 0;
    $disabled = 0;
    $dryRun = $this->option('dry-run');

    DB::beginTransaction();

    try {
        User::whereIn('role', ['admin', 'guru'])->get()->each(function (User $user): void {
            $user->update([
                'password' => Hash::make('password'),
                'status_akun' => 'Aktif',
            ]);
        });

        User::where('role', 'siswa')->get(['id'])->each(function (User $user): void {
            $user->update(['email' => "sync-temp-{$user->id}@smpimamsyuhodo.sch.id"]);
        });

        $existingStudents = Student::with('user')->get();
        $studentsByNis = $existingStudents->keyBy('nis');
        $studentsByName = $existingStudents->keyBy(fn (Student $student) => normalizeStudentKey($student->nama));
        $usedStudentIds = [];

        foreach ($rows as $row) {
            $student = $studentsByNis->get($row['nis']) ?: $studentsByName->get(normalizeStudentKey($row['nama']));
            $class = classFromRombel($row['rombel'], $activeYear, $classCache);
            $email = studentEmailFor($row['nama'], $usedEmails);

            if ($student) {
                $user = $student->user ?: User::create([
                    'name' => $row['nama'],
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'role' => 'siswa',
                    'status_akun' => 'Aktif',
                ]);

                $user->update([
                    'name' => $row['nama'],
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'role' => 'siswa',
                    'status_akun' => 'Aktif',
                ]);

                $student->update([
                    'user_id' => $user->id,
                    'nis' => $row['nis'],
                    'nama' => $row['nama'],
                    'jenis_kelamin' => $row['jk'],
                    'class_id' => $class->id,
                    'tahun_ajaran_id' => $activeYear?->id,
                    'status_siswa' => 'Aktif',
                    'no_hp' => $row['hp'],
                    'alamat' => $row['alamat'],
                ]);

                $updated++;
            } else {
                $user = User::create([
                    'name' => $row['nama'],
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'role' => 'siswa',
                    'status_akun' => 'Aktif',
                ]);

                $student = Student::create([
                    'user_id' => $user->id,
                    'nis' => $row['nis'],
                    'nama' => $row['nama'],
                    'jenis_kelamin' => $row['jk'],
                    'class_id' => $class->id,
                    'tahun_ajaran_id' => $activeYear?->id,
                    'status_siswa' => 'Aktif',
                    'no_hp' => $row['hp'],
                    'alamat' => $row['alamat'],
                ]);

                $created++;
            }

            $usedStudentIds[] = $student->id;
        }

        Student::with('user')
            ->whereNotIn('id', $usedStudentIds)
            ->get()
            ->each(function (Student $student) use (&$disabled): void {
                if ($student->status_siswa !== 'Keluar') {
                    $student->update(['status_siswa' => 'Keluar']);
                    $disabled++;
                }

                $student->user?->update(['status_akun' => 'Nonaktif']);
            });

        if ($dryRun) {
            DB::rollBack();
        } else {
            DB::commit();
        }
    } catch (Throwable $exception) {
        DB::rollBack();

        throw $exception;
    }

    $message = "Master Excel memuat {$rows->count()} siswa. {$created} dibuat, {$updated} diperbarui, {$disabled} siswa lama dinonaktifkan.";
    $this->info(($this->option('dry-run') ? 'Dry run selesai. ' : '').$message);

    if ($dryRun) {
        $this->warn('Catatan: dry-run memvalidasi file dan menghitung rencana; semua perubahan sudah di-rollback.');
    }

    return 0;
})->purpose('Sinkronisasi siswa dan akun login dari file Excel Dapodik');

if (! function_exists('readStudentMasterRows')) {
    function readStudentMasterRows(string $file): \Illuminate\Support\Collection
    {
    $zip = new ZipArchive();
    $zip->open($file);

    $sharedStrings = collect();
    $sharedXml = simplexml_load_string($zip->getFromName('xl/sharedStrings.xml'));
    foreach ($sharedXml->si as $si) {
        $text = '';
        if (isset($si->t)) {
            $text .= (string) $si->t;
        }
        foreach ($si->r as $run) {
            $text .= (string) $run->t;
        }
        $sharedStrings->push($text);
    }

    $sheet = simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml'));
    $zip->close();

    return collect(iterator_to_array($sheet->sheetData->row, false))
        ->filter(fn ($row) => (int) $row['r'] >= 7)
        ->map(function ($row) use ($sharedStrings): array {
            $cells = [];
            foreach ($row->c as $cell) {
                $ref = preg_replace('/\d+/', '', (string) $cell['r']);
                $value = (string) $cell->v;
                $cells[$ref] = ((string) $cell['t'] === 's' && $value !== '')
                    ? (string) $sharedStrings[(int) $value]
                    : trim($value);
            }

            $nis = trim((string) ($cells['C'] ?? '')) ?: trim((string) ($cells['E'] ?? ''));

            return [
                'nama' => trim((string) ($cells['B'] ?? '')),
                'nis' => $nis,
                'jk' => in_array(($cells['D'] ?? ''), ['L', 'P'], true) ? $cells['D'] : 'L',
                'rombel' => trim((string) ($cells['AQ'] ?? '')),
                'alamat' => trim((string) ($cells['J'] ?? '')) ?: null,
                'hp' => trim((string) ($cells['T'] ?? '')) ?: null,
            ];
        })
        ->filter(fn (array $row) => $row['nama'] !== '' && $row['nis'] !== '' && $row['rombel'] !== '')
        ->values();
    }
}

if (! function_exists('classFromRombel')) {
    function classFromRombel(string $rombel, ?AcademicYear $activeYear, \Illuminate\Support\Collection $cache): SchoolClass
    {
    preg_match('/(\d+)\s*\/?\s*([A-Z])/i', $rombel, $matches);
    $levelNumber = $matches[1] ?? null;
    $letter = strtoupper($matches[2] ?? '');
    $level = ['7' => 'VII', '8' => 'VIII', '9' => 'IX'][$levelNumber] ?? $levelNumber;
    $name = trim("{$level} {$letter}");
    $key = "{$levelNumber}{$letter}";

    if ($cache->has($key)) {
        return $cache[$key];
    }

    $class = SchoolClass::whereIn('nama_kelas', [$name, "{$levelNumber}{$letter}", "{$levelNumber}/{$letter}", "Kelas {$levelNumber}/{$letter}"])->first()
        ?: SchoolClass::create([
            'nama_kelas' => $name,
            'tingkat' => $level,
            'tahun_ajaran_id' => $activeYear?->id,
            'is_archived' => false,
        ]);

    $cache[$key] = $class;

    return $class;
    }
}

if (! function_exists('studentEmailFor')) {
    function studentEmailFor(string $name, \Illuminate\Support\Collection $usedEmails): string
    {
    $firstName = Str::of($name)->lower()->ascii()->replaceMatches('/[^a-z0-9\s]/', '')->squish()->explode(' ')->first() ?: 'siswa';
    $base = "{$firstName}@smpimamsyuhodo.sch.id";
    $email = $base;
    $counter = 2;

    while ($usedEmails->has($email)) {
        $email = "{$firstName}{$counter}@smpimamsyuhodo.sch.id";
        $counter++;
    }

    $usedEmails[$email] = true;

    return $email;
    }
}

if (! function_exists('normalizeStudentKey')) {
    function normalizeStudentKey(string $name): string
    {
    return Str::of($name)->lower()->ascii()->replaceMatches('/[^a-z0-9]/', '')->toString();
    }
}
