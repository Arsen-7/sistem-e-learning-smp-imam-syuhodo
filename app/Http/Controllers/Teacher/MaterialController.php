<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Concerns\AuthorizesTeacherAssignments;
use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Services\AcademicYearService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MaterialController extends Controller
{
    use AuthorizesTeacherAssignments;

    public function index(AcademicYearService $academicYears): View
    {
        $teacher = $this->currentTeacher();
        $query = Material::with(['subject', 'schoolClass'])
            ->where('teacher_id', $teacher->id);

        $academicYears->applyActiveYear($query);

        $materials = $query->latest()->paginate(10);

        return view('teacher.materials.index', compact('materials'));
    }

    public function create(): View
    {
        $teacher = $this->currentTeacher();

        return view('teacher.materials.create', ['pairs' => $this->assignedPairs($teacher)]);
    }

    public function store(Request $request, AcademicYearService $academicYears): RedirectResponse
    {
        $teacher = $this->currentTeacher();
        $validated = $request->validate($this->rules(), $this->messages());
        $this->ensureTeacherHandles($teacher, (int) $validated['subject_id'], (int) $validated['class_id']);

        if ($request->hasFile('file')) {
            $validated['file'] = $request->file('file')->store('materials', 'public');
        }

        $validated['teacher_id'] = $teacher->id;
        $validated['tahun_ajaran_id'] = $academicYears->activeId();
        $validated['semester_id'] = $academicYears->activeSemesterId();
        Material::create($validated);

        return redirect()->route('teacher.materials.index')->with('success', 'Materi pembelajaran berhasil ditambahkan.');
    }

    public function edit(Material $material): View
    {
        $teacher = $this->currentTeacher();
        abort_unless($material->teacher_id === $teacher->id, 403);

        return view('teacher.materials.edit', ['material' => $material, 'pairs' => $this->assignedPairs($teacher)]);
    }

    public function update(Request $request, Material $material): RedirectResponse
    {
        $teacher = $this->currentTeacher();
        abort_unless($material->teacher_id === $teacher->id, 403);

        $validated = $request->validate($this->rules(), $this->messages());
        $this->ensureTeacherHandles($teacher, (int) $validated['subject_id'], (int) $validated['class_id']);

        if ($request->hasFile('file')) {
            if ($material->file) {
                Storage::disk('public')->delete($material->file);
            }

            $validated['file'] = $request->file('file')->store('materials', 'public');
        }

        $material->update($validated);

        return redirect()->route('teacher.materials.index')->with('success', 'Materi pembelajaran berhasil diperbarui.');
    }

    public function destroy(Material $material): RedirectResponse
    {
        $teacher = $this->currentTeacher();
        abort_unless($material->teacher_id === $teacher->id, 403);

        if ($material->file) {
            Storage::disk('public')->delete($material->file);
        }

        $material->delete();

        return redirect()->route('teacher.materials.index')->with('success', 'Materi pembelajaran berhasil dihapus.');
    }

    private function rules(): array
    {
        return [
            'subject_id' => ['required', 'exists:subjects,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,jpg,jpeg,png', 'max:5120'],
        ];
    }

    private function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'exists' => ':attribute tidak ditemukan.',
            'file' => ':attribute harus berupa file.',
            'mimes' => 'Format file tidak didukung.',
            'max' => ':attribute maksimal 5 MB.',
        ];
    }
}
