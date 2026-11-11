<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Services\AcademicYearService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Http\Response;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request, ?string $role = null): View|Response
    {
        abort_unless($role === null || in_array($role, ['admin', 'guru', 'wali_kelas', 'siswa'], true), 404);

        $filters = [
            'tahun_ajaran_id' => $request->integer('tahun_ajaran_id') ?: null,
            'class_id' => $request->integer('class_id') ?: null,
            'tingkat' => trim((string) $request->query('tingkat', '')),
            'status_siswa' => trim((string) $request->query('status_siswa', '')),
            'jenis_kelamin' => trim((string) $request->query('jenis_kelamin', '')),
            'search' => trim((string) $request->query('search', '')),
            'sort' => in_array($request->query('sort'), ['asc', 'desc'], true) ? $request->query('sort') : 'asc',
        ];

        $usersQuery = User::with(['teacher.subjects', 'student.schoolClass'])
            ->when($role, fn ($query) => $query->where('role', $role));

        if (in_array($role, ['guru', 'wali_kelas'], true)) {
            $usersQuery
                ->join('teachers', 'teachers.user_id', '=', 'users.id')
                ->select('users.*')
                ->orderBy('teachers.nip');
        } elseif ($role === 'siswa') {
            $usersQuery = $this->studentUsersQuery($filters);
        } else {
            $usersQuery->latest();
        }

        if ($role === 'siswa' && in_array($request->query('export'), ['excel', 'pdf'], true)) {
            return $this->exportStudents($usersQuery->get(), $request->query('export'));
        }

        $users = $usersQuery->paginate(15)->withQueryString();
        $classes = SchoolClass::ordered()->get();
        $academicYears = AcademicYear::latest('id')->get(['id', 'nama']);

        return view('admin.users.index', compact('users', 'role', 'filters', 'classes', 'academicYears'));
    }

    public function create(Request $request): View
    {
        $role = $request->query('role', 'siswa');
        $classes = SchoolClass::ordered()->get();

        return view('admin.users.create', compact('role', 'classes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules(), $this->messages());

        DB::transaction(function () use ($validated): void {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'status_akun' => $validated['status_akun'] ?? 'Aktif',
            ]);

            $this->simpanProfil($user, $validated);
        });

        return redirect()->route('admin.users.index', ['role' => $validated['role']])
            ->with('success', 'Data pengguna berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        $user->load(['teacher', 'student']);
        $classes = SchoolClass::ordered()->get();

        return view('admin.users.edit', compact('user', 'classes'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate($this->rules($user), $this->messages());

        DB::transaction(function () use ($user, $validated): void {
            $payload = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'status_akun' => $validated['status_akun'] ?? ($user->status_akun ?? 'Aktif'),
            ];

            if (! empty($validated['password'])) {
                $payload['password'] = Hash::make($validated['password']);
            }

            $user->update($payload);
            $this->simpanProfil($user->fresh(), $validated);
        });

        return redirect()->route('admin.users.index', ['role' => $validated['role']])
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if(auth()->id() === $user->id, 422, 'Akun yang sedang digunakan tidak dapat dihapus.');

        $role = $user->role;
        $user->delete();

        return redirect()->route('admin.users.index', ['role' => $role])
            ->with('success', 'Data pengguna berhasil dihapus.');
    }

    private function rules(?User $user = null): array
    {
        $teacherId = $user?->teacher?->id;
        $studentId = $user?->student?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user?->id)],
            'role' => ['required', Rule::in(['admin', 'guru', 'wali_kelas', 'siswa'])],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'nip' => ['nullable', 'required_if:role,guru,wali_kelas', 'string', 'max:50', Rule::unique('teachers')->ignore($teacherId)],
            'gelar' => ['nullable', 'string', 'max:100'],
            'nis' => ['nullable', 'required_if:role,siswa', 'string', 'max:50', Rule::unique('students')->ignore($studentId)],
            'jenis_kelamin' => ['nullable', 'required_unless:role,admin', Rule::in(['L', 'P'])],
            'class_id' => ['nullable', 'required_if:role,siswa', 'exists:classes,id'],
            'status_siswa' => ['nullable', Rule::in(['Aktif', 'Pindah', 'Keluar', 'Lulus/Alumni'])],
            'status_akun' => ['nullable', Rule::in(['Aktif', 'Nonaktif'])],
            'no_hp' => ['nullable', 'string', 'max:30'],
            'alamat' => ['nullable', 'string'],
        ];
    }

    private function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'required_if' => ':attribute wajib diisi untuk role yang dipilih.',
            'required_unless' => ':attribute wajib diisi untuk role guru atau siswa.',
            'email' => 'Format email tidak valid.',
            'unique' => ':attribute sudah digunakan.',
            'confirmed' => 'Konfirmasi password tidak cocok.',
            'min' => ':attribute minimal :min karakter.',
            'exists' => ':attribute tidak ditemukan.',
            'in' => ':attribute tidak valid.',
        ];
    }

    private function simpanProfil(User $user, array $data): void
    {
        if (in_array($data['role'], ['guru', 'wali_kelas'], true)) {
            Teacher::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nip' => $data['nip'],
                    'nama' => $data['name'],
                    'gelar' => $data['gelar'] ?? null,
                    'jenis_kelamin' => $data['jenis_kelamin'],
                    'no_hp' => $data['no_hp'] ?? null,
                    'alamat' => $data['alamat'] ?? null,
                ]
            );
        }

        if ($data['role'] === 'siswa') {
            Student::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nis' => $data['nis'],
                    'nama' => $data['name'],
                    'jenis_kelamin' => $data['jenis_kelamin'],
                    'class_id' => $data['class_id'],
                    'tahun_ajaran_id' => app(AcademicYearService::class)->activeId(),
                    'status_siswa' => $data['status_siswa'] ?? 'Aktif',
                    'no_hp' => $data['no_hp'] ?? null,
                    'alamat' => $data['alamat'] ?? null,
                ]
            );
        }
    }

    private function studentUsersQuery(array $filters)
    {
        return User::query()
            ->with(['student.schoolClass', 'student.academicYear'])
            ->join('students', 'students.user_id', '=', 'users.id')
            ->leftJoin('classes', 'classes.id', '=', 'students.class_id')
            ->where('users.role', 'siswa')
            ->when($filters['tahun_ajaran_id'], fn ($query, $yearId) => $query->where('students.tahun_ajaran_id', $yearId))
            ->when($filters['class_id'], fn ($query, $classId) => $query->where('students.class_id', $classId))
            ->when($filters['tingkat'] !== '', fn ($query) => $query->where('classes.tingkat', $filters['tingkat']))
            ->when($filters['status_siswa'] !== '', fn ($query) => $query->where('students.status_siswa', $filters['status_siswa']))
            ->when($filters['jenis_kelamin'] !== '', fn ($query) => $query->where('students.jenis_kelamin', $filters['jenis_kelamin']))
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $search = mb_strtolower($filters['search']);
                $query->where(function ($item) use ($search): void {
                    $item
                        ->whereRaw('LOWER(students.nama) like ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(students.nis) like ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(users.name) like ?', ["%{$search}%"]);
                });
            })
            ->select('users.*')
            ->orderByRaw("case classes.tingkat when 'VII' then 1 when 'VIII' then 2 when 'IX' then 3 when '7' then 1 when '8' then 2 when '9' then 3 else 99 end {$filters['sort']}")
            ->orderBy('classes.nama_kelas', $filters['sort'])
            ->orderBy('students.nama', $filters['sort']);
    }

    private function exportStudents($users, string $format): Response
    {
        $rows = $users->map(function (User $user): array {
            return [
                'Nama Siswa' => $user->student->nama ?? $user->name,
                'NIS' => $user->student->nis ?? '-',
                'Kelas' => $user->student->schoolClass->nama_kelas ?? '-',
                'Tahun Ajaran' => $user->student->academicYear->nama ?? '-',
                'Jenis Kelamin' => $user->student->jenis_kelamin ?? '-',
                'Status' => $user->student->status_siswa ?? '-',
                'Email' => $user->email,
            ];
        });

        if ($format === 'pdf') {
            $htmlRows = $rows
                ->map(fn (array $row) => '<tr>'.collect($row)->map(fn ($value) => '<td>'.e((string) $value).'</td>')->implode('').'</tr>')
                ->implode('');
            $html = '<!doctype html><html><head><meta charset="utf-8"><title>Export Data Siswa</title><style>body{font-family:Arial,sans-serif}table{width:100%;border-collapse:collapse;font-size:12px}th,td{border:1px solid #ddd;padding:6px;text-align:left}th{background:#f1f5f9}</style></head><body><h1>Data Siswa</h1><table><thead><tr><th>Nama Siswa</th><th>NIS</th><th>Kelas</th><th>Tahun Ajaran</th><th>Jenis Kelamin</th><th>Status</th><th>Email</th></tr></thead><tbody>'.$htmlRows.'</tbody></table><script>window.print()</script></body></html>';

            return response($html);
        }

        $csv = "Nama Siswa,NIS,Kelas,Tahun Ajaran,Jenis Kelamin,Status,Email\r\n";
        foreach ($rows as $row) {
            $csv .= collect($row)->map(fn ($value) => '"'.str_replace('"', '""', (string) $value).'"')->implode(',')."\r\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="data-siswa.csv"',
        ]);
    }
}
