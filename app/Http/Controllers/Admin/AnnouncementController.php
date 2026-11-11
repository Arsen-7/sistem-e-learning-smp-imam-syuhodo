<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\SchoolClass;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        $announcements = Announcement::with(['author', 'schoolClass'])
            ->latest()
            ->paginate(10);

        return view('admin.announcements.index', compact('announcements'));
    }

    public function create(): View
    {
        return view('admin.announcements.create', [
            'classes' => SchoolClass::ordered()->get(),
        ]);
    }

    public function store(Request $request, NotificationService $notifications): RedirectResponse
    {
        $announcement = Announcement::create($this->validatedData($request) + [
            'user_id' => auth()->id(),
            'status_aktif' => $request->boolean('status_aktif', true),
        ]);

        if ($announcement->isPublishedNow()) {
            $notifications->notifyAnnouncement($announcement);
        }

        return redirect()->route('admin.announcements.index')->with('success', 'Pengumuman berhasil dibuat dan notifikasi dikirim.');
    }

    public function edit(Announcement $announcement): View
    {
        return view('admin.announcements.edit', [
            'announcement' => $announcement,
            'classes' => SchoolClass::ordered()->get(),
        ]);
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $announcement->update($this->validatedData($request) + [
            'status_aktif' => $request->boolean('status_aktif'),
        ]);

        return redirect()->route('admin.announcements.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return redirect()->route('admin.announcements.index')->with('success', 'Pengumuman berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'audience' => ['required', 'in:semua,guru,siswa,kelas'],
            'class_id' => ['nullable', 'required_if:audience,kelas', 'exists:classes,id'],
            'judul' => ['required', 'string', 'max:255'],
            'isi' => ['required', 'string'],
            'publish_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:publish_at'],
        ], [
            'required' => ':attribute wajib diisi.',
            'required_if' => ':attribute wajib dipilih untuk target kelas.',
            'exists' => ':attribute tidak ditemukan.',
            'date' => ':attribute harus berupa tanggal valid.',
            'after_or_equal' => ':attribute tidak boleh lebih awal dari waktu publikasi.',
        ]);

        if ($data['audience'] !== 'kelas') {
            $data['class_id'] = null;
        }

        return $data;
    }
}
