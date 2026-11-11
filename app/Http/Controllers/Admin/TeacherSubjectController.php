<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GradeWeight;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubject;
use App\Models\TeachingHistory;
use App\Services\AcademicYearService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeacherSubjectController extends Controller
{
    public function index(): View
    {
        $teacherSubjects = TeacherSubject::with(['teacher', 'subject', 'schoolClass'])->latest()->paginate(10);

        return view('admin.teacher-subjects.index', compact('teacherSubjects'));
    }

    public function create(): View
    {
        return view('admin.teacher-subjects.create', $this->formData());
    }

    public function store(Request $request, AcademicYearService $academicYears): RedirectResponse
    {
        $validated = $request->validate($this->rules(), $this->messages());
        $validated['tahun_ajaran_id'] = $academicYears->activeId();
        $validated['semester_id'] = $academicYears->activeSemesterId();

        TeacherSubject::create($validated);
        $this->syncTeachingHistory($validated);
        GradeWeight::firstOrCreate(
            ['subject_id' => $validated['subject_id'], 'class_id' => $validated['class_id'], 'tahun_ajaran_id' => $validated['tahun_ajaran_id'], 'semester_id' => $validated['semester_id']],
            ['bobot_tugas' => 25, 'bobot_kuis' => 20, 'bobot_uts' => 25, 'bobot_uas' => 30, 'kkm' => 75]
        );

        return redirect()->route('admin.teacher-subjects.index')->with('success', 'Relasi guru dan mata pelajaran berhasil ditambahkan.');
    }

    public function edit(TeacherSubject $teacherSubject): View
    {
        return view('admin.teacher-subjects.edit', array_merge($this->formData(), compact('teacherSubject')));
    }

    public function update(Request $request, TeacherSubject $teacherSubject, AcademicYearService $academicYears): RedirectResponse
    {
        $validated = $request->validate($this->rules($teacherSubject), $this->messages());
        $validated['tahun_ajaran_id'] = $teacherSubject->tahun_ajaran_id ?: $academicYears->activeId();
        $validated['semester_id'] = $teacherSubject->semester_id ?: $academicYears->activeSemesterId();

        $teacherSubject->update($validated);
        $this->syncTeachingHistory($validated);
        GradeWeight::firstOrCreate(
            ['subject_id' => $validated['subject_id'], 'class_id' => $validated['class_id'], 'tahun_ajaran_id' => $validated['tahun_ajaran_id'], 'semester_id' => $validated['semester_id']],
            ['bobot_tugas' => 25, 'bobot_kuis' => 20, 'bobot_uts' => 25, 'bobot_uas' => 30, 'kkm' => 75]
        );

        return redirect()->route('admin.teacher-subjects.index')->with('success', 'Relasi guru dan mata pelajaran berhasil diperbarui.');
    }

    public function destroy(TeacherSubject $teacherSubject): RedirectResponse
    {
        $teacherSubject->delete();

        return back()->with('success', 'Relasi guru dan mata pelajaran berhasil dihapus.');
    }

    private function formData(): array
    {
        return [
            'teachers' => Teacher::orderBy('nama')->get(),
            'subjects' => Subject::orderBy('nama_mapel')->get(),
            'classes' => SchoolClass::ordered()->get(),
        ];
    }

    private function syncTeachingHistory(array $data): void
    {
        TeachingHistory::updateOrCreate(
            [
                'teacher_id' => $data['teacher_id'],
                'subject_id' => $data['subject_id'],
                'class_id' => $data['class_id'],
                'tahun_ajaran_id' => $data['tahun_ajaran_id'] ?? null,
            ],
            []
        );
    }

    private function rules(?TeacherSubject $teacherSubject = null): array
    {
        return [
            'teacher_id' => ['required', 'exists:teachers,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'class_id' => [
                'required',
                'exists:classes,id',
                Rule::unique('teacher_subjects')->where(fn ($query) => $query
                    ->where('teacher_id', request('teacher_id'))
                    ->where('subject_id', request('subject_id'))
                    ->where('tahun_ajaran_id', $teacherSubject?->tahun_ajaran_id ?: app(AcademicYearService::class)->activeId())
                    ->where('semester_id', $teacherSubject?->semester_id ?: app(AcademicYearService::class)->activeSemesterId()))
                    ->ignore($teacherSubject?->id),
            ],
        ];
    }

    private function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'exists' => ':attribute tidak ditemukan.',
            'unique' => 'Relasi guru, kelas, dan mata pelajaran sudah ada.',
        ];
    }
}
