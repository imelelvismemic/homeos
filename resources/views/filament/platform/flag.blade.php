{{-- Zastava jezika (Faza 9b). SVG, ne emoji: Windows ne renderuje emoji
     zastave (🇧🇦 se prikaže kao slova "BA"), pa bi prekidač na najčešćem
     korisničkom sistemu izgledao pokvareno. --}}
@props(['locale'])

@php
    // Jedinstven id za clipPath (britanska zastava) — isti id dva puta na
    // stranici bi lomio prikaz druge instance.
    $uid = 'flag-'.$locale.'-'.\Illuminate\Support\Str::random(6);
@endphp

<span class="inline-block h-4 w-6 shrink-0 overflow-hidden rounded-[2px] ring-1 ring-black/10 dark:ring-white/20" aria-hidden="true">
    @switch($locale)
        @case('bs')
            {{-- Bosna i Hercegovina: plavo polje, žuti trougao, bijele zvijezde po hipotenuzi. --}}
            <svg viewBox="0 0 60 30" class="h-full w-full">
                <rect width="60" height="30" fill="#00209f" />
                <path d="M14 0H60V30z" fill="#fecb00" />
                <g fill="#fff">
                    <path d="m14.5 1.6 1 2.1 2.3.3-1.7 1.6.4 2.3-2-1.1-2 1.1.4-2.3-1.7-1.6 2.3-.3z" />
                    <path d="m22.5 6.6 1 2.1 2.3.3-1.7 1.6.4 2.3-2-1.1-2 1.1.4-2.3-1.7-1.6 2.3-.3z" />
                    <path d="m30.5 11.6 1 2.1 2.3.3-1.7 1.6.4 2.3-2-1.1-2 1.1.4-2.3-1.7-1.6 2.3-.3z" />
                    <path d="m38.5 16.6 1 2.1 2.3.3-1.7 1.6.4 2.3-2-1.1-2 1.1.4-2.3-1.7-1.6 2.3-.3z" />
                    <path d="m46.5 21.6 1 2.1 2.3.3-1.7 1.6.4 2.3-2-1.1-2 1.1.4-2.3-1.7-1.6 2.3-.3z" />
                </g>
            </svg>
            @break

        @case('en')
            {{-- Ujedinjeno Kraljevstvo. --}}
            <svg viewBox="0 0 60 30" class="h-full w-full">
                <clipPath id="{{ $uid }}">
                    <path d="M30 15h30v15zv15H0zH0V0zV0h30z" />
                </clipPath>
                <rect width="60" height="30" fill="#012169" />
                <path d="M0 0 60 30M60 0 0 30" stroke="#fff" stroke-width="6" />
                <path d="M0 0 60 30M60 0 0 30" clip-path="url(#{{ $uid }})" stroke="#c8102e" stroke-width="4" />
                <path d="M30 0v30M0 15h60" stroke="#fff" stroke-width="10" />
                <path d="M30 0v30M0 15h60" stroke="#c8102e" stroke-width="6" />
            </svg>
            @break

        @case('de')
            {{-- Njemačka. --}}
            <svg viewBox="0 0 60 30" class="h-full w-full">
                <rect width="60" height="10" y="0" fill="#000" />
                <rect width="60" height="10" y="10" fill="#dd0000" />
                <rect width="60" height="10" y="20" fill="#ffce00" />
            </svg>
            @break
    @endswitch
</span>
