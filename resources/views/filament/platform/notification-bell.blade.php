{{-- Zvonce u topbaru s brojačem nepročitanih. Komponenta se osvježava sama
     (wire:poll), pa nova obavijest stigne i bez ponovnog učitavanja stranice;
     označavanje pročitanim je trenutno (Livewire event).

     Faza 9c: na širokim ekranima klik otvara panel s desne strane — korisnik
     pročita i potvrdi, pa ostane tamo gdje je bio. Na uskim ekranima panel te
     širine nema smisla, pa zvonce vodi na punu stranicu sandučeta. Zato dva
     okidača, razdvojena CSS-om, a ne grananjem u JavaScriptu. --}}
<div wire:poll.30s="refreshCount">
    {{-- Uski ekrani: obična navigacija na stranicu. --}}
    <a
        href="{{ $inboxUrl }}"
        class="relative flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 lg:hidden dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-200"
        title="{{ __('platform.inbox.title') }}"
        aria-label="{{ __('platform.inbox.title') }}"
    >
        <x-filament::icon icon="heroicon-o-bell" class="h-5 w-5" />

        @if ($unreadCount > 0)
            <span
                class="absolute -right-0.5 -top-0.5 flex min-w-[1.15rem] items-center justify-center rounded-full bg-danger-500 px-1 text-[0.65rem] font-semibold leading-4 text-white"
                aria-live="polite"
            >{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
        @endif
    </a>

    {{-- Široki ekrani: panel. --}}
    <button
        type="button"
        wire:click="openPanel"
        class="relative hidden h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 lg:flex dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-200"
        title="{{ __('platform.inbox.title') }}"
        aria-label="{{ __('platform.inbox.title') }}"
        aria-expanded="{{ $panelOpen ? 'true' : 'false' }}"
    >
        <x-filament::icon icon="heroicon-o-bell" class="h-5 w-5" />

        @if ($unreadCount > 0)
            <span
                class="absolute -right-0.5 -top-0.5 flex min-w-[1.15rem] items-center justify-center rounded-full bg-danger-500 px-1 text-[0.65rem] font-semibold leading-4 text-white"
                aria-live="polite"
            >{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
        @endif
    </button>

    @if ($panelOpen)
        {{-- Zamagljena pozadina je ISTI recept kao brzo dodavanje i univerzalna
             pretraga (`bg-gray-950/50 backdrop-blur-sm`), da efekat kroz cijelu
             aplikaciju bude jedan i prepoznatljiv. --}}
        <div class="fixed inset-0 z-50" x-on:keydown.escape.window="$wire.closePanel()">
            <div
                class="fixed inset-0 bg-gray-950/50 backdrop-blur-sm"
                x-on:click="$wire.closePanel()"
            ></div>

            <div
                x-data
                x-transition
                {{-- Panel je PUN (kao panel pretrage), zamagljena je pozadina iza
                     njega — inače tekst obavještenja stoji nad sadržajem stranice
                     i čitljivost zavisi od toga šta je slučajno ispod. --}}
                class="fixed inset-y-0 end-0 flex w-full max-w-md flex-col bg-white shadow-2xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
                role="dialog"
                aria-modal="true"
                aria-label="{{ __('platform.inbox.title') }}"
            >
                <div class="flex items-center gap-2 border-b border-gray-100 px-4 py-3 dark:border-white/10">
                    <h2 class="homeos-display text-base text-gray-950 dark:text-white">
                        {{ __('platform.inbox.title') }}
                    </h2>

                    @if ($unreadCount > 0)
                        <span class="rounded-full bg-danger-500 px-1.5 text-[0.65rem] font-semibold leading-5 text-white">
                            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                        </span>
                    @endif

                    <button
                        type="button"
                        wire:click="closePanel"
                        class="ms-auto flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/5 dark:hover:text-gray-300"
                        aria-label="{{ __('platform.inbox.close') }}"
                    >
                        <x-filament::icon icon="heroicon-m-x-mark" class="h-5 w-5" />
                    </button>
                </div>

                <div class="flex items-center gap-2 border-b border-gray-100 px-4 py-2 dark:border-white/10">
                    <button
                        type="button"
                        wire:click="toggleShowRead"
                        class="text-xs font-medium text-gray-500 underline-offset-2 transition hover:text-gray-700 hover:underline dark:text-gray-400 dark:hover:text-gray-200"
                    >
                        {{ $showRead ? __('platform.inbox.hide_read') : __('platform.inbox.show_read') }}
                    </button>

                    @if ($unreadCount > 0)
                        <button
                            type="button"
                            wire:click="markAllRead"
                            class="ms-auto text-xs font-medium text-primary-600 underline-offset-2 transition hover:underline dark:text-primary-400"
                        >
                            {{ __('platform.inbox.mark_all_read') }}
                        </button>
                    @endif
                </div>

                <div class="flex-1 overflow-y-auto p-2">
                    @forelse ($notifications as $notification)
                        <div
                            @class([
                                'flex items-start gap-3 rounded-lg px-3 py-2.5 transition',
                                'bg-primary-50/60 dark:bg-primary-500/10' => $notification->read_at === null,
                            ])
                            wire:key="bell-{{ $notification->id }}"
                        >
                            <x-filament::icon
                                icon="heroicon-o-bell-alert"
                                @class([
                                    'mt-0.5 h-5 w-5 shrink-0',
                                    'text-primary-600 dark:text-primary-400' => $notification->read_at === null,
                                    'text-gray-400' => $notification->read_at !== null,
                                ])
                            />

                            <div class="min-w-0 flex-1">
                                <p @class([
                                    'text-sm text-gray-950 dark:text-white',
                                    'font-medium' => $notification->read_at === null,
                                ])>{{ $this->line($notification->data) }}</p>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                            </div>

                            @if ($notification->read_at === null)
                                <button
                                    type="button"
                                    wire:click="markAsRead('{{ $notification->id }}')"
                                    class="shrink-0 text-xs font-medium text-primary-600 underline-offset-2 transition hover:underline dark:text-primary-400"
                                >
                                    {{ __('platform.inbox.mark_read') }}
                                </button>
                            @endif
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center px-6 py-12 text-center">
                            <x-filament::icon icon="heroicon-o-bell-slash" class="h-8 w-8 text-gray-400" />
                            <p class="mt-3 text-sm font-medium text-gray-950 dark:text-white">
                                {{ $showRead ? __('platform.inbox.empty_heading') : __('platform.inbox.empty_unread_heading') }}
                            </p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ $showRead ? __('platform.inbox.empty_description') : __('platform.inbox.empty_unread_description') }}
                            </p>
                        </div>
                    @endforelse
                </div>

                <div class="border-t border-gray-100 px-4 py-3 dark:border-white/10">
                    <a
                        href="{{ $inboxUrl }}"
                        class="text-sm font-medium text-primary-600 underline-offset-2 transition hover:underline dark:text-primary-400"
                    >
                        {{ __('platform.inbox.open_all') }}
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
