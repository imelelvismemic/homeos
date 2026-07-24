<div
    x-data="{ open: false }"
    x-on:keydown.window="if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') { event.preventDefault(); open = ! open }"
    x-on:keydown.escape.window="open = false"
    x-init="$watch('open', (value) => { if (value) { $nextTick(() => $refs.searchInput?.focus()) } })"
>
    {{-- Trigger: ikonica na mobilnom, polje s Ctrl+K nagovještajem na širem ekranu. --}}
    <button
        type="button"
        x-on:click="open = true"
        class="flex items-center gap-2 rounded-lg p-2 text-gray-500 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 sm:bg-gray-50 sm:px-3 sm:py-1.5 sm:ring-1 sm:ring-gray-950/10 sm:hover:bg-gray-100 sm:dark:bg-white/5 sm:dark:ring-white/10 sm:dark:hover:bg-white/10"
        aria-label="{{ __('search.placeholder_short') }}"
    >
        <x-filament::icon icon="heroicon-m-magnifying-glass" class="h-5 w-5 shrink-0" />
        <span class="hidden text-sm sm:inline">{{ __('search.placeholder_short') }}</span>
        <kbd class="hidden items-center rounded border border-gray-300 px-1.5 py-0.5 text-xs font-medium text-gray-400 dark:border-white/20 md:inline-flex">
            Ctrl K
        </kbd>
    </button>

    {{-- Command palette modal. Fiksno pozicioniranje prekriva cijeli viewport
         (topbar nije containing block), pa nije potreban teleport koji bi lomio
         Livewire morph. --}}
    <div
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto p-4 sm:p-6"
        style="display: none;"
    >
        {{-- Zatamnjenje trenutne stranice --}}
        <div
            x-show="open"
            x-transition.opacity
            class="fixed inset-0 bg-gray-950/50 backdrop-blur-sm"
            x-on:click="open = false"
        ></div>

        <div
            x-show="open"
            x-transition
            class="relative mx-auto mt-16 w-full max-w-xl"
        >
            <div class="overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-2 border-b border-gray-100 px-4 dark:border-white/10">
                    <x-filament::icon icon="heroicon-m-magnifying-glass" class="h-5 w-5 shrink-0 text-gray-400" />

                    <input
                        x-ref="searchInput"
                        type="search"
                        wire:model.live.debounce.300ms="q"
                        placeholder="{{ __('search.placeholder') }}"
                        class="w-full border-0 bg-transparent py-3.5 text-sm text-gray-950 placeholder:text-gray-400 focus:ring-0 dark:text-white"
                        x-on:keydown.escape="open = false"
                    />

                    <div wire:loading wire:target="q" class="shrink-0">
                        <x-filament::loading-indicator class="h-4 w-4 text-gray-400" />
                    </div>

                    <kbd class="hidden shrink-0 rounded border border-gray-300 px-1.5 py-0.5 text-xs font-medium text-gray-400 dark:border-white/20 sm:inline-block">
                        Esc
                    </kbd>
                </div>

                <div class="max-h-[60vh] overflow-y-auto p-2">
                    @if (! $this->hasQuery())
                        <p class="px-3 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                            {{ __('search.hint') }}
                        </p>
                    @elseif ($groups->isEmpty())
                        <p class="px-3 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                            {{ __('search.empty', ['query' => trim($q)]) }}
                        </p>
                    @else
                        @foreach ($groups as $type => $results)
                            <div class="pb-2">
                                <h3 class="px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-gray-400">
                                    {{ $this->typeLabel($type) }}
                                </h3>
                                <ul>
                                    @foreach ($results as $result)
                                        <li wire:key="{{ $type }}-{{ $result->id }}">
                                            <a
                                                href="{{ $result->url }}"
                                                class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-950 transition hover:bg-gray-100 dark:text-white dark:hover:bg-white/5"
                                            >
                                                @if ($result->icon)
                                                    <x-filament::icon :icon="$result->icon" class="h-5 w-5 shrink-0 text-primary-500" />
                                                @endif
                                                <span class="truncate">{{ $result->title }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
