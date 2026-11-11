<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Concerns\AuthorizesTeacherAssignments;
use App\Http\Controllers\Controller;
use App\Models\TeachingHistory;
use Illuminate\View\View;

class TeachingArchiveController extends Controller
{
    use AuthorizesTeacherAssignments;

    public function index(): View
    {
        $teacher = $this->currentTeacher();

        $histories = TeachingHistory::with(['subject', 'schoolClass', 'academicYear'])
            ->where('teacher_id', $teacher->id)
            ->latest()
            ->paginate(15);

        return view('teacher.teaching-archives.index', compact('histories'));
    }
}
