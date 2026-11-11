<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Concerns\AuthorizesTeacherAssignments;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Services\AcademicYearService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    use AuthorizesTeacherAssignments;

    public function index(AcademicYearService $academicYears): View
    {
        $teacher = $this->currentTeacher();

        $query = Announcement::with(['author', 'schoolClass', 'subject'])
            ->teacherOwnedBy(auth()->user())
            ->latest();

        $academicYears->applyActiveYear($query);

        $announcements = $query->paginate(10);

        return view('teacher.announcements.index', compact('announcements', 'teacher'));
    }

    public function create(): View
    {
        return view('teacher.announcements.create', [
            'classes' => $this->assignedClasses(),
            'pairs' => $this->assignedPairs($this->currentTeacher()),
        ]);
    }

    public function store(Request $request, NotificationService $notifications, AcademicYearService $academicYears): RedirectResponse
    {
        $data = $this->validatedData($request);
        $this->ensureTeacherHandles($this->currentTeacher(), (int) $data['subject_id'], (int) $data['class_id']);

        $announcement = Announcement::create($data + [
            'user_id' => auth()->id(),
            'audience' => 'kelas',
            'status_aktif' => $request->boolean('status_aktif', true),
            'tahun_ajaran_id' => $academicYears->activeId(),
        ]);

        if ($announcement->isPublishedNow()) {
            $notifications->notifyAnnouncement($announcement);
        }

        return redirect()->route('teacher.announcements.index')->with('success', 'Pengumuman kelas berhasil dibuat dan notifikasi dikirim.');
    }

    public function edit(Announcement $announcement): View
    {
        abort_unless(Announcement::query()->teacherOwnedBy(auth()->user())->whereKey($announcement->id)->exists(), 403);

        return view('teacher.announcements.edit', [
            'announcement' => $announcement,
            'classes' => $this->assignedClasses(),
            'pairs' => $this->assignedPairs($this->currentTeacher()),
        ]);
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        abort_unless(Announcement::query()->teacherOwnedBy(auth()->user())->whereKey($announcement->id)->exists(), 403);

        $data = $this->validatedData($request);
        $this->ensureTeacherHandles($this->currentTeacher(), (int) $data['subject_id'], (int) $data['class_id']);

        $announcement->update($data + [
            'audience' => 'kelas',
            'status_aktif' => $request->boolean('status_aktif'),
        ]);

        return redirect()->route('teacher.announcements.index')->with('success', 'Pengumuman kelas berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        abort_unless(Announcement::query()->teacherOwnedBy(auth()->user())->whereKey($announcement->id)->exists(), 403);
        $announcement->delete();

        return redirect()->route('teacher.announcements.index')->with('success', 'Pengumuman kelas berhasil dihapus.');
    }

    private function assignedClasses()
    {
        return $this->assignedPairs($this->currentTeacher())
            ->pluck('schoolClass')
            ->filter()
            ->unique('id')
            ->values();
    }

    private function ensureTeacherHandlesClass(int $classId): void
    {
        abort_unless($this->assignedClasses()->contains('id', $classId), 403, 'Anda hanya dapat membuat pengumuman untuk kelas yang diampu.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'judul' => ['required', 'string', 'max:255'],
            'isi' => ['required', 'string'],
            'publish_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:publish_at'],
        ], [
            'required' => ':attribute wajib diisi.',
            'exists' => ':attribute tidak ditemukan.',
            'date' => ':attribute harus berupa tanggal valid.',
            'after_or_equal' => ':attribute tidak boleh lebih awal dari waktu publikasi.',
        ]);
    }
}
