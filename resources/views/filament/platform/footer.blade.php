{{-- Tanka traka na dnu panela (ROADMAP Faza 9). Verzija dolazi iz istog izvora
     kao i health endpoint — config('homeos.version') — pa ne mogu razići. --}}
<footer class="px-4 pb-4 pt-2 text-center text-xs text-gray-400 dark:text-gray-500 md:px-6 lg:px-8">
    {{ __('platform.footer.powered_by') }}
    {{-- Blade tretira `@` kao direktivu, pa ide kroz echo (ne HTML entitet:
         `&commat;` bi ostao u izvoru stranice). --}}
    <span class="font-medium text-gray-500 dark:text-gray-400">{{ '@elvismemic' }}</span>
    <span aria-hidden="true"> · </span>
    <span class="tabular-nums">v{{ config('homeos.version') }}</span>
</footer>
