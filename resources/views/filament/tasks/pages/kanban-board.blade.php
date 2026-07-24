<x-filament-panels::page>
    {{-- Filter po tabli --}}
    <div class="flex flex-wrap items-center gap-3">
        <label for="kanban-board-filter" class="text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ __('tasks.fields.board') }}
        </label>
        <select
            id="kanban-board-filter"
            wire:model.live="boardId"
            class="fi-select-input block rounded-lg border-none bg-white py-1.5 ps-3 pe-8 text-sm text-gray-950 shadow-sm ring-1 ring-gray-950/10 dark:bg-white/5 dark:text-white dark:ring-white/20"
        >
            <option value="">{{ __('tasks.kanban.all_boards') }}</option>
            @foreach ($this->boardOptions() as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Kolone = statusi. Prevlačenje mijenja status; select je touch fallback. --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        @foreach ($this->statuses() as $status)
            @php($tasks = $this->tasksFor($status))
            <div
                x-on:dragover.prevent
                x-on:drop.prevent="$wire.moveTask($event.dataTransfer.getData('taskId'), '{{ $status->value }}')"
                class="flex flex-col rounded-xl bg-gray-50 p-3 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
            >
                <div class="mb-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-filament::badge :color="$status->color()">{{ $status->label() }}</x-filament::badge>
                    </div>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $tasks->count() }}</span>
                </div>

                <div class="flex min-h-24 flex-col gap-2">
                    @forelse ($tasks as $task)
                        <div
                            draggable="true"
                            x-on:dragstart="$event.dataTransfer.setData('taskId', '{{ $task->id }}')"
                            wire:key="task-{{ $task->id }}"
                            class="cursor-grab rounded-lg bg-white p-3 shadow-sm ring-1 ring-gray-950/5 transition hover:ring-primary-500 active:cursor-grabbing dark:bg-gray-900 dark:ring-white/10"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <a
                                    href="{{ \App\Modules\Tasks\Filament\Resources\TaskResource::getUrl('edit', ['record' => $task]) }}"
                                    class="text-sm font-medium text-gray-950 hover:text-primary-600 dark:text-white dark:hover:text-primary-400"
                                >
                                    {{ $task->title }}
                                </a>
                                <x-filament::badge :color="$task->priority->color()" size="sm">
                                    {{ $task->priority->label() }}
                                </x-filament::badge>
                            </div>

                            @if ($task->due_date)
                                <p @class([
                                    'mt-2 text-xs',
                                    'text-danger-600 dark:text-danger-400 font-medium' => $task->due_date->isPast() && ! $task->completed_at,
                                    'text-gray-500 dark:text-gray-400' => ! ($task->due_date->isPast() && ! $task->completed_at),
                                ])>
                                    {{ $task->due_date->translatedFormat('j. M Y.') }}
                                </p>
                            @endif

                            {{-- Touch-friendly premještanje (CLAUDE.md §6) --}}
                            <select
                                x-on:change="$wire.moveTask({{ $task->id }}, $event.target.value)"
                                class="mt-2 w-full rounded-md border-none bg-gray-50 py-1 ps-2 pe-7 text-xs text-gray-700 ring-1 ring-gray-950/10 dark:bg-white/5 dark:text-gray-300 dark:ring-white/10"
                                aria-label="{{ __('tasks.kanban.move_to') }}"
                            >
                                @foreach ($this->statuses() as $option)
                                    <option value="{{ $option->value }}" @selected($option === $task->status)>
                                        {{ $option->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @empty
                        <p class="rounded-lg border border-dashed border-gray-300 p-4 text-center text-xs text-gray-400 dark:border-white/10 dark:text-gray-500">
                            {{ __('tasks.kanban.empty_column') }}
                        </p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
