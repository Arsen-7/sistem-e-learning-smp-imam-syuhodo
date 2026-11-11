<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(): View
    {
        $subjects = Subject::withCount(['materials', 'assignments'])->orderBy('nama_mapel')->paginate(10);

        return view('admin.subjects.index', compact('subjects'));
    }

    public function create(): View
    {
        return view('admin.subjects.create', ['officialSubjects' => Subject::OFFICIAL_SUBJECTS]);
    }

    public function store(Request $request): RedirectResponse
    {
        Subject::create($request->validate($this->rules(), $this->messages()));

        return redirect()->route('admin.subjects.index')->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function edit(Subject $subject): View
    {
        return view('admin.subjects.edit', [
            'subject' => $subject,
            'officialSubjects' => Subject::OFFICIAL_SUBJECTS,
        ]);
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $subject->update($request->validate($this->rules($subject), $this->messages()));

        return redirect()->route('admin.subjects.index')->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $subject->delete();

        return redirect()->route('admin.subjects.index')->with('success', 'Mata pelajaran berhasil dihapus.');
    }

    private function rules(?Subject $subject = null): array
    {
        return [
            'kode_mapel' => [
                'required',
                'string',
                'max:30',
                Rule::in(Subject::officialCodes()),
                Rule::unique('subjects')->ignore($subject?->id),
            ],
            'nama_mapel' => [
                'required',
                'string',
                'max:150',
                Rule::in(Subject::officialNames()),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (Subject::officialNameForCode(request('kode_mapel')) !== $value) {
                        $fail('Nama mata pelajaran tidak sesuai dengan kode yang dipilih.');
                    }
                },
            ],
        ];
    }

    private function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'unique' => ':attribute sudah digunakan.',
            'in' => ':attribute harus menggunakan daftar mata pelajaran resmi.',
        ];
    }
}
