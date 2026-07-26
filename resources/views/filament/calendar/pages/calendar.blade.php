<x-filament-panels::page>
    {{-- Dark mode: FullCalendar po defaultu ima svijetlu pozadinu; na tamnoj temi
         bez ovih override-a bijeli tekst pada na bijelu pozadinu (nevidljivo). --}}
    <style>
        .dark #homeos-calendar {
            --fc-page-bg-color: transparent;
            --fc-neutral-bg-color: rgba(255, 255, 255, 0.04);
            --fc-border-color: rgba(255, 255, 255, 0.12);
            --fc-today-bg-color: rgba(191, 106, 68, 0.18);
            --fc-list-event-hover-bg-color: rgba(255, 255, 255, 0.06);
            --fc-neutral-text-color: #e5e7eb;
            color: #e5e7eb;
        }
        .dark #homeos-calendar a { color: #e5e7eb; }
        .dark #homeos-calendar .fc-col-header-cell-cushion,
        .dark #homeos-calendar .fc-daygrid-day-number,
        .dark #homeos-calendar .fc-timegrid-axis-cushion,
        .dark #homeos-calendar .fc-timegrid-slot-label-cushion,
        .dark #homeos-calendar .fc-list-day-text,
        .dark #homeos-calendar .fc-list-day-side-text,
        .dark #homeos-calendar .fc-toolbar-title {
            color: #f3f4f6;
        }
        .dark #homeos-calendar .fc-list-empty { background: transparent; }
    </style>

    <p class="flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
        <x-filament::icon icon="heroicon-m-cursor-arrow-rays" class="h-4 w-4 shrink-0" />
        {{ __('calendar.day_click_hint') }}
    </p>

    {{-- FullCalendar montira se na ovaj element; wire:ignore da ga Livewire ne dira. --}}
    <div
        wire:ignore
        class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 [&_.fc]:text-sm [&_.fc-button-primary]:bg-primary-600 [&_.fc-button-primary]:border-primary-600 [&_.fc-button-primary:hover]:bg-primary-500 [&_.fc-today-button]:capitalize"
    >
        <div id="homeos-calendar"></div>
    </div>

    @vite('resources/js/calendar.js')

    <script>
        (function () {
            const eventsUrl = @json($this->eventsUrl());
            // Jezik i tekst dugmadi idu iz PHP-a (CalendarPage::calendarOptions):
            // FullCalendar sam formatira nazive mjeseci i dana za dati jezik, a
            // terminologiju držimo u prijevodima.
            const calendarOptions = @json($this->calendarOptions());

            function bootHomeosCalendar() {
                const el = document.getElementById('homeos-calendar');
                if (! el || el.dataset.booted) {
                    return;
                }
                if (typeof window.initHomeosCalendar !== 'function') {
                    return;
                }
                el.dataset.booted = '1';
                window.initHomeosCalendar(el, eventsUrl, calendarOptions);
            }

            document.addEventListener('DOMContentLoaded', bootHomeosCalendar);
            // Filament koristi wire:navigate (SPA) — DOMContentLoaded se tad ne okida.
            document.addEventListener('livewire:navigated', bootHomeosCalendar);
        })();
    </script>
</x-filament-panels::page>
