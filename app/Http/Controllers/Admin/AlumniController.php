<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alumni;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlumniController extends Controller
{
    public function index(Request $request): View
    {
        $alumni = Alumni::with(['student.schoolClass', 'academicYear'])
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = '%'.$request->q.'%';
                $query->whereHas('student', fn ($student) => $student
                    ->where('nama', 'like', $term)
                    ->orWhere('nis', 'like', $term));
            })
            ->when($request->filled('tahun_ajaran_id'), fn ($query) => $query->where('tahun_ajaran_id', $request->integer('tahun_ajaran_id')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.alumni.index', compact('alumni'));
    }

    public function show(Alumni $alumni): View
    {
        $alumni->load([
            'student.schoolClass',
            'student.grades.subject',
            'student.grades.schoolClass',
            'student.classHistories.schoolClass',
            'student.classHistories.academicYear',
            'academicYear',
        ]);

        return view('admin.alumni.show', compact('alumni'));
    }
}
