{{-- Univerzalna pretraga — command palette (Ctrl/Cmd+K). Čisti Alpine modal:
     otvaranje/fokus/zatvaranje je client-side, a live rezultati se dohvaćaju
     običnim GET fetch-om ka /pretraga (SearchController). Bez Livewire komponente
     → nema /livewire/update ni 419. Dostupno na svim širinama; u TOPBAR_START,
     ispred hamburgera na tabletu/mobilnom. --}}
<div
    x-data="{
        isOpen: false,
        q: '',
        groups: [],
        loading: false,
        url: @js($searchUrl),
        toggle() {
            this.isOpen = ! this.isOpen;
            if (this.isOpen) { this.$nextTick(() => this.$refs.input?.focus()); }
        },
        close() { this.isOpen = false; },
        async search() {
            const query = this.q.trim();
            if (query.length < 2) { this.groups = []; this.loading = false; return; }
            this.loading = true;
            try {
                const res = await fetch(this.url + '?q=' + encodeURIComponent(query), {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });
                this.groups = res.ok ? ((await res.json()).groups || []) : [];
            } catch (e) {
                this.groups = [];
            }
            this.loading = false;
        },
    }"
    x-on:keydown.window="if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') { event.preventDefault(); toggle(); }"
    x-on:keydown.escape.window="close()"
>
    {{-- Trigger: ikonica na mobilnom, polje s Ctrl+K nagovještajem na širem ekranu. --}}
    <button
        type="button"
        x-on:click="toggle()"
        class="flex items-center gap-2 rounded-lg p-2 text-gray-500 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 sm:bg-gray-50 sm:px-3 sm:py-1.5 sm:ring-1 sm:ring-gray-950/10 sm:hover:bg-gray-100 sm:dark:bg-white/5 sm:dark:ring-white/10 sm:dark:hover:bg-white/10"
        aria-label="{{ __('search.placeholder_short') }}"
    >
        <x-filament::icon icon="heroicon-m-magnifying-glass" class="h-5 w-5 shrink-0" />
        <span class="hidden text-sm sm:inline">{{ __('search.placeholder_short') }}</span>
        <kbd class="hidden items-center rounded border border-gray-300 px-1.5 py-0.5 text-xs font-medium text-gray-400 dark:border-white/20 md:inline-flex">
            Ctrl K
        </kbd>
    </button>

    {{-- Modal (fixed prekriva viewport; topbar nije containing block). --}}
    <div x-show="isOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto p-4 sm:p-6" style="display: none;">
        <div
            x-show="isOpen"
            x-transition.opacity
            class="fixed inset-0 bg-gray-950/50 backdrop-blur-sm"
            x-on:click="close()"
        ></div>

        <div x-show="isOpen" x-transition class="relative mx-auto mt-16 w-full max-w-xl">
            <div class="overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-2 border-b border-gray-100 px-4 dark:border-white/10">
                    <x-filament::icon icon="heroicon-m-magnifying-glass" class="h-5 w-5 shrink-0 text-gray-400" />
                    <input
                        x-ref="input"
                        type="search"
                        x-model="q"
                        x-on:input.debounce.300ms="search()"
                        placeholder="{{ __('search.placeholder') }}"
                        class="w-full border-0 bg-transparent py-3.5 text-sm text-gray-950 placeholder:text-gray-400 focus:ring-0 dark:text-white"
                    />
                    <svg x-show="loading" class="h-4 w-4 shrink-0 animate-spin text-gray-400" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <kbd class="hidden shrink-0 rounded border border-gray-300 px-1.5 py-0.5 text-xs font-medium text-gray-400 dark:border-white/20 sm:inline-block">Esc</kbd>
                </div>

                <div class="max-h-[60vh] overflow-y-auto p-2">
                    <p x-show="q.trim().length < 2" class="px-3 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                        {{ __('search.hint') }}
                    </p>
                    <p x-show="q.trim().length >= 2 && ! loading && groups.length === 0" class="px-3 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                        {{ __('search.empty_generic') }}
                    </p>

                    <template x-for="group in groups" :key="group.type">
                        <div class="pb-2">
                            <h3 class="px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-gray-400" x-text="group.label"></h3>
                            <ul>
                                <template x-for="result in group.results" :key="result.id">
                                    <li>
                                        <a
                                            :href="result.url"
                                            class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-950 transition hover:bg-gray-100 dark:text-white dark:hover:bg-white/5"
                                        >
                                            <span class="truncate" x-text="result.title"></span>
                                        </a>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
