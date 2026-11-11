<x-app-layout>
    <x-slot name="header"><h1 class="text-2xl font-extrabold text-slate-900">Tambah Pengguna</h1></x-slot>
    <form method="POST" action="{{ route('admin.users.store') }}" class="rounded-3xl border border-white/70 bg-white/90 p-6 shadow-sm">
        @csrf
        @include('admin.users._form')
    </form>
</x-app-layout>
