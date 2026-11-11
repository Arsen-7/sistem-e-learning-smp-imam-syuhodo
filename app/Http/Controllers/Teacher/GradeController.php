<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Concerns\AuthorizesTeacherAssignments;
use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\GradeWeight;
use App\Models\Student;
use App\Models\TeacherSubject;
use App\Services\AcademicService;
use App\Services\AcademicYearService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GradeController extends Controller
{
    use AuthorizesTeacherAssignments;

    public function index(Request $request, AcademicYearService $academicYears): View
    {
        $teacher = $this->currentTeacher();
        $pairs = $this->assignedPairs($teacher);
        $selectedPair = $this->selectedPair($pairs, $request);

        $students = collect();
        $grades = collect();
        $weight = null;

        if ($selectedPair) {
            $academicYearId = $selectedPair->tahun_ajaran_id ?: $academicYears->activeId();
            $semesterId = $request->integer('semester_id') ?: $academicYears->activeSemesterId();
            $students = Student::where('class_id', $selectedPair->class_id)->orderBy('nama')->get();
            $weight = GradeWeight::firstOrCreate(
                ['subject_id' => $selectedPair->subject_id, 'class_id' => $selectedPair->class_id, 'tahun_ajaran_id' => $academicYearId, 'semester_id' => $semesterId],
                ['bobot_tugas' => 25, 'bobot_kuis' => 20, 'bobot_uts' => 25, 'bobot_uas' => 30, 'kkm' => 75]
            );

            $grades = $students->mapWithKeys(function (Student $student) use ($selectedPair, $academicYearId) {
                $grade = Grade::firstOrCreate(
                ['student_id' => $student->id, 'subject_id' => $selectedPair->subject_id, 'class_id' => $selectedPair->class_id, 'tahun_ajaran_id' => $academicYearId, 'semester_id' => $semesterId]
                );

                return [$student->id => $grade];
            });
        }

        return view('teacher.grades.index', compact('pairs', 'selectedPair', 'students', 'grades', 'weight'));
    }

    public function update(Request $request, AcademicService $service, AcademicYearService $academicYears): RedirectResponse
    {
        $teacher = $this->currentTeacher();
        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'grades' => ['array'],
            'grades.*.nilai_tugas' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'grades.*.nilai_kuis' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'grades.*.nilai_uts' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'grades.*.nilai_uas' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ], [
            'required' => ':attribute wajib diisi.',
            'numeric' => ':attribute harus berupa angka.',
            'min' => ':attribute minimal :min.',
            'max' => ':attribute maksimal :max.',
            'exists' => ':attribute tidak ditemukan.',
        ]);

        $this->ensureTeacherHandles($teacher, (int) $validated['subject_id'], (int) $validated['class_id']);

        $academicYearId = $academicYears->activeId();
        $semesterId = $academicYears->activeSemesterId();

        $weight = GradeWeight::firstOrCreate(
            ['subject_id' => $validated['subject_id'], 'class_id' => $validated['class_id'], 'tahun_ajaran_id' => $academicYearId, 'semester_id' => $semesterId],
            ['bobot_tugas' => 25, 'bobot_kuis' => 20, 'bobot_uts' => 25, 'bobot_uas' => 30, 'kkm' => 75]
        );

        if (! $service->bobotValid($weight)) {
            return back()->withErrors(['bobot' => 'Total bobot nilai harus 100% sebelum nilai akhir dapat dihitung.'])->withInput();
        }

        $studentIds = Student::where('class_id', $validated['class_id'])->pluck('id');

        foreach (($validated['grades'] ?? []) as $studentId => $row) {
            if (! $studentIds->contains((int) $studentId)) {
                continue;
            }

            $grade = Grade::updateOrCreate(
                ['student_id' => $studentId, 'subject_id' => $validated['subject_id'], 'class_id' => $validated['class_id'], 'tahun_ajaran_id' => $academicYearId, 'semester_id' => $semesterId],
                [
                    'nilai_tugas' => $this->nilaiAtauNull($row['nilai_tugas'] ?? null),
                    'nilai_kuis' => $this->nilaiAtauNull($row['nilai_kuis'] ?? null),
                    'nilai_uts' => $this->nilaiAtauNull($row['nilai_uts'] ?? null),
                    'nilai_uas' => $this->nilaiAtauNull($row['nilai_uas'] ?? null),
                ]
            );

            $service->hitungDanSimpan($grade, $weight);
        }

        return redirect()->route('teacher.grades.index', ['subject_id' => $validated['subject_id'], 'class_id' => $validated['class_id']])
            ->with('success', 'Nilai siswa berhasil disimpan dan nilai akhir dihitung otomatis.');
    }

    private function selectedPair($pairs, Request $request): ?TeacherSubject
    {
        if ($request->subject_id && $request->class_id) {
            return $pairs->first(fn ($pair) => (int) $pair->subject_id === (int) $request->subject_id && (int) $pair->class_id === (int) $request->class_id);
        }

        return $pairs->first();
    }

    private function nilaiAtauNull(mixed $value): ?float
    {
        return $value === null || $value === '' ? null : (float) $value;
    }
}
