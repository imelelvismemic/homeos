import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

// Privremena paleta — pravi token sistem (4-6 imenovanih boja, tipografija,
// signature element) zaključava se prije Faze 2 (CLAUDE.md tačka 6).
export default {
    presets: [preset],
    theme: {
        extend: {
            colors: {
                brand: {
                    50: '#fdf7ee',
                    100: '#f8e9cf',
                    300: '#e9bd6f',
                    500: '#d99b2b',
                    700: '#a8721a',
                    900: '#5c3d0e',
                },
            },
        },
    },
    content: [
        './app/Platform/Filament/**/*.php',
        './app/Modules/*/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
}
