<x-filament-panels::page>
    <x-filament-panels::form wire:submit="save">
        {{ $this->form }}

        <div>
            <x-filament::button type="submit">
                {{ __('platform.settings.save') }}
            </x-filament::button>
        </div>
    </x-filament-panels::form>
</x-filament-panels::page>
