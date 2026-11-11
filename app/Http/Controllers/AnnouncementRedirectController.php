<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;

class AnnouncementRedirectController extends Controller
{
    public function __invoke(Announcement $announcement): RedirectResponse
    {
        $user = auth()->user();

        $visible = match ($user->role) {
            'admin' => Announcement::query()->whereKey($announcement->id)->exists(),
            'guru' => Announcement::query()->teacherOwnedBy($user)->whereKey($announcement->id)->exists(),
            'siswa' => Announcement::query()->published()->forStudentClass($user)->whereKey($announcement->id)->exists(),
            default => false,
        };

        abort_unless($visible, 403);

        $route = match ($user->role) {
            'admin' => 'admin.announcements.index',
            'guru' => 'teacher.announcements.index',
            default => 'student.announcements.index',
        };

        return redirect()->route($route, ['highlight' => $announcement->id]);
    }
}
