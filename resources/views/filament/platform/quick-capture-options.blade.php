@if ($items->isNotEmpty())
    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
        @foreach ($items as $item)
            @php
                $tenant = \Filament\Facades\Filament::getTenant();
                $href = \Illuminate\Support\Str::startsWith($item['url'], ['http', '/'])
                    ? $item['url']
                    : route($item['url'], $tenant ? ['tenant' => $tenant] : []);
            @endphp
            <a
                href="{{ $href }}"
                class="flex items-center gap-3 rounded-xl border border-gray-200 p-4 transition hover:border-primary-500 hover:bg-primary-50 dark:border-gray-700 dark:hover:bg-primary-500/10"
            >
                @if ($item['icon'])
                    <x-filament::icon :icon="$item['icon']" class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                @endif
                <span class="font-medium text-gray-950 dark:text-white">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
@else
    <div class="flex flex-col items-center justify-center py-6 text-center">
        <x-filament::icon icon="heroicon-o-sparkles" class="h-8 w-8 text-gray-400" />
        <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
            {{ __('platform.quick_capture.empty') }}
        </p>
    </div>
@endif
