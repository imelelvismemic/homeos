<x-filament-panels::page>
    <x-filament-panels::form id="form" wire:submit="save">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    {{-- Članovi domaćinstva su dio postavki domaćinstva, ne zasebna stavka menija.
         Svaki član vidi listu; radnje nad članovima vidi samo vlasnik. --}}
    {{ $this->table }}
</x-filament-panels::page>
