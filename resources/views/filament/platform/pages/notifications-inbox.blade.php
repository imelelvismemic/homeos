<x-filament-panels::page>
    @php($notifications = $this->notifications())

    @if ($this->unreadCount() > 0)
        <div class="flex justify-end">
            <x-filament::button color="gray" size="sm" wire:click="markAllRead">
                {{ __('platform.inbox.mark_all_read') }}
            </x-filament::button>
        </div>
    @endif

    @if ($notifications->isEmpty())
        <x-filament::section>
            <div class="flex flex-col items-center gap-2 py-8 text-center">
                <x-filament::icon icon="heroicon-o-bell-slash" class="h-8 w-8 text-gray-400" />
                <p class="text-sm font-medium text-gray-950 dark:text-white">{{ __('platform.inbox.empty_heading') }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('platform.inbox.empty_description') }}</p>
            </div>
        </x-filament::section>
    @else
        <x-filament::section>
            <ul class="divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($notifications as $notification)
                    @php($isUnread = $notification->read_at === null)
                    <li @class([
                        '-mx-2 flex items-start justify-between gap-3 rounded-lg px-2 py-3',
                        'bg-primary-50/50 dark:bg-primary-500/5' => $isUnread,
                    ])>
                        <div class="flex items-start gap-2 min-w-0">
                            <span @class([
                                'mt-1.5 h-2 w-2 shrink-0 rounded-full',
                                'bg-primary-500' => $isUnread,
                                'bg-transparent' => ! $isUnread,
                            ])></span>
                            <div class="min-w-0">
                                <p @class([
                                    'text-sm text-gray-950 dark:text-white',
                                    'font-medium' => $isUnread,
                                ])>{{ $this->line($notification->data) }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $notification->created_at?->diffForHumans() }}</p>
                            </div>
                        </div>

                        @if ($isUnread)
                            <x-filament::button
                                color="gray"
                                size="xs"
                                wire:click="markAsRead('{{ $notification->id }}')"
                            >
                                {{ __('platform.inbox.mark_read') }}
                            </x-filament::button>
                        @endif
                    </li>
                @endforeach
            </ul>
        </x-filament::section>
    @endif
</x-filament-panels::page>
