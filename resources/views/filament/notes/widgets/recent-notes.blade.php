<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">{{ __('notes.widget.heading') }}</x-slot>

        @php($notes = $this->getNotes())

        @if ($notes->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('notes.widget.none') }}</p>
        @else
            <ul class="divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($notes as $note)
                    <li>
                        <a
                            href="{{ \App\Modules\Notes\Filament\Resources\NoteResource::getUrl('edit', ['record' => $note]) }}"
                            class="-mx-2 flex items-center justify-between gap-3 rounded-lg px-2 py-2 transition hover:bg-gray-50 dark:hover:bg-white/5"
                        >
                            <div class="flex items-center gap-2 min-w-0">
                                <x-filament::icon icon="heroicon-m-document-text" class="h-4 w-4 shrink-0 text-primary-500" />
                                <span class="truncate text-sm text-gray-950 dark:text-white">{{ $note->displayTitle() }}</span>
                            </div>
                            @if ($note->journal_date)
                                <span class="shrink-0 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $note->journal_date->translatedFormat('j. M') }}
                                </span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
