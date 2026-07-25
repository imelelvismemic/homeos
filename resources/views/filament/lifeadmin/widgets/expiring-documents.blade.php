<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">{{ __('lifeadmin.widget.heading') }}</x-slot>

        @php($documents = $this->getDocuments())

        @if ($documents->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('lifeadmin.widget.none') }}</p>
        @else
            <ul class="divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($documents as $document)
                    <li>
                        <a
                            href="{{ \App\Modules\LifeAdmin\Filament\Resources\DocumentResource::getUrl('edit', ['record' => $document]) }}"
                            class="-mx-2 flex items-center justify-between gap-3 rounded-lg px-2 py-2 transition hover:bg-gray-50 dark:hover:bg-white/5"
                        >
                            <div class="flex items-center gap-2 min-w-0">
                                <x-filament::icon :icon="$document->type->icon()" class="h-4 w-4 shrink-0 text-primary-500" />
                                <span class="truncate text-sm text-gray-950 dark:text-white">{{ $document->title }}</span>
                            </div>
                            <span @class([
                                'text-xs shrink-0',
                                'text-danger-600 dark:text-danger-400 font-medium' => $document->expiry_date->isPast(),
                                'text-gray-500 dark:text-gray-400' => ! $document->expiry_date->isPast(),
                            ])>{{ $document->expiry_date->translatedFormat('j. M Y.') }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
