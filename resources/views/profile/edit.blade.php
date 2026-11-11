<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if($user->isSiswa() && $user->student)
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h3 class="text-lg font-extrabold text-slate-900">Profil Siswa</h3>
                    <div class="mt-4 grid gap-4 md:grid-cols-3">
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <div class="text-xs font-bold uppercase text-slate-500">Nama</div>
                            <div class="mt-1 font-extrabold text-slate-900">{{ $user->student->nama }}</div>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <div class="text-xs font-bold uppercase text-slate-500">NIS/NISN</div>
                            <div class="mt-1 font-extrabold text-slate-900">{{ $user->student->nis }}</div>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <div class="text-xs font-bold uppercase text-slate-500">Email/Username</div>
                            <div class="mt-1 font-extrabold text-slate-900">{{ $user->email }}</div>
                        </div>
                        <div class="rounded-2xl bg-cyan-50 p-4">
                            <div class="text-xs font-bold uppercase text-cyan-700">Kelas</div>
                            <div class="mt-1 font-extrabold text-cyan-900">{{ $user->student->schoolClass->nama_kelas ?? '-' }}</div>
                        </div>
                        <div class="rounded-2xl bg-cyan-50 p-4">
                            <div class="text-xs font-bold uppercase text-cyan-700">Tahun Ajaran Aktif</div>
                            <div class="mt-1 font-extrabold text-cyan-900">{{ $user->student->academicYear->nama ?? '-' }}</div>
                        </div>
                        <div class="rounded-2xl bg-cyan-50 p-4">
                            <div class="text-xs font-bold uppercase text-cyan-700">Status</div>
                            <div class="mt-1 font-extrabold text-cyan-900">{{ $user->student->status_siswa ?? 'Aktif' }}</div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
