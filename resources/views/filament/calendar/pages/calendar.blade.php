<x-filament-panels::page>
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
            const events = @json($this->events());

            function bootHomeosCalendar() {
                const el = document.getElementById('homeos-calendar');
                if (! el || el.dataset.booted) {
                    return;
                }
                if (typeof window.initHomeosCalendar !== 'function') {
                    return;
                }
                el.dataset.booted = '1';
                window.initHomeosCalendar(el, events);
            }

            document.addEventListener('DOMContentLoaded', bootHomeosCalendar);
            // Filament koristi wire:navigate (SPA) — DOMContentLoaded se tad ne okida.
            document.addEventListener('livewire:navigated', bootHomeosCalendar);
        })();
    </script>
</x-filament-panels::page>
