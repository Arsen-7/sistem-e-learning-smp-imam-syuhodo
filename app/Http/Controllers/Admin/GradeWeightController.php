<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\GradeWeight;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Services\AcademicService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GradeWeightController extends Controller
{
    public function index(): View
    {
        $gradeWeights = GradeWeight::with(['subject', 'schoolClass'])->latest()->paginate(10);

        return view('admin.grade-weights.index', compact('gradeWeights'));
    }

    public function create(): View
    {
        return view('admin.grade-weights.create', $this->formData());
    }

    public function store(Request $request, AcademicService $service): RedirectResponse
    {
        $validated = $request->validate($this->rules(), $this->messages());
        $this->pastikanTotalBobot100($validated);

        $weight = GradeWeight::create($validated);
        $this->hitungUlangNilai($weight, $service);

        return redirect()->route('admin.grade-weights.index')->with('success', 'Bobot nilai berhasil ditambahkan.');
    }

    public function edit(GradeWeight $gradeWeight): View
    {
        return view('admin.grade-weights.edit', array_merge($this->formData(), compact('gradeWeight')));
    }

    public function update(Request $request, GradeWeight $gradeWeight, AcademicService $service): RedirectResponse
    {
        $validated = $request->validate($this->rules($gradeWeight), $this->messages());
        $this->pastikanTotalBobot100($validated);

        $gradeWeight->update($validated);
        $this->hitungUlangNilai($gradeWeight->fresh(), $service);

        return redirect()->route('admin.grade-weights.index')->with('success', 'Bobot nilai berhasil diperbarui dan nilai akhir dihitung ulang.');
    }

    public function destroy(GradeWeight $gradeWeight): RedirectResponse
    {
        $gradeWeight->delete();

        return redirect()->route('admin.grade-weights.index')->with('success', 'Bobot nilai berhasil dihapus.');
    }

    private function formData(): array
    {
        return [
            'subjects' => Subject::orderBy('nama_mapel')->get(),
            'classes' => SchoolClass::ordered()->get(),
        ];
    }

    private function rules(?GradeWeight $gradeWeight = null): array
    {
        return [
            'subject_id' => ['required', 'exists:subjects,id'],
            'class_id' => [
                'required',
                'exists:classes,id',
                Rule::unique('grade_weights')->where(fn ($query) => $query->where('subject_id', request('subject_id')))->ignore($gradeWeight?->id),
            ],
            'bobot_tugas' => ['required', 'numeric', 'min:0', 'max:100'],
            'bobot_kuis' => ['required', 'numeric', 'min:0', 'max:100'],
            'bobot_uts' => ['required', 'numeric', 'min:0', 'max:100'],
            'bobot_uas' => ['required', 'numeric', 'min:0', 'max:100'],
            'kkm' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }

    private function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'numeric' => ':attribute harus berupa angka.',
            'min' => ':attribute minimal :min.',
            'max' => ':attribute maksimal :max.',
            'exists' => ':attribute tidak ditemukan.',
            'unique' => 'Bobot untuk kelas dan mata pelajaran ini sudah ada.',
        ];
    }

    private function pastikanTotalBobot100(array $data): void
    {
        $total = (float) $data['bobot_tugas'] + (float) $data['bobot_kuis'] + (float) $data['bobot_uts'] + (float) $data['bobot_uas'];

        if (abs($total - 100) >= 0.001) {
            back()->withErrors(['bobot_tugas' => 'Total bobot tugas, kuis, UTS, dan UAS harus tepat 100%. Total saat ini: '.$total.'%.'])->throwResponse();
        }
    }

    private function hitungUlangNilai(GradeWeight $weight, AcademicService $service): void
    {
        Grade::where('subject_id', $weight->subject_id)
            ->where('class_id', $weight->class_id)
            ->get()
            ->each(fn (Grade $grade) => $service->hitungDanSimpan($grade, $weight));
    }
}
