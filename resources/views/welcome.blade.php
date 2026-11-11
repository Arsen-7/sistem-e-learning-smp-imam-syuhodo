<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'E-Learning Imam Syuhodo') }}</title>
        <link rel="icon" href="{{ asset('images/logo-smp.svg') }}" type="image/svg+xml">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-800">
        <main class="min-h-screen bg-slate-50">
            <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur">
                <div class="mx-auto flex h-20 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                    <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3">
                        <div class="grid h-12 w-12 shrink-0 place-items-center overflow-hidden rounded-lg bg-white p-1 shadow-sm ring-1 ring-cyan-100">
                            <x-application-logo class="h-full w-full" />
                        </div>
                        <div class="min-w-0">
                            <div class="truncate text-base font-extrabold text-slate-950">E-Learning Imam Syuhodo</div>
                            <div class="truncate text-sm font-semibold text-slate-500">SMP Muhammadiyah Imam Syuhodo</div>
                        </div>
                    </a>

                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-lg bg-cyan-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-cyan-800">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-lg bg-cyan-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-cyan-800">Masuk</a>
                    @endauth
                </div>
            </header>

            <section class="relative overflow-hidden bg-slate-950 text-white">
                <img src="{{ asset('images/logo-smp.svg') }}" alt="" class="pointer-events-none absolute bottom-8 right-4 w-64 opacity-10 sm:w-80 lg:right-16 lg:w-[28rem]">
                <div class="absolute inset-x-0 bottom-0 h-24 bg-cyan-900/20"></div>

                <div class="relative mx-auto flex min-h-[calc(100svh-5rem)] max-w-7xl flex-col justify-center px-4 py-12 sm:px-6 lg:px-8">
                    <p class="text-sm font-extrabold uppercase tracking-wider text-cyan-300">Portal Pembelajaran Digital</p>
                    <h1 class="mt-4 max-w-4xl text-4xl font-black leading-tight text-white sm:text-5xl lg:text-6xl">E-Learning Imam Syuhodo</h1>
                    <p class="mt-5 max-w-2xl text-base leading-7 text-slate-200 sm:text-lg">Satu ruang kerja akademik untuk materi, tugas, nilai, pengumuman, dan pemantauan perkembangan siswa.</p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="rounded-lg bg-cyan-500 px-5 py-3 text-sm font-extrabold text-white shadow-sm transition hover:bg-cyan-400">Buka Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-lg bg-cyan-500 px-5 py-3 text-sm font-extrabold text-white shadow-sm transition hover:bg-cyan-400">Masuk ke Sistem</a>
                        @endauth
                    </div>

                    <div class="mt-12 grid max-w-4xl gap-3 sm:grid-cols-3">
                        <div class="rounded-lg border border-white/10 bg-white/10 p-4 backdrop-blur">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-cyan-400/20 text-cyan-200">
                                <x-sidebar-icon name="book" class="h-5 w-5" />
                            </div>
                            <div class="mt-4 text-sm font-extrabold text-white">Materi Terstruktur</div>
                            <div class="mt-1 text-sm leading-6 text-slate-300">Bahan ajar tersusun berdasarkan kelas dan mata pelajaran.</div>
                        </div>
                        <div class="rounded-lg border border-white/10 bg-white/10 p-4 backdrop-blur">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-400/20 text-amber-200">
                                <x-sidebar-icon name="assignment" class="h-5 w-5" />
                            </div>
                            <div class="mt-4 text-sm font-extrabold text-white">Tugas Terpantau</div>
                            <div class="mt-1 text-sm leading-6 text-slate-300">Pengumpulan dan penilaian tugas berada dalam satu sistem.</div>
                        </div>
                        <div class="rounded-lg border border-white/10 bg-white/10 p-4 backdrop-blur">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-400/20 text-emerald-200">
                                <x-sidebar-icon name="analytics" class="h-5 w-5" />
                            </div>
                            <div class="mt-4 text-sm font-extrabold text-white">Analitik Akademik</div>
                            <div class="mt-1 text-sm leading-6 text-slate-300">Perkembangan nilai dapat dipantau oleh guru dan siswa.</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="grid gap-4 md:grid-cols-3">
                    @foreach([
                        ['role' => 'Admin', 'desc' => 'Mengelola pengguna, kelas, mata pelajaran, pengampu, bobot nilai, pengumuman, dan laporan.', 'icon' => 'users', 'tone' => 'cyan'],
                        ['role' => 'Guru', 'desc' => 'Mengelola materi, tugas, nilai, pengumuman kelas, dan analitik akademik.', 'icon' => 'teacher', 'tone' => 'amber'],
                        ['role' => 'Siswa', 'desc' => 'Mengakses materi, mengumpulkan tugas, melihat nilai, dan membaca pengumuman.', 'icon' => 'grade', 'tone' => 'emerald'],
                    ] as $item)
                        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                            <div @class([
                                'flex h-11 w-11 items-center justify-center rounded-lg',
                                'bg-cyan-50 text-cyan-700' => $item['tone'] === 'cyan',
                                'bg-amber-50 text-amber-700' => $item['tone'] === 'amber',
                                'bg-emerald-50 text-emerald-700' => $item['tone'] === 'emerald',
                            ])>
                                <x-sidebar-icon :name="$item['icon']" class="h-5 w-5" />
                            </div>
                            <h2 class="mt-5 text-lg font-extrabold text-slate-950">{{ $item['role'] }}</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ $item['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        </main>
    </body>
</html>
