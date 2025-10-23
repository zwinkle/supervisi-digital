@props([
    'name',
    'classes' => 'h-5 w-5'
])

@php
    $class = trim($classes.' flex-shrink-0');
@endphp

@switch($name)
    @case('layout-dashboard')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <rect x="3.5" y="3.5" width="7" height="7" rx="1.8" />
            <rect x="13.5" y="3.5" width="7" height="4.5" rx="1.8" />
            <rect x="13.5" y="10" width="7" height="10.5" rx="1.8" />
            <rect x="3.5" y="13.5" width="7" height="7" rx="1.8" />
        </svg>
        @break

    @case('users')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <path d="M8 14c-3 0-5.5 2-5.5 4.5v0" />
            <path d="M16 14c3 0 5.5 2 5.5 4.5v0" />
            <circle cx="8" cy="7.5" r="3.2" />
            <circle cx="16" cy="7.5" r="3.2" />
        </svg>
        @break

    @case('buildings')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <path d="M4.5 21.5v-13a1.5 1.5 0 0 1 1.5-1.5h4a1.5 1.5 0 0 1 1.5 1.5v13" />
            <path d="M12.5 21.5v-9A1.5 1.5 0 0 1 14 11h4a1.5 1.5 0 0 1 1.5 1.5v9" />
            <path d="M3 21.5h18" />
            <path d="M7 10h2" />
            <path d="M7 13h2" />
            <path d="M15 13h2" />
            <path d="M15 16h2" />
        </svg>
        @break

    @case('sparkles')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <path d="M9.5 4.5 11 9l4.5 1.5L11 12l-1.5 4.5L8 12 3.5 10.5 8 9z" />
            <path d="m17 5 1 2.5 2.5 1L18 9l-1 2.5-1-2.5-2.5-1L17 7.5z" />
            <path d="m17 14 1 2 2 1-2 1-1 2-1-2-2-1 2-1z" />
        </svg>
        @break

    @case('calendar')
    @case('calendar-days')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <rect x="3.5" y="5" width="17" height="15.5" rx="2" />
            <path d="M7 3.5v3" />
            <path d="M17 3.5v3" />
            <path d="M3.5 9.5h17" />
            <circle cx="8.5" cy="13.5" r="1.1" />
            <circle cx="12" cy="13.5" r="1.1" />
            <circle cx="15.5" cy="13.5" r="1.1" />
            <circle cx="8.5" cy="17" r="1.1" />
            <circle cx="12" cy="17" r="1.1" />
            <circle cx="15.5" cy="17" r="1.1" />
        </svg>
        @break

    @case('graduation-cap')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <path d="M3 9.5 12 5l9 4.5-9 4.5z" />
            <path d="M12 18.5c-3.5 0-6.5-1.4-6.5-3.2V12" />
            <path d="M18.5 12v3.3c0 1.7-3 3.2-6.5 3.2" />
            <path d="m21 10.3-.2 5.2" />
            <circle cx="20.8" cy="16.5" r="0.9" fill="currentColor" stroke="currentColor" stroke-width="0" />
        </svg>
        @break

    @case('send')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <path d="M4 12h9" />
            <path d="m5 4 15 8-15 8 3-8z" />
        </svg>
        @break

    @case('user-circle')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <circle cx="12" cy="12" r="9" />
            <circle cx="12" cy="9" r="2.75" />
            <path d="M7 17.5a5.5 5.5 0 0 1 10 0" />
        </svg>
        @break

    @case('timeline')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <path d="M5 5h6" />
            <path d="M13 12h6" />
            <path d="M5 19h6" />
            <circle cx="4" cy="5" r="1.5" fill="currentColor" stroke="none" />
            <circle cx="12" cy="12" r="1.5" fill="currentColor" stroke="none" />
            <circle cx="4" cy="19" r="1.5" fill="currentColor" stroke="none" />
        </svg>
        @break

    @case('clock')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <circle cx="12" cy="12" r="9" />
            <path d="M12 7v5l3 2" />
        </svg>
        @break

    @case('check-circle')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <circle cx="12" cy="12" r="9" />
            <path d="m9.2 12.5 2.1 2.1 4.3-4.3" />
        </svg>
        @break

    @case('alert-triangle')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <path d="M10.3 4.3 2.7 18a1.5 1.5 0 0 0 1.3 2.2h16a1.5 1.5 0 0 0 1.3-2.2L13.7 4.3a1.5 1.5 0 0 0-2.6 0z" />
            <path d="M12 9.5v4.5" />
            <circle cx="12" cy="16.5" r="0.7" fill="currentColor" stroke="none" />
        </svg>
        @break

    @case('shield-alert')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <path d="M12 3.2 5 6v6.5c0 4.4 3.4 7.8 7 8.8 3.6-1 7-4.4 7-8.8V6l-7-2.8z" />
            <path d="M12 9.5v4" />
            <circle cx="12" cy="15.5" r="0.7" fill="currentColor" stroke="none" />
        </svg>
        @break

    @case('cloud-upload')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <path d="M6.5 18.5A4.5 4.5 0 1 1 7 9.6a5 5 0 0 1 9.8 1.4h.7a4.5 4.5 0 0 1 0 9H8" />
            <path d="m12 12.5 0 7" />
            <path d="m9.5 14.7 2.5-2.5 2.5 2.5" />
        </svg>
        @break

    @case('cloud')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <path d="M7.5 18.5a4.5 4.5 0 0 1 .5-8.9 5 5 0 0 1 9.7 1.2h.8a3.5 3.5 0 0 1 0 7H8.5" />
        </svg>
        @break

    @case('copy')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <rect x="8" y="3.5" width="12.5" height="15" rx="2" />
            <path d="M5.5 8.5v10a2 2 0 0 0 2 2h8" />
        </svg>
        @break

    @case('document')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <path d="M7 3.5h6.5L18 8v12.5a1.5 1.5 0 0 1-1.5 1.5H7A1.5 1.5 0 0 1 5.5 20.5v-15A1.5 1.5 0 0 1 7 3.5z" />
            <path d="M13.5 3.5v4.5H18" />
            <path d="M8.5 12h7" />
            <path d="M8.5 15.5h7" />
        </svg>
        @break

    @case('external-link')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <path d="M10.5 6.5h-4A2.5 2.5 0 0 0 4 9v9a2.5 2.5 0 0 0 2.5 2.5h9A2.5 2.5 0 0 0 18 18.5v-4" />
            <path d="M12.5 11.5 20 4" />
            <path d="M15.5 4H20v4.5" />
        </svg>
        @break

    @case('video')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <rect x="3.5" y="6.5" width="12" height="11" rx="2" />
            <path d="m15.5 9.5 5-3v11l-5-3" />
        </svg>
        @break

    @case('mail')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <rect x="3.5" y="5" width="17" height="14" rx="2" />
            <path d="m4.8 6.5 7.2 6 7.2-6" />
        </svg>
        @break

    @case('inbox')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <path d="M4.5 6h15l1.5 9.5a2 2 0 0 1-2 2.3H5a2 2 0 0 1-2-2.3z" />
            <path d="M4 12h5.5l1.5 2h2l1.5-2H20" />
        </svg>
        @break

    @case('plus')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <path d="M12 5v14" />
            <path d="M5 12h14" />
        </svg>
        @break

    @case('pencil')
    @case('edit')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <path d="M4 17.5 16.5 5a1.5 1.5 0 0 1 2 0l1.5 1.5a1.5 1.5 0 0 1 0 2L7.5 21H4z" />
            <path d="M13.5 6.5 17.5 10.5" />
        </svg>
        @break

    @case('trash')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <path d="M5.5 7.5h13" />
            <path d="M9 4.5h6" />
            <path d="M7.5 7.5 8.5 20a1.5 1.5 0 0 0 1.5 1.4h4a1.5 1.5 0 0 0 1.5-1.4L18.5 7.5" />
        </svg>
        @break

    @case('ellipsis')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="{{ $class }}">
            <circle cx="5.5" cy="12" r="1.4" />
            <circle cx="12" cy="12" r="1.4" />
            <circle cx="18.5" cy="12" r="1.4" />
        </svg>
        @break

    @case('download')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <path d="M12 4.5v11" />
            <path d="m7.5 11.5 4.5 4.5 4.5-4.5" />
            <path d="M5 19.5h14" />
        </svg>
        @break

    @case('check')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <path d="m5.5 12.5 4 4 9-9" />
        </svg>
        @break

    @case('filter')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <path d="M4 6.5h16" />
            <path d="M7 12h10" />
            <path d="M10 17.5h4" />
        </svg>
        @break

    @case('search')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <circle cx="11" cy="11" r="6" />
            <path d="m20 20-3.5-3.5" />
        </svg>
        @break

    @case('chevron-right')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <path d="M9 6.5 15 12l-6 5.5" />
        </svg>
        @break

    @case('chevron-left')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <path d="M15 6.5 9 12l6 5.5" />
        </svg>
        @break

    @case('chevron-down')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <path d="M6.5 9.5 12 15l5.5-5.5" />
        </svg>
        @break

    @case('chevron-up')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <path d="m6.5 14.5 5.5-5.5 5.5 5.5" />
        </svg>
        @break

    @case('logout')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <path d="M15.5 6V4.5A1.5 1.5 0 0 0 14 3H6.5A1.5 1.5 0 0 0 5 4.5v15A1.5 1.5 0 0 0 6.5 21h7.5a1.5 1.5 0 0 0 1.5-1.5V18" />
            <path d="M10 12h9" />
            <path d="m16.5 8.5 3.5 3.5-3.5 3.5" />
        </svg>
        @break

    @case('lock')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <rect x="5.5" y="11" width="13" height="9.5" rx="2" />
            <path d="M8.5 11V8.5a3.5 3.5 0 0 1 7 0V11" />
            <circle cx="12" cy="15.5" r="1.1" />
        </svg>
        @break

    @case('eye')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <path d="M2.5 12s3.5-6.5 9.5-6.5 9.5 6.5 9.5 6.5-3.5 6.5-9.5 6.5S2.5 12 2.5 12z" />
            <circle cx="12" cy="12" r="2.5" />
        </svg>
        @break

    @case('eye-off')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <path d="M3 3l18 18" />
            <path d="M9.8 9.8a3.1 3.1 0 0 1 4.4 4.4" />
            <path d="M7.4 7.6C4.5 9 3 12 3 12s3.5 6.5 9.5 6.5a9.5 9.5 0 0 0 4.4-1" />
            <path d="M17.1 13.9c1.3-1.2 1.9-1.9 2.9-3.4 0 0-3.5-6.5-9.5-6.5-1.4 0-2.6.3-3.7.7" />
        </svg>
        @break

    @case('arrow-up-right')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <path d="M7 17 17 7" />
            <path d="M9 7h8v8" />
        </svg>
        @break

    @case('bar-chart')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <path d="M4 20.5V9.5" />
            <path d="M10 20.5V4.5" />
            <path d="M16 20.5V11.5" />
            <path d="M4 20.5h16" />
        </svg>
        @break

    @case('tag')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <path d="M11 3.5h-5a1.5 1.5 0 0 0-1.5 1.5v5L11 21l10-10-7.5-7.5z" />
            <circle cx="7.5" cy="7.5" r="1.1" />
        </svg>
        @break

    @case('badge-check')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <path d="M12 3.3 14.2 5h2.8l.9 2.8 2.1 2.1-.7 2.7.7 2.7-2.1 2.1-.9 2.8h-2.8L12 20.7 9.8 19H7l-.9-2.8L4 14.3l.7-2.7L4 8.9 6.1 6.8 7 4h2.8z" />
            <path d="m9.8 12 1.8 1.8L15 10.2" />
        </svg>
        @break

    @case('settings')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <circle cx="12" cy="12" r="3" />
            <path d="M19.4 12a7.4 7.4 0 0 0-.1-1.2l2.1-1.6-2-3.4-2.5 1a7.6 7.6 0 0 0-2.1-1.2l-.3-2.6H9.5l-.3 2.6a7.6 7.6 0 0 0-2.1 1.2l-2.5-1-2 3.4 2.1 1.6a7.4 7.4 0 0 0 0 2.4l-2.1 1.6 2 3.4 2.5-1a7.6 7.6 0 0 0 2.1 1.2l.3 2.6h4.6l.3-2.6a7.6 7.6 0 0 0 2.1-1.2l2.5 1 2-3.4-2.1-1.6c.1-.4.1-.8.1-1.2z" />
        </svg>
        @break

    @case('refresh')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <path d="M20.5 11.5a7.5 7.5 0 0 0-12-5.7L7 3" />
            <path d="M7 6.5v-3h-3" />
            <path d="M3.5 12.5a7.5 7.5 0 0 0 12 5.7L17 21" />
            <path d="M17 17.5v3h3" />
        </svg>
        @break

    @case('shield-check')
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <path d="M12 3.2 5 6v6.5c0 4.3 3.4 7.7 7 8.8 3.6-1.1 7-4.5 7-8.8V6l-7-2.8z" />
            <path d="m9.5 12.3 1.9 1.9 3.6-3.6" />
        </svg>
        @break

    @default
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
            <circle cx="12" cy="12" r="9" />
            <path d="M12 8v4l2.5 2.5" />
        </svg>
@endswitch
