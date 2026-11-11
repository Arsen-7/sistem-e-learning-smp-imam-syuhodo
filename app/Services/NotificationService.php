<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class NotificationService
{
    public function notifyUser(User $user, string $title, ?string $body = null, ?string $url = null, string $type = 'info', ?string $relatedType = null, ?int $relatedId = null): AppNotification
    {
        $attributes = [
            'user_id' => $user->id,
            'title' => $title,
        ];

        if ($relatedType && $relatedId) {
            $attributes['related_type'] = $relatedType;
            $attributes['related_id'] = $relatedId;
        }

        return AppNotification::updateOrCreate($attributes, [
            'body' => $body,
            'url' => $this->localUrl($url),
            'type' => $type,
            'read_at' => null,
        ]);
    }

    public function notifyUsers(Collection|EloquentCollection $users, string $title, ?string $body = null, ?string $url = null, string $type = 'info', ?string $relatedType = null, ?int $relatedId = null): void
    {
        $users->each(fn (User $user) => $this->notifyUser($user, $title, $body, $url, $type, $relatedType, $relatedId));
    }

    public function notifyAnnouncement(Announcement $announcement): void
    {
        $users = $this->announcementRecipients($announcement)
            ->reject(fn (User $user) => $user->id === $announcement->user_id)
            ->values();

        $this->notifyUsers(
            $users,
            'Pengumuman baru: '.$announcement->judul,
            str($announcement->isi)->limit(140)->toString(),
            route('announcements.redirect', $announcement, false),
            'announcement',
            Announcement::class,
            $announcement->id
        );
    }

    public function announcementRecipients(Announcement $announcement): EloquentCollection
    {
        return match ($announcement->audience) {
            'guru' => User::where('role', 'guru')->get(),
            'siswa' => User::where('role', 'siswa')->get(),
            'kelas' => User::where('role', 'siswa')
                ->where('status_akun', 'Aktif')
                ->whereHas('student', fn ($query) => $query
                    ->where('class_id', $announcement->class_id)
                    ->where('status_siswa', 'Aktif')
                    ->where(function ($yearQuery) use ($announcement): void {
                        $yearQuery
                            ->whereNull('tahun_ajaran_id')
                            ->orWhere('tahun_ajaran_id', $announcement->tahun_ajaran_id);
                    }))
                ->get(),
            default => User::whereIn('role', ['admin', 'guru', 'siswa'])->get(),
        };
    }

    private function localUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $parts = parse_url($url);

        if (! isset($parts['scheme'], $parts['host'])) {
            return $url;
        }

        $path = $parts['path'] ?? '/';
        $query = isset($parts['query']) ? '?'.$parts['query'] : '';

        return $path.$query;
    }
}
