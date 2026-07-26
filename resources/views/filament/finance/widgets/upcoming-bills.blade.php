<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">{{ __('finance.widget.heading') }}</x-slot>

        @php($bills = $this->getBills())

        @if ($bills->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('finance.widget.none') }}</p>
        @else
            <ul class="divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($bills as $bill)
                    <li>
                        <a
                            href="{{ \App\Modules\Finance\Filament\Resources\BillResource::getUrl('edit', ['record' => $bill]) }}"
                            class="-mx-2 flex items-center justify-between gap-3 rounded-lg px-2 py-2 transition hover:bg-gray-50 dark:hover:bg-white/5"
                        >
                            <div class="flex items-center gap-2 min-w-0">
                                <x-filament::icon icon="heroicon-m-document-currency-euro" class="h-4 w-4 shrink-0 text-primary-500" />
                                <span class="truncate text-sm text-gray-950 dark:text-white">{{ $bill->title }}</span>
                            </div>
                            <div class="flex shrink-0 items-center gap-3">
                                <span class="text-sm tabular-nums text-gray-700 dark:text-gray-300">{{ \App\Modules\Finance\Support\Money::format($bill->amount) }}</span>
                                <span @class([
                                    'text-xs',
                                    'text-danger-600 dark:text-danger-400 font-medium' => $bill->due_date->isPast(),
                                    'text-gray-500 dark:text-gray-400' => ! $bill->due_date->isPast(),
                                ])>{{ $bill->due_date->translatedFormat('j. M') }}</span>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
