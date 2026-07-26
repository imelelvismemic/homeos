{{-- Tanka traka na dnu panela (ROADMAP Faza 9). Verzija dolazi iz istog izvora
     kao i health endpoint — config('homeos.version') — pa ne mogu razići.
     Potpis je JEDAN tekstualni čvor: bez razmaka koje bi HTML uvukao između
     elemenata, i provjerljiv testom kao doslovan string. --}}
<footer class="px-4 pb-4 pt-2 text-center text-xs text-gray-400 dark:text-gray-500 md:px-6 lg:px-8">
    {{ '©elvismemic v'.config('homeos.version') }}
</footer>
