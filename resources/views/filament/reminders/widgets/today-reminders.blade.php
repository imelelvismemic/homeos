<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">{{ __('reminders.widget.heading') }}</x-slot>

        @php($reminders = $this->getReminders())

        @if ($reminders->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('reminders.widget.none') }}</p>
        @else
            <ul class="divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($reminders as $reminder)
                    <li>
                        <a
                            href="{{ \App\Modules\Reminders\Filament\Resources\ReminderResource::getUrl('edit', ['record' => $reminder]) }}"
                            class="-mx-2 flex items-center justify-between gap-3 rounded-lg px-2 py-2 transition hover:bg-gray-50 dark:hover:bg-white/5"
                        >
                            <div class="flex items-center gap-2 min-w-0">
                                <x-filament::icon icon="heroicon-m-bell" class="h-4 w-4 shrink-0 text-primary-500" />
                                <span class="truncate text-sm text-gray-950 dark:text-white">{{ $reminder->title }}</span>
                            </div>
                            @if ($reminder->due_date)
                                <span @class([
                                    'shrink-0 text-xs',
                                    'text-danger-600 dark:text-danger-400 font-medium' => $reminder->due_date->isPast() && ! $reminder->due_date->isToday(),
                                    'text-gray-500 dark:text-gray-400' => ! ($reminder->due_date->isPast() && ! $reminder->due_date->isToday()),
                                ])>
                                    {{ $reminder->due_date->isToday() ? $reminder->due_date->translatedFormat('H:i') : $reminder->due_date->translatedFormat('j. M') }}
                                </span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
