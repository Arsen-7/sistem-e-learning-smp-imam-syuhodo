<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900">Notifikasi</h1>
                <p class="text-sm text-slate-500">Info terbaru tentang pengumuman, tugas, pengumpulan, dan nilai.</p>
            </div>
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button class="app-action-button app-action-button--read">Tandai Semua Dibaca</button>
            </form>
        </div>
    </x-slot>

    <div class="space-y-3">
        @forelse($notifications as $notification)
            <a href="{{ route('notifications.open', $notification) }}" class="block rounded-3xl bg-white/90 p-5 shadow-sm ring-1 {{ $notification->read_at ? 'ring-white/60' : 'ring-cyan-200' }}">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full {{ $notification->read_at ? 'bg-slate-100 text-slate-600' : 'bg-cyan-50 text-cyan-700' }} px-3 py-1 text-xs font-bold">
                                {{ $notification->read_at ? 'Sudah dibaca' : 'Baru' }}
                            </span>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">{{ $notification->created_at->format('d M Y H:i') }}</span>
                        </div>
                        <h2 class="mt-3 text-base font-black text-slate-900">{{ $notification->title }}</h2>
                        @if($notification->body)
                            <p class="mt-1 text-sm leading-6 text-slate-600">{{ $notification->body }}</p>
                        @endif
                    </div>
                    <span class="app-action-button app-action-button--read">Baca</span>
                </div>
            </a>
        @empty
            <div class="rounded-3xl bg-white/90 p-8 text-center text-slate-500">Belum ada notifikasi.</div>
        @endforelse
    </div>

    <div class="mt-5">{{ $notifications->links() }}</div>
</x-app-layout>
