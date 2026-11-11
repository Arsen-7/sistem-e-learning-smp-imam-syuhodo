@props(['name'])

@switch($name)
    @case('dashboard')
        <svg {{ $attributes->merge(['class' => 'h-5 w-5']) }} stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4h6v6H4V4Zm10 0h6v6h-6V4ZM4 14h6v6H4v-6Zm10 0h6v6h-6v-6Z" />
        </svg>
        @break

    @case('users')
        <svg {{ $attributes->merge(['class' => 'h-5 w-5']) }} stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M9.5 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm11.5 10v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
        </svg>
        @break

    @case('class')
        <svg {{ $attributes->merge(['class' => 'h-5 w-5']) }} stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 21V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v16M8 7h2m4 0h2M8 11h2m4 0h2M8 15h2m4 0h2M3 21h18" />
        </svg>
        @break

    @case('book')
        <svg {{ $attributes->merge(['class' => 'h-5 w-5']) }} stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M4 19.5A2.5 2.5 0 0 0 6.5 22H20V4H6.5A2.5 2.5 0 0 0 4 6.5v13Z" />
        </svg>
        @break

    @case('teacher')
        <svg {{ $attributes->merge(['class' => 'h-5 w-5']) }} stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m3 8 9-5 9 5-9 5-9-5Zm4 4v4.5c0 1.5 2.2 2.5 5 2.5s5-1 5-2.5V12m4-4v7" />
        </svg>
        @break

    @case('weights')
        <svg {{ $attributes->merge(['class' => 'h-5 w-5']) }} stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M7 6v12m10-12v12M4 18h16M9 10h6m-6 4h6" />
        </svg>
        @break

    @case('announcement')
        <svg {{ $attributes->merge(['class' => 'h-5 w-5']) }} stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 13V7a2 2 0 0 1 2-2h2l9-2v16l-9-2H6a2 2 0 0 1-2-2v-2Zm4 4 2 4h3l-2-4" />
        </svg>
        @break

    @case('report')
    @case('analytics')
        <svg {{ $attributes->merge(['class' => 'h-5 w-5']) }} stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19V5m0 14h16M8 16v-5m4 5V8m4 8v-3" />
        </svg>
        @break

    @case('assignment')
        <svg {{ $attributes->merge(['class' => 'h-5 w-5']) }} stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 4h6l1 2h3v15H5V6h3l1-2Zm0 8h6m-6 4h6" />
        </svg>
        @break

    @case('grade')
        <svg {{ $attributes->merge(['class' => 'h-5 w-5']) }} stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 13 4 4L19 7M5 5h14v14H5V5Z" />
        </svg>
        @break

    @case('profile')
        <svg {{ $attributes->merge(['class' => 'h-5 w-5']) }} stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 21a8 8 0 1 0-16 0M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" />
        </svg>
        @break

    @case('notification')
        <svg {{ $attributes->merge(['class' => 'h-5 w-5']) }} stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9Zm-8 12a2 2 0 0 0 4 0" />
        </svg>
        @break

    @case('logout')
        <svg {{ $attributes->merge(['class' => 'h-5 w-5']) }} stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17l5-5-5-5M20 12H9m3 8H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h7" />
        </svg>
        @break

    @default
        <svg {{ $attributes->merge(['class' => 'h-5 w-5']) }} stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14" />
        </svg>
@endswitch
