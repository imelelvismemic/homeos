<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">{{ __('pets.widget.heading') }}</x-slot>

        @php($records = $this->getCareRecords())

        @if ($records->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('pets.widget.none') }}</p>
        @else
            <ul class="divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($records as $record)
                    <li>
                        <a
                            href="{{ \App\Modules\Pets\Filament\Resources\PetResource::getUrl('edit', ['record' => $record->pet_id]) }}"
                            class="-mx-2 flex items-center justify-between gap-3 rounded-lg px-2 py-2 transition hover:bg-gray-50 dark:hover:bg-white/5"
                        >
                            <div class="flex min-w-0 items-center gap-2">
                                <x-filament::icon :icon="$record->type->icon()" class="h-4 w-4 shrink-0 text-primary-500" />
                                <span class="truncate text-sm text-gray-950 dark:text-white">{{ $record->displayTitle() }}</span>
                            </div>
                            <span @class([
                                'shrink-0 text-xs',
                                'font-medium text-danger-600 dark:text-danger-400' => $record->due_date->isPast(),
                                'text-gray-500 dark:text-gray-400' => ! $record->due_date->isPast(),
                            ])>{{ $record->due_date->translatedFormat('j. M Y.') }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
