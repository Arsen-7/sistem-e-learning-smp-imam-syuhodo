<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-extrabold text-slate-900">Tambah Pengumuman</h1>
    </x-slot>

    <form method="POST" action="{{ route('admin.announcements.store') }}" class="rounded-3xl bg-white/90 p-6 shadow-sm">
        @csrf
        @include('admin.announcements._form')
    </form>
</x-app-layout>
