<x-filament-panels::page>
    {{-- Navigacija po mjesecima (najdalje do tekućeg mjeseca) --}}
    <div class="flex items-center justify-between gap-3">
        <x-filament::button
            color="gray"
            icon="heroicon-m-chevron-left"
            wire:click="previousMonth"
        >
            {{ __('finance.overview.previous_month') }}
        </x-filament::button>

        <span class="text-base font-semibold text-gray-950 dark:text-white">{{ $this->periodLabel() }}</span>

        <x-filament::button
            color="gray"
            icon="heroicon-m-chevron-right"
            icon-position="after"
            wire:click="nextMonth"
            :disabled="! $this->canGoNext()"
        >
            {{ __('finance.overview.next_month') }}
        </x-filament::button>
    </div>

    @php($totals = $this->totals())

    {{-- Sažetak mjeseca --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-filament::section>
            <x-slot name="heading">{{ __('finance.overview.income') }}</x-slot>
            <p class="text-2xl font-semibold text-success-600 dark:text-success-400">{{ $this->money($totals['income']) }}</p>
        </x-filament::section>
        <x-filament::section>
            <x-slot name="heading">{{ __('finance.overview.expense') }}</x-slot>
            <p class="text-2xl font-semibold text-danger-600 dark:text-danger-400">{{ $this->money($totals['expense']) }}</p>
        </x-filament::section>
        <x-filament::section>
            <x-slot name="heading">{{ __('finance.overview.net') }}</x-slot>
            <p @class([
                'text-2xl font-semibold',
                'text-success-600 dark:text-success-400' => $totals['net'] >= 0,
                'text-danger-600 dark:text-danger-400' => $totals['net'] < 0,
            ])>{{ $this->money($totals['net']) }}</p>
        </x-filament::section>
    </div>

    {{-- Po kategoriji naspram budžeta --}}
    <x-filament::section>
        <x-slot name="heading">{{ __('finance.overview.by_category') }}</x-slot>

        @php($rows = $this->categoryRows())
        @if (empty($rows))
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('finance.overview.no_expenses') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-start text-xs uppercase tracking-wide text-gray-500 dark:border-white/10 dark:text-gray-400">
                            <th class="py-2 text-start font-medium">{{ __('finance.overview.category') }}</th>
                            <th class="py-2 text-end font-medium">{{ __('finance.overview.spent') }}</th>
                            <th class="py-2 text-end font-medium">{{ __('finance.overview.budget') }}</th>
                            <th class="py-2 text-end font-medium">{{ __('finance.overview.remaining') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                        @foreach ($rows as $row)
                            @php($remaining = $row['budget'] !== null ? $row['budget'] - $row['spent'] : null)
                            <tr>
                                <td class="py-2 text-gray-950 dark:text-white">{{ $row['name'] }}</td>
                                <td class="py-2 text-end tabular-nums">{{ $this->money($row['spent']) }}</td>
                                <td class="py-2 text-end tabular-nums text-gray-500 dark:text-gray-400">
                                    {{ $row['budget'] !== null ? $this->money($row['budget']) : '—' }}
                                </td>
                                <td @class([
                                    'py-2 text-end tabular-nums',
                                    'text-danger-600 dark:text-danger-400 font-medium' => $remaining !== null && $remaining < 0,
                                    'text-gray-500 dark:text-gray-400' => $remaining === null || $remaining >= 0,
                                ])>
                                    {{ $remaining !== null ? $this->money($remaining) : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>

    {{-- Ko duguje kome --}}
    <x-filament::section>
        <x-slot name="heading">{{ __('finance.overview.who_owes') }}</x-slot>

        @php($balances = $this->balanceRows())
        @if (empty($balances))
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('finance.overview.no_balances') }}</p>
        @else
            <ul class="divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($balances as $b)
                    <li class="flex items-center justify-between gap-3 py-2">
                        <span class="text-sm text-gray-950 dark:text-white">{{ $b['name'] }}</span>
                        @if ($b['net'] > 0)
                            <span class="text-sm font-medium text-success-600 dark:text-success-400">
                                {{ __('finance.overview.is_owed', ['amount' => $this->money($b['net'])]) }}
                            </span>
                        @elseif ($b['net'] < 0)
                            <span class="text-sm font-medium text-danger-600 dark:text-danger-400">
                                {{ __('finance.overview.owes', ['amount' => $this->money(abs($b['net']))]) }}
                            </span>
                        @else
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('finance.overview.settled') }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </x-filament::section>
</x-filament-panels::page>
