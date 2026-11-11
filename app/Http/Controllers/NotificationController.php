<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = auth()->user()
            ->appNotifications()
            ->latest()
            ->paginate(12);

        return view('notifications.index', compact('notifications'));
    }

    public function open(AppNotification $appNotification): RedirectResponse
    {
        abort_unless($appNotification->user_id === auth()->id(), 403);

        $appNotification->update(['read_at' => now()]);

        return redirect($this->localUrl($appNotification->url) ?: route('notifications.index'));
    }

    public function markAllRead(): RedirectResponse
    {
        auth()->user()
            ->unreadAppNotifications()
            ->update(['read_at' => now()]);

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
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
