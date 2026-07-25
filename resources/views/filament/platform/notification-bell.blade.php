{{-- Zvonce u topbaru → sanduče obavještenja trenutnog člana, s brojačem nepročitanih. --}}
<a
    href="{{ $inboxUrl }}"
    class="relative flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-200"
    title="{{ __('platform.inbox.title') }}"
    aria-label="{{ __('platform.inbox.title') }}"
>
    <x-filament::icon icon="heroicon-o-bell" class="h-5 w-5" />

    @if ($unreadCount > 0)
        <span class="absolute -right-0.5 -top-0.5 flex min-w-[1.15rem] items-center justify-center rounded-full bg-danger-500 px-1 text-[0.65rem] font-semibold leading-4 text-white">
            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
        </span>
    @endif
</a>
