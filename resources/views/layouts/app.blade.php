<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'E-Learning Imam Syuhodo') }}</title>
        <link rel="icon" href="{{ asset('images/logo-smp.svg') }}" type="image/svg+xml">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <style>
            .app-action-bar {
                align-items: flex-start;
                display: flex;
                flex-shrink: 0;
                gap: 0.5rem;
                justify-content: flex-end;
            }

            .app-action-button {
                align-items: center;
                align-self: flex-start;
                border: 0;
                border-radius: 0.75rem;
                display: inline-flex;
                font-size: 0.75rem;
                font-weight: 700;
                height: 2.25rem;
                justify-content: center;
                line-height: 1;
                max-height: 2.25rem;
                min-height: 2.25rem;
                padding: 0 0.75rem;
                text-decoration: none;
                white-space: nowrap;
            }

            .app-action-button--detail,
            .app-action-button--read {
                background: #cffafe;
                color: #155e75;
            }

            .app-action-button--edit {
                background: #fef3c7;
                color: #92400e;
            }

            .app-action-button--delete {
                background: #ffe4e6;
                color: #be123c;
            }
        </style>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-800">
        <div
            x-data="{
                sidebarStorageKey: 'elearning-sidebar-open',
                sidebarOpen: false,
                initSidebar() {
                    const storedState = this.getStoredSidebarState();

                    this.sidebarOpen = window.innerWidth >= 768
                        ? storedState !== 'closed'
                        : false;

                    this.syncBodyScroll();
                },
                toggleSidebar() {
                    this.sidebarOpen = ! this.sidebarOpen;
                },
                getStoredSidebarState() {
                    try {
                        return window.localStorage.getItem(this.sidebarStorageKey);
                    } catch (error) {
                        return null;
                    }
                },
                setStoredSidebarState(value) {
                    try {
                        window.localStorage.setItem(this.sidebarStorageKey, value ? 'open' : 'closed');
                    } catch (error) {
                        return;
                    }
                },
                handleResize() {
                    const storedState = this.getStoredSidebarState();

                    this.sidebarOpen = window.innerWidth >= 768
                        ? storedState !== 'closed'
                        : false;

                    this.syncBodyScroll();
                },
                syncBodyScroll() {
                    document.body.classList.toggle('overflow-hidden', this.sidebarOpen && window.innerWidth < 768);
                },
            }"
            x-init="
                initSidebar();
                $watch('sidebarOpen', value => {
                    if (window.innerWidth >= 768) {
                        setStoredSidebarState(value);
                    }

                    syncBodyScroll();
                });
                window.addEventListener('resize', () => handleResize());
            "
            class="min-h-screen bg-gradient-to-br from-slate-50 via-cyan-50 to-amber-50"
        >
            @include('layouts.navigation')

            <div
                class="min-h-screen transition-[padding] duration-300 ease-in-out"
                :class="sidebarOpen ? 'md:pl-72' : 'md:pl-20'"
            >
                <div class="sticky top-0 z-30 border-b border-white/70 bg-white/70 backdrop-blur-xl">
                    <div class="mx-auto flex h-14 max-w-7xl items-center px-4 sm:px-6 lg:px-8">
                        <button
                            type="button"
                            @click="toggleSidebar()"
                            class="grid h-10 w-10 shrink-0 place-items-center rounded-xl border border-cyan-100 bg-white text-slate-600 shadow-sm transition hover:border-cyan-200 hover:bg-cyan-50 hover:text-cyan-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2"
                            aria-controls="app-sidebar"
                            :aria-expanded="sidebarOpen.toString()"
                            aria-label="Buka atau tutup sidebar"
                        >
                            <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{'hidden': sidebarOpen, 'inline-flex': ! sidebarOpen }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{'hidden': ! sidebarOpen, 'inline-flex': sidebarOpen }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                @isset($header)
                    <header class="border-b border-white/70 bg-white/70 shadow-sm backdrop-blur">
                        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    @if (session('success'))
                        <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                            <div class="font-semibold">Periksa kembali input berikut:</div>
                            <ul class="mt-2 list-disc space-y-1 ps-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>

        <x-confirm-dialog />

        @stack('scripts')
    </body>
</html>
