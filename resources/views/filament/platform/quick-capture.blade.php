{{-- "Brzo dodaj" launcher (ROADMAP Faza 2.4). Običan Filament dropdown linkova
     koje moduli registruju (QuickCaptureRegistry); otvara se client-side (Alpine),
     bez Livewire round-tripa — pa nema /livewire/update zahtjeva ni 419. Hrefovi
     su već razriješeni (s tenant segmentom) u render hooku. Sa 0 modula → prazno
     stanje, bez greške. --}}
<x-filament::dropdown placement="bottom-end" teleport>
    <x-slot name="trigger">
        <x-filament::button icon="heroicon-m-plus" size="sm" color="primary">
            {{ __('platform.quick_capture.button') }}
        </x-filament::button>
    </x-slot>

    <x-filament::dropdown.list>
        @forelse ($items as $item)
            <x-filament::dropdown.list.item
                tag="a"
                :href="$item['href']"
                :icon="$item['icon']"
            >
                {{ $item['label'] }}
            </x-filament::dropdown.list.item>
        @empty
            <div class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                {{ __('platform.quick_capture.empty') }}
            </div>
        @endforelse
    </x-filament::dropdown.list>
</x-filament::dropdown>
