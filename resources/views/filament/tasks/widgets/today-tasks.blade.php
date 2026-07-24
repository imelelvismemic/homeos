<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">{{ __('tasks.widget.heading') }}</x-slot>

        @php($tasks = $this->getTasks())

        @if ($tasks->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('tasks.widget.none') }}</p>
        @else
            <ul class="divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($tasks as $task)
                    <li>
                        <a
                            href="{{ \App\Modules\Tasks\Filament\Resources\TaskResource::getUrl('edit', ['record' => $task]) }}"
                            class="-mx-2 flex items-center justify-between gap-3 rounded-lg px-2 py-2 transition hover:bg-gray-50 dark:hover:bg-white/5"
                        >
                            <div class="flex items-center gap-2 min-w-0">
                                <x-filament::badge :color="$task->priority->color()" size="sm">
                                    {{ $task->priority->label() }}
                                </x-filament::badge>
                                <span class="truncate text-sm text-gray-950 dark:text-white">{{ $task->title }}</span>
                            </div>
                            @if ($task->due_date)
                                <span @class([
                                    'shrink-0 text-xs',
                                    'text-danger-600 dark:text-danger-400 font-medium' => $task->due_date->isPast() && ! $task->due_date->isToday(),
                                    'text-gray-500 dark:text-gray-400' => ! ($task->due_date->isPast() && ! $task->due_date->isToday()),
                                ])>
                                    {{ $task->due_date->isToday() ? __('tasks.widget.due_today') : $task->due_date->translatedFormat('j. M') }}
                                </span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
