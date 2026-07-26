<x-filament-panels::page class="fi-dashboard-page">
    {{-- Signature element: dnevni-brief hero (CLAUDE.md §6) --}}
    <section
        class="homeos-hero relative overflow-hidden rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 p-6 text-white shadow-sm ring-1 ring-black/5 sm:p-8"
    >
        <p class="text-sm font-medium uppercase tracking-wide text-white/80">
            {{ $this->heroDate() }}
        </p>

        <h1 class="homeos-display mt-1 text-3xl text-white sm:text-4xl">
            {{ $this->heroGreeting() }}
        </h1>

        @if (filled($this->heroSummary()))
            <p class="mt-4 text-white/90">
                {{ __('platform.dashboard.summary_prefix') }}
                <span class="font-semibold">{{ implode(' · ', $this->heroSummary()) }}</span>
            </p>
        @else
            <p class="mt-4 max-w-prose text-white/85">
                {{ __('platform.dashboard.empty_summary') }}
            </p>
        @endif
    </section>

    {{-- Widgeti modula — agregirani iz registryja; prazno dok nema modula --}}
    @if (filled($this->getVisibleWidgets()))
        <x-filament-widgets::widgets
            :columns="$this->getColumns()"
            :data="$this->getWidgetData()"
            :widgets="$this->getVisibleWidgets()"
        />
    @else
        {{-- Dvije različite situacije, dvije različite poruke: nema uključenih
             aplikacija vs. aplikacije rade ali danas nemaju šta pokazati. --}}
        <div
            class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-300 p-10 text-center dark:border-gray-700"
        >
            <x-filament::icon
                :icon="$this->hasEnabledApps() ? 'heroicon-o-sparkles' : 'heroicon-o-squares-2x2'"
                class="h-8 w-8 text-gray-400"
            />
            <p class="mt-3 text-sm font-medium text-gray-950 dark:text-white">
                {{ $this->hasEnabledApps() ? __('platform.dashboard.nothing_yet_heading') : __('platform.dashboard.no_apps_heading') }}
            </p>
            <p class="mt-1 max-w-prose text-sm text-gray-500 dark:text-gray-400">
                {{ $this->hasEnabledApps() ? __('platform.dashboard.nothing_yet') : __('platform.dashboard.no_apps') }}
            </p>
        </div>
    @endif
</x-filament-panels::page>
