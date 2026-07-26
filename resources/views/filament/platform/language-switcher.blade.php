{{-- Prekidač jezika (ROADMAP Faza 9b): zastava + kod jezika, padajuća lista.
     Čisti Alpine (Filament ga već isporučuje) + obična forma po jeziku — radi
     i bez JavaScripta, pa nema stanja koje može ostati "zaglavljeno".
     Stoji i u traci prijavljenog korisnika i na stranici prijave. --}}

@php
    $current = app()->getLocale();
    $locales = \App\Platform\Localization\Locales::SUPPORTED;
@endphp

<div
    x-data="{ open: false }"
    @keydown.escape="open = false"
    @click.outside="open = false"
    class="homeos-language-switcher relative"
>
    <button
        type="button"
        x-on:click="open = ! open"
        :aria-expanded="open"
        aria-haspopup="true"
        class="flex items-center gap-1.5 rounded-lg px-2 py-1.5 text-xs font-medium text-gray-500 outline-none transition hover:bg-gray-50 focus-visible:ring-2 focus-visible:ring-primary-600 dark:text-gray-400 dark:hover:bg-white/5"
        title="{{ \App\Platform\Localization\Locales::label($current) }}"
    >
        @include('filament.platform.flag', ['locale' => $current])
        {{-- Kod jezika se na najužim ekranima gubi: traka tamo već nosi
             hamburger, pretragu, brzo dodavanje, zvonce i avatar. --}}
        <span class="hidden uppercase sm:inline">{{ $current }}</span>
        <x-filament::icon icon="heroicon-m-chevron-down" class="h-3 w-3" />
    </button>

    <div
        {{-- Inline `display:none` umjesto x-cloak: lista ne smije bljesnuti
             prije nego Alpine preuzme element. --}}
        style="display: none"
        x-show="open"
        x-transition.origin.top.right
        class="absolute end-0 z-50 mt-1 w-44 overflow-hidden rounded-lg bg-white py-1 shadow-lg ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
    >
        @foreach ($locales as $code => $label)
            <form method="POST" action="{{ route('locale', ['locale' => $code]) }}">
                @csrf
                <button
                    type="submit"
                    @class([
                        'flex w-full items-center gap-2 px-3 py-2 text-sm outline-none transition hover:bg-gray-50 focus-visible:bg-gray-50 dark:hover:bg-white/5 dark:focus-visible:bg-white/5',
                        'font-semibold text-primary-600 dark:text-primary-400' => $code === $current,
                        'text-gray-700 dark:text-gray-200' => $code !== $current,
                    ])
                    @if ($code === $current) aria-current="true" @endif
                >
                    @include('filament.platform.flag', ['locale' => $code])
                    <span>{{ $label }}</span>
                    @if ($code === $current)
                        <x-filament::icon icon="heroicon-m-check" class="ms-auto h-4 w-4" />
                    @endif
                </button>
            </form>
        @endforeach
    </div>
</div>
