<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Subject;
use App\Services\AcademicYearService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaterialController extends Controller
{
    public function index(Request $request, AcademicYearService $academicYears): View
    {
        $student = auth()->user()->student;
        abort_unless($student, 403);

        $selectedSubject = $request->integer('subject_id') ?: null;

        $query = Material::with(['teacher', 'subject', 'schoolClass'])
            ->where('class_id', $student->class_id)
            ->when($selectedSubject, fn ($item) => $item->where('subject_id', $selectedSubject));

        $academicYears->applyActiveYear($query);

        $materials = $query->latest()->paginate(10)->withQueryString();

        $subjects = Subject::whereHas('materials', fn ($query) => $query->where('class_id', $student->class_id))
            ->orderBy('nama_mapel')
            ->get();

        return view('student.materials.index', compact('materials', 'subjects', 'selectedSubject'));
    }

    public function show(Material $material): View
    {
        $student = auth()->user()->student;
        abort_unless($student && $material->class_id === $student->class_id, 403);
        $material->load(['teacher', 'subject', 'schoolClass']);

        return view('student.materials.show', compact('material'));
    }
}
