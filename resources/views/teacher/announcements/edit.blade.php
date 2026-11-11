<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-extrabold text-slate-900">Edit Pengumuman Kelas</h1>
    </x-slot>

    <form method="POST" action="{{ route('teacher.announcements.update', $announcement) }}" class="rounded-3xl bg-white/90 p-6 shadow-sm">
        @csrf
        @method('PUT')
        @include('teacher.announcements._form')
    </form>
</x-app-layout>
