{{-- Univerzalna pretraga u topbaru — obična GET forma ka /pretraga (bez
     Livewire round-tripa). Enter vodi na stranicu pretrage koja agregira sve
     module preko SearchService-a. --}}
<form method="GET" action="{{ $action }}" class="hidden items-center sm:flex">
    <x-filament::input.wrapper prefix-icon="heroicon-m-magnifying-glass" class="w-56 lg:w-72">
        <x-filament::input
            type="search"
            name="q"
            :placeholder="__('search.placeholder')"
            :value="request()->query('q')"
        />
    </x-filament::input.wrapper>
</form>
