@props(['name'])

<svg viewBox="0 0 24 24" {{ $attributes->merge(['class' => 'size-5']) }} fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('dashboard')
            <path d="M4 13h4V4H4v9Zm12 7h4v-7h-4v7ZM4 20h4v-3H4v3Zm12-11h4V4h-4v5ZM10 20h4V4h-4v16Z" />
            @break
        @case('branches')
            <path d="M4 20V7l8-3 8 3v13M2 20h20M8 9h2m4 0h2M8 13h2m4 0h2M10 20v-4h4v4" />
            @break
        @case('leads')
            <path d="M7 4h10v16H7zM9 8h6M9 12h6M9 16h3M5 7h2m10 0h2" />
            @break
        @case('members')
            <circle cx="12" cy="12" r="7" /><circle cx="12" cy="12" r="2" /><path d="M12 5v2m0 10v2M5 12h2m10 0h2" />
            @break
        @case('trainers')
            <path d="M8 10a4 4 0 1 0 8 0 4 4 0 0 0-8 0Zm-4 9c.8-3.1 3.4-5 8-5s7.2 1.9 8 5M5 8l2-2m10 2-2-2" />
            @break
        @case('plans')
            <circle cx="12" cy="8" r="4" /><circle cx="12" cy="16" r="4" /><path d="M8 8h8M8 16h8" />
            @break
        @case('batches')
            <rect x="4" y="5" width="16" height="15" rx="2" /><path d="M8 3v4m8-4v4M4 10h16M8 14h3m2 0h3" />
            @break
        @case('attendance')
            <circle cx="12" cy="13" r="7" /><path d="M12 9v4l3 2M9 3h6M12 3v3" />
            @break
        @case('finance')
            <circle cx="12" cy="12" r="8" /><path d="M14.5 9.5c-.5-.7-1.4-1-2.5-1-1.4 0-2.5.7-2.5 1.7s1 1.4 2.5 1.7 2.5.7 2.5 1.8-1.1 1.8-2.5 1.8c-1.1 0-2-.3-2.5-1M12 7v10" />
            @break
        @case('reports')
            <path d="M4 19V5m0 14h16M8 16v-4m4 4V8m4 8V6" />
            @break
        @case('fitness')
            <path d="M8 8h8v8H8zM5 10v4m14-4v4M3 11v2m18-2v2M8 12h8" />
            @break
        @case('operations')
            <path d="m14 6 4 4M3 21l4.5-1 10.8-10.8a2.8 2.8 0 0 0-4-4L3.5 16 3 21ZM14 17h6M16 21h4" />
            @break
        @default
            <circle cx="12" cy="12" r="8" />
    @endswitch
</svg>
