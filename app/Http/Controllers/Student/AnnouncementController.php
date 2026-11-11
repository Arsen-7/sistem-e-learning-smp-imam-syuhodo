<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Services\AcademicYearService;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(AcademicYearService $academicYears): View
    {
        $query = Announcement::with(['author', 'schoolClass', 'subject'])
            ->published()
            ->forStudentClass(auth()->user());

        $academicYears->applyActiveYear($query);

        $announcements = $query->latest()->paginate(10);

        return view('student.announcements.index', compact('announcements'));
    }
}
