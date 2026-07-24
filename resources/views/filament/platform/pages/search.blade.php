<x-filament-panels::page>
    <div class="mx-auto w-full max-w-2xl">
        <x-filament::input.wrapper prefix-icon="heroicon-m-magnifying-glass">
            <x-filament::input
                type="search"
                wire:model.live.debounce.500ms="q"
                :placeholder="__('search.placeholder')"
                autofocus
            />
        </x-filament::input.wrapper>

        @php($groups = $this->getGroupedResults())

        <div class="mt-6 space-y-6">
            @if (! $this->hasQuery())
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('search.hint') }}</p>
            @elseif ($groups->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('search.empty', ['query' => trim($q)]) }}
                </p>
            @else
                @foreach ($groups as $type => $results)
                    <section>
                        <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            {{ $this->typeLabel($type) }}
                            <span class="ms-1 text-gray-400">({{ $results->count() }})</span>
                        </h3>
                        <ul class="divide-y divide-gray-100 overflow-hidden rounded-xl bg-white ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10">
                            @foreach ($results as $result)
                                <li>
                                    <a
                                        href="{{ $result->url }}"
                                        class="flex items-center gap-3 px-4 py-3 transition hover:bg-gray-50 dark:hover:bg-white/5"
                                    >
                                        @if ($result->icon)
                                            <x-filament::icon :icon="$result->icon" class="h-5 w-5 shrink-0 text-primary-500" />
                                        @endif
                                        <span class="truncate text-sm text-gray-950 dark:text-white">{{ $result->title }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endforeach
            @endif
        </div>
    </div>
</x-filament-panels::page>
