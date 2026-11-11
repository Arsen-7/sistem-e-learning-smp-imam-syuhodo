<x-app-layout>
    <x-slot name="header"><h1 class="text-2xl font-extrabold text-slate-900">Edit Kelas</h1></x-slot>
    <form method="POST" action="{{ route('admin.classes.update', $schoolClass) }}" class="rounded-3xl bg-white/90 p-6 shadow-sm">@csrf @method('PUT') @include('admin.classes._form')</form>
</x-app-layout>
