@props(['name', 'size' => 20])

<svg class="ui-icon" width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    @switch($name)
        @case('home') <path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/> @break
        @case('users') <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/> @break
        @case('wallet') <rect x="3" y="5" width="18" height="15" rx="2"/><path d="M3 9h18M16 14h2"/> @break
        @case('activity') <path d="M3 12h4l3-8 4 16 3-8h4"/> @break
        @case('transfer') <path d="M3 7h17m-5-5 5 5-5 5M21 17H4m5-5-5 5 5 5"/> @break
        @case('calendar') <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18M8 14h1m6 0h1m-8 3h1m6 0h1"/> @break
        @case('coins') <ellipse cx="15" cy="5" rx="6" ry="2.5"/><ellipse cx="8" cy="13" rx="5" ry="2.5"/><path d="M9 5v3m12-3v8c0 1.4-2.7 2.5-6 2.5M21 9c0 1.4-2.7 2.5-6 2.5M3 13v5c0 1.4 2.2 2.5 5 2.5s5-1.1 5-2.5v-5"/> @break
        @case('user') <circle cx="12" cy="7" r="4"/><path d="M4 21v-2a5 5 0 0 1 5-5h6a5 5 0 0 1 5 5v2z"/> @break
        @case('user-plus') <circle cx="9" cy="7" r="4"/><path d="M2 21v-2a5 5 0 0 1 5-5h4a5 5 0 0 1 5 5v2H2m17-8v8m-4-4h8"/> @break
        @case('chart') <path d="M4 19V5M4 19h17"/><path d="m7 15 4-4 3 2 6-7"/><path d="M17 6h3v3"/> @break
        @case('file') <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h8"/> @break
        @case('settings') <circle cx="12" cy="12" r="3"/><path d="m19.4 15 .1.1a1.8 1.8 0 0 1-2.5 2.5l-.1-.1a1.8 1.8 0 0 0-3 .9v.2a1.8 1.8 0 0 1-3.6 0v-.2a1.8 1.8 0 0 0-3-.9l-.1.1a1.8 1.8 0 0 1-2.5-2.5l.1-.1a1.8 1.8 0 0 0-.9-3h-.2a1.8 1.8 0 0 1 0-3.6h.2a1.8 1.8 0 0 0 .9-3l-.1-.1a1.8 1.8 0 0 1 2.5-2.5l.1.1a1.8 1.8 0 0 0 3-.9v-.2a1.8 1.8 0 0 1 3.6 0v.2a1.8 1.8 0 0 0 3 .9l.1-.1a1.8 1.8 0 0 1 2.5 2.5l-.1.1a1.8 1.8 0 0 0 .9 3h.2a1.8 1.8 0 0 1 0 3.6h-.2a1.8 1.8 0 0 0-.9 3z"/> @break
        @case('search') <circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/> @break
        @case('bell') <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/> @break
        @case('plus') <path d="M12 5v14M5 12h14"/> @break
        @case('arrow-up') <path d="M7 17 17 7M7 7h10v10"/> @break
        @case('arrow-down') <path d="M7 7 17 17M17 7v10H7"/> @break
        @case('arrow-left') <path d="m15 18-6-6 6-6"/> @break
        @case('arrow-right') <path d="m9 18 6-6-6-6"/> @break
        @case('chevron-down') <path d="m6 9 6 6 6-6"/> @break
        @case('menu') <path d="M4 6h16M4 12h16M4 18h16"/> @break
        @case('close') <path d="m18 6-12 12M6 6l12 12"/> @break
        @case('eye') <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/> @break
        @case('eye-off') <path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.2A10.8 10.8 0 0 1 12 5c6.5 0 10 7 10 7a16 16 0 0 1-3 3.8M6.2 6.2C3.4 8.1 2 12 2 12s3.5 7 10 7a10 10 0 0 0 3-.5"/> @break
        @case('clock') <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/> @break
        @case('check') <path d="m5 12 4 4L19 6"/> @break
        @case('filter') <path d="M4 7h16M7 12h10m-7 5h4"/> @break
        @case('download') <path d="M12 3v12m-5-5 5 5 5-5M5 21h14"/> @break
        @case('mail') <rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/> @break
        @case('phone') <path d="M22 16.9v3a2 2 0 0 1-2.2 2A19.8 19.8 0 0 1 3.1 5.2 2 2 0 0 1 5.1 3h3a2 2 0 0 1 2 1.7l.4 2.7a2 2 0 0 1-.6 1.7L8 11a16 16 0 0 0 5 5l1.9-1.9a2 2 0 0 1 1.7-.6l2.7.4a2 2 0 0 1 1.7 2z"/> @break
        @case('shield') <path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/><path d="m9 12 2 2 4-4"/> @break
        @case('bank') <path d="m3 9 9-6 9 6M4 10h16M6 10v8m4-8v8m4-8v8m4-8v8M3 21h18M4 18h16"/> @break
        @case('logout') <path d="M10 17l5-5-5-5M15 12H3"/><path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/> @break
        @case('edit') <path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/> @break
        @case('trash') <path d="M3 6h18M8 6V4h8v2m3 0-1 14H6L5 6m4 4v6m6-6v6"/> @break
        @default <circle cx="12" cy="12" r="9"/><path d="M12 8v4m0 4h.01"/>
    @endswitch
</svg>
