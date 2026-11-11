<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\AcademicYearService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassController extends Controller
{
    public function index(AcademicYearService $academicYears): View
    {
        $activeYearId = $academicYears->activeId();

        $classes = SchoolClass::where('is_archived', false)
            ->when($activeYearId, fn ($query) => $query->where(fn ($item) => $item->where('tahun_ajaran_id', $activeYearId)->orWhereNull('tahun_ajaran_id')))
            ->with([
                'waliKelas',
                'academicYear',
                'students' => fn ($query) => $query->where('status_siswa', 'Aktif')->orderBy('nama'),
            ])
            ->withCount(['students' => fn ($query) => $query->where('status_siswa', 'Aktif')])
            ->ordered()
            ->paginate(10);

        $classSummaries = SchoolClass::where('is_archived', false)
            ->when($activeYearId, fn ($query) => $query->where(fn ($item) => $item->where('tahun_ajaran_id', $activeYearId)->orWhereNull('tahun_ajaran_id')))
            ->withCount(['students' => fn ($query) => $query->where('status_siswa', 'Aktif')])
            ->get()
            ->groupBy('tingkat')
            ->map(fn ($items, $tingkat) => [
                'tingkat' => $tingkat,
                'jumlah_kelas' => $items->count(),
                'jumlah_siswa' => $items->sum('students_count'),
            ])
            ->sortBy(fn ($item) => [
                'I' => 1,
                '1' => 1,
                'II' => 2,
                '2' => 2,
                'III' => 3,
                '3' => 3,
                'IV' => 4,
                '4' => 4,
                'V' => 5,
                '5' => 5,
                'VI' => 6,
                '6' => 6,
                'VII' => 7,
                '7' => 7,
                'VIII' => 8,
                '8' => 8,
                'IX' => 9,
                '9' => 9,
            ][strtoupper((string) $item['tingkat'])] ?? 99)
            ->values();

        return view('admin.classes.index', compact('classes', 'classSummaries'));
    }

    public function create(): View
    {
        $teachers = Teacher::orderBy('nama')->get();

        return view('admin.classes.create', compact('teachers'));
    }

    public function show(SchoolClass $schoolClass): View
    {
        $schoolClass->load([
            'waliKelas',
            'students' => fn ($query) => $query->orderBy('nama'),
            'students.user',
            'students.grades.subject',
            'students.submissions.assignment.subject',
            'teacherSubjects.teacher',
            'teacherSubjects.subject',
            'materials.subject',
            'assignments.subject',
        ]);

        $students = $schoolClass->students->map(function (Student $student) {
            $student->rata_nilai = round((float) $student->grades->whereNotNull('nilai_akhir')->avg('nilai_akhir'), 2);
            $student->jumlah_tugas_terkumpul = $student->submissions->count();

            return $student;
        });

        return view('admin.classes.show', compact('schoolClass', 'students'));
    }

    public function store(Request $request, AcademicYearService $academicYears): RedirectResponse
    {
        SchoolClass::create($request->validate($this->rules(), $this->messages()) + [
            'tahun_ajaran_id' => $academicYears->activeId(),
        ]);

        return redirect()->route('admin.classes.index')->with('success', 'Data kelas berhasil ditambahkan.');
    }

    public function edit(SchoolClass $schoolClass): View
    {
        $teachers = Teacher::orderBy('nama')->get();

        return view('admin.classes.edit', compact('schoolClass', 'teachers'));
    }

    public function update(Request $request, SchoolClass $schoolClass): RedirectResponse
    {
        $schoolClass->update($request->validate($this->rules(), $this->messages()));

        return redirect()->route('admin.classes.index')->with('success', 'Data kelas berhasil diperbarui.');
    }

    public function destroy(SchoolClass $schoolClass): RedirectResponse
    {
        $schoolClass->loadCount(['students', 'grades', 'assignments', 'materials', 'teacherSubjects']);

        if (
            $schoolClass->students_count > 0
            || $schoolClass->grades_count > 0
            || $schoolClass->assignments_count > 0
            || $schoolClass->materials_count > 0
            || $schoolClass->teacher_subjects_count > 0
        ) {
            $schoolClass->update(['is_archived' => true]);

            return back()->with('success', 'Kelas sudah memiliki data, sehingga dipindahkan ke arsip.');
        }

        $schoolClass->delete();

        return back()->with('success', 'Data kelas berhasil dihapus.');
    }

    private function rules(): array
    {
        return [
            'nama_kelas' => ['required', 'string', 'max:100'],
            'tingkat' => ['required', 'string', 'max:20'],
            'wali_kelas_id' => ['nullable', 'exists:teachers,id'],
        ];
    }

    private function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'exists' => ':attribute tidak ditemukan.',
        ];
    }
}
