<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Concerns\AuthorizesTeacherAssignments;
use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\GradeWeight;
use App\Services\AcademicService;
use App\Services\AcademicYearService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GradeWeightController extends Controller
{
    use AuthorizesTeacherAssignments;

    public function index(AcademicYearService $academicYears): View
    {
        $teacher = $this->currentTeacher();
        $pairs = $this->assignedPairs($teacher);

        $weights = $pairs->map(function ($pair) use ($academicYears) {
            $academicYearId = $pair->tahun_ajaran_id ?: $academicYears->activeId();

            return GradeWeight::firstOrCreate(
                ['subject_id' => $pair->subject_id, 'class_id' => $pair->class_id, 'tahun_ajaran_id' => $academicYearId],
                ['bobot_tugas' => 25, 'bobot_kuis' => 20, 'bobot_uts' => 25, 'bobot_uas' => 30, 'kkm' => 75]
            )->load(['subject', 'schoolClass']);
        });

        return view('teacher.grade-weights.index', compact('weights'));
    }

    public function edit(GradeWeight $gradeWeight): View
    {
        $teacher = $this->currentTeacher();
        $this->ensureTeacherHandles($teacher, $gradeWeight->subject_id, $gradeWeight->class_id);
        $gradeWeight->load(['subject', 'schoolClass']);

        return view('teacher.grade-weights.edit', compact('gradeWeight'));
    }

    public function update(Request $request, GradeWeight $gradeWeight, AcademicService $service): RedirectResponse
    {
        $teacher = $this->currentTeacher();
        $this->ensureTeacherHandles($teacher, $gradeWeight->subject_id, $gradeWeight->class_id);

        $validated = $request->validate([
            'bobot_tugas' => ['required', 'numeric', 'min:0', 'max:100'],
            'bobot_kuis' => ['required', 'numeric', 'min:0', 'max:100'],
            'bobot_uts' => ['required', 'numeric', 'min:0', 'max:100'],
            'bobot_uas' => ['required', 'numeric', 'min:0', 'max:100'],
            'kkm' => ['required', 'numeric', 'min:0', 'max:100'],
        ], [
            'required' => ':attribute wajib diisi.',
            'numeric' => ':attribute harus berupa angka.',
            'min' => ':attribute minimal :min.',
            'max' => ':attribute maksimal :max.',
        ]);

        $total = array_sum([$validated['bobot_tugas'], $validated['bobot_kuis'], $validated['bobot_uts'], $validated['bobot_uas']]);
        if (abs($total - 100) >= 0.001) {
            return back()->withErrors(['bobot_tugas' => 'Total bobot harus tepat 100%. Total saat ini: '.$total.'%.'])->withInput();
        }

        $gradeWeight->update($validated);

        Grade::where('subject_id', $gradeWeight->subject_id)
            ->where('class_id', $gradeWeight->class_id)
            ->where('tahun_ajaran_id', $gradeWeight->tahun_ajaran_id)
            ->get()
            ->each(fn (Grade $grade) => $service->hitungDanSimpan($grade, $gradeWeight->fresh()));

        return redirect()->route('teacher.grade-weights.index')->with('success', 'Bobot nilai berhasil diperbarui.');
    }
}
