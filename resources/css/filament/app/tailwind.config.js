import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

/**
 * Filament custom tema (Tailwind v3). Boje (paleta "Topli dom") postavljaju se
 * u HomePanelProvider preko ->colors() — Filament generiše CSS varijable, pa ih
 * ovdje ne dupliramo. Ovdje: content putanje (uklj. buduće module) + display font.
 */
export default {
    presets: [preset],
    content: [
        './app/Platform/Filament/**/*.php',
        './app/Modules/*/Filament/**/*.php',
        './resources/views/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                display: ['Fraunces', 'ui-serif', 'Georgia', 'serif'],
            },
        },
    },
}
