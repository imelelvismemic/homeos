{{-- Znak + naziv proizvoda (ROADMAP Faza 9). Inline SVG, bez vanjskih asseta:
     krov s plusom u terakoti teme; isti motiv je i favicon (public/favicon.svg).
     Naziv dolazi iz config('homeos.name') — jedno mjesto istine. --}}
@php($name = config('homeos.name'))

<span class="flex items-center gap-2" title="{{ $name }}">
    <svg viewBox="0 0 32 32" class="h-8 w-8 shrink-0" aria-hidden="true">
        <rect width="32" height="32" rx="8" class="fill-primary-600" />
        <path d="M7 15.5 16 8l9 7.5" fill="none" stroke="white" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M9.5 15v8.5h13V15" fill="none" stroke="white" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M16 16.8v4.4M13.8 19h4.4" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" />
    </svg>

    <span class="homeos-display text-lg leading-none text-gray-950 dark:text-white">
        {{-- Zadnja riječ naziva ide u boji teme, ostatak normalno. Razmak MORA
             biti eksplicitan — bez njega se dijelovi slijepe ("HomeOSplus"). --}}
        {{ \Illuminate\Support\Str::beforeLast($name, ' ') }}<span class="text-primary-600 dark:text-primary-400"> {{ \Illuminate\Support\Str::afterLast($name, ' ') }}</span>
    </span>
</span>
